<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;

class OrderController extends Controller
{
    public function list(): void
    {
        Auth::requireLogin();

        $deliveryStatus = $_GET['delivery_status'] ?? '';
        $search = $_GET['search'] ?? '';
        $searchType = $_GET['search_type'] ?? 'order_number';
        $page = (int) ($_GET['page'] ?? 1);
        $startDate = $_GET['start_date'] ?? '';
        $endDate = $_GET['end_date'] ?? '';

        $orderModel = new Order();
        $filters = [];

        if ($deliveryStatus) {
            $filters['delivery_status'] = $deliveryStatus;
        }
        if ($search) {
            $filters[$searchType] = $search;
        }
        if ($startDate && $endDate) {
            $filters['start_date'] = $startDate;
            $filters['end_date'] = $endDate;
        }

        $result = $orderModel->searchOrders($filters, $page);

        $this->render('orders/list', [
            'page_title' => 'Orders',
            'flash' => $this->getFlash(),
            'orders' => $result['items'],
            'total' => $result['total'],
            'page' => $result['page'],
            'totalPages' => $result['totalPages'],
            'deliveryStatus' => $deliveryStatus,
            'search' => $search,
            'searchType' => $searchType,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }

    public function create(): void
    {
        Auth::requireLogin();

        $variantModel = new ProductVariant();
        $variants = $variantModel->getAllWithProduct();

        $variantJson = [];
        foreach ($variants as $v) {
            $variantJson[$v['product_id']][] = [
                'id' => $v['id'],
                'sku' => $v['sku'],
                'size' => $v['size'],
                'price' => $v['variant_price'] ?? $v['base_price'],
                'stock' => (int) ($v['stock'] ?? 0)
            ];
        }

        $this->render('orders/form', [
            'page_title'    => 'Create Order',
            'order'         => null,
            'variantJson'   => json_encode($variantJson),
            'variants'      => $variants,
            'errors'        => $_SESSION['errors'] ?? [],
            'old'           => $_SESSION['old_input'] ?? [],
            'hasStockIssue' => false,
        ]);

        unset($_SESSION['errors'], $_SESSION['old_input']);
    }

    public function store(): void
    {
        Auth::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/orders/create');
        }

        $customerName = $_POST['customer_name'] ?? '';
        $customerEmail = $_POST['customer_email'] ?? '';
        $customerPhone = $_POST['customer_phone'] ?? '';
        $deliveryAddress = $_POST['delivery_address'] ?? '';
        $notes = $_POST['notes'] ?? '';
        $paymentMethod = $_POST['payment_method'] ?? 'cod';
        $paymentStatus = $_POST['payment_status'] ?? 'unpaid';
        $deliveryStatus = $_POST['delivery_status'] ?? 'pending';
        $pickupPersonName = $_POST['pickup_person_name'] ?? null;

        $productIds = $_POST['product_id'] ?? [];
        $variantIds = $_POST['variant_id'] ?? [];
        $quantities = $_POST['quantity'] ?? [];

        $errors = $this->validate(
            [
                'customer_name' => $customerName,
                'customer_phone' => $customerPhone,
                'delivery_address' => $deliveryAddress,
            ],
            [
                'customer_name' => 'required|min:2|max:255',
                'customer_phone' => 'required|min:5|max:20',
                'delivery_address' => 'required|min:5|max:500',
            ]
        );

        if (empty($productIds) || empty($variantIds) || empty($quantities)) {
            $errors['items'] = 'At least one order item is required';
        }

        // Validate payment method
        if (!in_array($paymentMethod, ['cod', 'bkash', 'bank'])) {
            $errors['payment_method'] = 'Invalid payment method';
        }

        // Validate payment status
        if (!in_array($paymentStatus, ['unpaid', 'paid'])) {
            $errors['payment_status'] = 'Invalid payment status';
        }

        // Validate delivery status
        if (!in_array($deliveryStatus, ['pending', 'waiting_for_print', 'package_ready', 'courier_pickup', 'personal_pickup', 'delivered', 'on_hold', 'cancelled', 'returned'])) {
            $errors['delivery_status'] = 'Invalid delivery status';
        }

        // Validate pickup person name if personal pickup is selected
        if ($deliveryStatus === 'personal_pickup' && (empty($pickupPersonName) || strlen(trim($pickupPersonName)) < 2)) {
            $errors['pickup_person_name'] = 'Pickup person name is required for personal pickup';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = $_POST;
            $this->redirect('/orders/create');
            return;
        }

        $deliveryCharge = max(0, (float) ($_POST['delivery_charge'] ?? 80));

        $orderModel = new Order();
        $variantModel = new ProductVariant();

        try {
            $db = $orderModel->getConnection();
            $db->beginTransaction();

            $orderNumber = date('Y') . '-' . strtoupper(substr(uniqid(), -5));
            $totalAmount = 0;

            $now = date('Y-m-d H:i:s');
            $timestampFields = [];
            if ($deliveryStatus === 'delivered')  $timestampFields['delivered_at'] = $now;
            elseif ($deliveryStatus === 'cancelled') $timestampFields['cancelled_at'] = $now;
            elseif ($deliveryStatus === 'returned')  $timestampFields['returned_at']  = $now;

            $orderId = $orderModel->create(array_merge([
                'order_number' => $orderNumber,
                'customer_name' => $customerName,
                'customer_email' => $customerEmail,
                'customer_phone' => $customerPhone,
                'delivery_address' => $deliveryAddress,
                'notes' => $notes,
                'total_amount' => 0,
                'payment_method' => $paymentMethod,
                'payment_status' => $paymentStatus,
                'delivery_status' => $deliveryStatus,
                'pickup_person_name' => $pickupPersonName
            ], $timestampFields));

            $itemModel = new OrderItem();
            $hasStockIssue = false;

            for ($i = 0; $i < count($productIds); $i++) {
                $variantId = (int) $variantIds[$i];
                $quantity = (int) $quantities[$i];

                if ($quantity <= 0) continue;

                $variant = $variantModel->find($variantId);
                if (!$variant) continue;

                $unitPrice = (float) ($_POST['unit_price'][$i] ?? 0);
                if ($unitPrice <= 0) {
                    $unitPrice = (float) ($variant['variant_price'] ?? $variant['base_price'] ?? 0);
                }
                $patchesExtra = (float) ($_POST['patches_extra'][$i] ?? 0);
                $namekitExtra = (float) ($_POST['namekit_extra'][$i] ?? 0);
                $kitName      = trim($_POST['kit_name'][$i] ?? '');
                $kitNumber    = trim($_POST['kit_number'][$i] ?? '');
                $lineTotal = ($unitPrice * $quantity) + $patchesExtra + $namekitExtra;
                $totalAmount += $lineTotal;

                $stockDeducted = (int) $variant['stock'] >= $quantity ? 1 : 0;
                if (!$stockDeducted) $hasStockIssue = true;

                $itemModel->create([
                    'order_id'       => $orderId,
                    'product_id'     => (int) $productIds[$i],
                    'variant_id'     => $variantId,
                    'quantity'       => $quantity,
                    'unit_price'     => $unitPrice,
                    'line_total'     => $lineTotal,
                    'patches_extra'  => $patchesExtra,
                    'namekit_extra'  => $namekitExtra,
                    'kit_name'       => $kitName ?: null,
                    'kit_number'     => $kitNumber ?: null,
                    'stock_deducted' => $stockDeducted,
                ]);

                if ($stockDeducted) $variantModel->updateStock($variantId, -$quantity);
            }

            if ($hasStockIssue) $deliveryStatus = 'pending';

            $orderModel->update($orderId, [
                'total_amount'    => $totalAmount + $deliveryCharge,
                'delivery_status' => $deliveryStatus,
                'has_stock_issue' => (int) $hasStockIssue,
            ]);

            $db->commit();

            $this->setFlash('success', 'Order created successfully!');
            $this->redirect("/orders/$orderId");

        } catch (\Exception $e) {
            $db->rollback();
            $_SESSION['errors'] = ['database' => 'Failed to create order: ' . $e->getMessage()];
            $_SESSION['old_input'] = $_POST;
            $this->redirect('/orders/create');
        }
    }

    public function show(): void
    {
        Auth::requireLogin();

        $id = (int) ($_GET['id'] ?? 0);

        if ($id <= 0) {
            $this->abort(404, 'Order not found');
        }

        $orderModel = new Order();
        $order = $orderModel->find($id);

        if (!$order) {
            $this->abort(404, 'Order not found');
        }

        $itemModel    = new OrderItem();
        $variantModel = new ProductVariant();
        $items        = $itemModel->getByOrder($id);

        $hasStockIssue = $this->computeStockIssue($items, $variantModel, $orderModel, $id);

        $this->render('orders/show', [
            'page_title'    => 'Order ' . htmlspecialchars(str_replace('ORD-', '', $order['order_number'])),
            'order'         => $order,
            'items'         => $items,
            'hasStockIssue' => $hasStockIssue,
            'flash'         => $this->getFlash(),
        ]);
    }

    public function edit(): void
    {
        Auth::requireLogin();

        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) $this->abort(404, 'Order not found');

        $orderModel = new Order();
        $order = $orderModel->find($id);
        if (!$order) $this->abort(404, 'Order not found');

        $blockedStatuses = ['delivered', 'cancelled', 'on_hold'];
        if (in_array($order['delivery_status'], $blockedStatuses)) {
            $this->setFlash('error', 'Orders with status "' . ucfirst(str_replace('_', ' ', $order['delivery_status'])) . '" cannot be edited.');
            $this->redirect("/orders/{$id}");
            return;
        }

        $variantModel  = new ProductVariant();
        $variants      = $variantModel->getAllWithProduct();
        $itemModel     = new OrderItem();
        $existingItems = $itemModel->getByOrder($id);

        // For edit mode: add back quantities already deducted by this order so the
        // stock display reflects what would actually be available after releasing this order.
        $ownDeductedQty = [];
        foreach ($existingItems as $item) {
            if (!($item['is_return'] ?? 0) && ($item['stock_deducted'] ?? 0)) {
                $vid = (int) $item['variant_id'];
                $ownDeductedQty[$vid] = ($ownDeductedQty[$vid] ?? 0) + (int) $item['quantity'];
            }
        }

        $variantJson = [];
        foreach ($variants as $v) {
            $vid         = (int) $v['id'];
            $displayStock = (int) ($v['stock'] ?? 0) + ($ownDeductedQty[$vid] ?? 0);
            $variantJson[$v['product_id']][] = [
                'id'    => $vid,
                'sku'   => $v['sku'],
                'size'  => $v['size'],
                'price' => $v['variant_price'] ?? $v['base_price'],
                'stock' => $displayStock,
            ];
        }

        $hasStockIssue = $this->computeStockIssue($existingItems, $variantModel, $orderModel, $id);

        $this->render('orders/form', [
            'page_title'    => 'Edit Order',
            'order'         => $order,
            'variantJson'   => json_encode($variantJson),
            'variants'      => $variants,
            'existingItems' => $existingItems,
            'errors'        => $_SESSION['errors'] ?? [],
            'old'           => $_SESSION['old_input'] ?? [],
            'hasStockIssue' => $hasStockIssue,
        ]);

        unset($_SESSION['errors'], $_SESSION['old_input']);
    }

    public function update(): void
    {
        Auth::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectBack();
        }

        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) $this->abort(404, 'Order not found');

        $orderModel = new Order();
        $order = $orderModel->find($id);
        if (!$order) $this->abort(404, 'Order not found');

        $blockedStatuses = ['delivered', 'cancelled', 'on_hold'];
        if (in_array($order['delivery_status'], $blockedStatuses)) {
            $this->setFlash('error', 'This order cannot be edited.');
            $this->redirect("/orders/{$id}");
            return;
        }

        $customerName    = $_POST['customer_name'] ?? '';
        $customerEmail   = $_POST['customer_email'] ?? '';
        $customerPhone   = $_POST['customer_phone'] ?? '';
        $deliveryAddress = $_POST['delivery_address'] ?? '';
        $notes           = $_POST['notes'] ?? '';
        $paymentMethod   = $_POST['payment_method'] ?? 'cod';
        $paymentStatus   = $_POST['payment_status'] ?? 'unpaid';
        $deliveryStatus  = $_POST['delivery_status'] ?? 'pending';
        $pickupPersonName = $_POST['pickup_person_name'] ?? null;

        $productIds = $_POST['product_id'] ?? [];
        $variantIds = $_POST['variant_id'] ?? [];
        $quantities  = $_POST['quantity'] ?? [];

        $errors = $this->validate(
            [
                'customer_name'    => $customerName,
                'customer_phone'   => $customerPhone,
                'delivery_address' => $deliveryAddress,
            ],
            [
                'customer_name'    => 'required|min:2|max:255',
                'customer_phone'   => 'required|min:5|max:20',
                'delivery_address' => 'required|min:5|max:500',
            ]
        );

        if (empty($productIds) || empty($variantIds) || empty($quantities)) {
            $errors['items'] = 'At least one order item is required';
        }

        if (!in_array($paymentMethod, ['cod', 'bkash', 'bank'])) {
            $errors['payment_method'] = 'Invalid payment method';
        }
        if (!in_array($paymentStatus, ['unpaid', 'paid'])) {
            $errors['payment_status'] = 'Invalid payment status';
        }
        if (!in_array($deliveryStatus, ['pending', 'waiting_for_print', 'package_ready', 'courier_pickup', 'personal_pickup', 'delivered', 'on_hold', 'cancelled', 'returned'])) {
            $errors['delivery_status'] = 'Invalid delivery status';
        }
        if ($deliveryStatus === 'personal_pickup' && (empty($pickupPersonName) || strlen(trim($pickupPersonName)) < 2)) {
            $errors['pickup_person_name'] = 'Pickup person name is required for personal pickup';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = $_POST;
            $this->redirect("/orders/edit/{$id}");
            return;
        }

        $deliveryCharge = max(0, (float) ($_POST['delivery_charge'] ?? 80));

        $variantModel = new ProductVariant();
        $itemModel    = new OrderItem();

        try {
            $db = $orderModel->getConnection();
            $db->beginTransaction();

            // Restore stock only for items that were actually deducted
            $existingItems = $itemModel->getByOrder($id);
            foreach ($existingItems as $item) {
                if (!$item['is_return'] && $item['stock_deducted']) {
                    $variantModel->updateStock((int) $item['variant_id'], (int) $item['quantity']);
                }
            }
            $db->prepare("DELETE FROM order_items WHERE order_id = ?")->execute([$id]);

            // Re-create items
            $totalAmount = 0;
            $hasStockIssue = false;
            for ($i = 0; $i < count($productIds); $i++) {
                $variantId = (int) $variantIds[$i];
                $quantity  = (int) $quantities[$i];
                if ($quantity <= 0) continue;

                $variant = $variantModel->find($variantId);
                if (!$variant) continue;

                $unitPrice    = (float) ($_POST['unit_price'][$i] ?? 0);
                if ($unitPrice <= 0) {
                    $unitPrice = (float) ($variant['variant_price'] ?? $variant['base_price'] ?? 0);
                }
                $patchesExtra = (float) ($_POST['patches_extra'][$i] ?? 0);
                $namekitExtra = (float) ($_POST['namekit_extra'][$i] ?? 0);
                $kitName      = trim($_POST['kit_name'][$i] ?? '');
                $kitNumber    = trim($_POST['kit_number'][$i] ?? '');
                $lineTotal    = ($unitPrice * $quantity) + $patchesExtra + $namekitExtra;
                $totalAmount += $lineTotal;

                $stockDeducted = (int) $variant['stock'] >= $quantity ? 1 : 0;
                if (!$stockDeducted) $hasStockIssue = true;

                $itemModel->create([
                    'order_id'       => $id,
                    'product_id'     => (int) $productIds[$i],
                    'variant_id'     => $variantId,
                    'quantity'       => $quantity,
                    'unit_price'     => $unitPrice,
                    'line_total'     => $lineTotal,
                    'patches_extra'  => $patchesExtra,
                    'namekit_extra'  => $namekitExtra,
                    'kit_name'       => $kitName ?: null,
                    'kit_number'     => $kitNumber ?: null,
                    'stock_deducted' => $stockDeducted,
                ]);

                if ($stockDeducted) $variantModel->updateStock($variantId, -$quantity);
            }

            if ($hasStockIssue) $deliveryStatus = 'pending';

            $orderModel->update($id, [
                'customer_name'      => $customerName,
                'customer_email'     => $customerEmail,
                'customer_phone'     => $customerPhone,
                'delivery_address'   => $deliveryAddress,
                'notes'              => $notes,
                'payment_method'     => $paymentMethod,
                'payment_status'     => $paymentStatus,
                'delivery_status'    => $deliveryStatus,
                'pickup_person_name' => $pickupPersonName,
                'total_amount'       => $totalAmount + $deliveryCharge,
                'has_stock_issue'    => (int) $hasStockIssue,
            ]);

            $orderModel->setDeliveryTimestamp($id, $deliveryStatus, $order['delivery_status']);

            $db->commit();
            $this->setFlash('success', 'Order updated successfully!');
            $this->redirect("/orders/{$id}");

        } catch (\Exception $e) {
            $db->rollback();
            $_SESSION['errors'] = ['database' => 'Failed to update order: ' . $e->getMessage()];
            $_SESSION['old_input'] = $_POST;
            $this->redirect("/orders/edit/{$id}");
        }
    }

    public function delete(): void
    {
        Auth::requireLogin();

        $id = (int) ($_GET['id'] ?? 0);

        if ($id <= 0) {
            $this->abort(404, 'Order not found');
        }

        $orderModel = new Order();
        $order = $orderModel->find($id);

        if (!$order) {
            $this->abort(404, 'Order not found');
        }

        $itemModel = new OrderItem();
        $items = $itemModel->getByOrder($id);

        $variantModel = new ProductVariant();
        foreach ($items as $item) {
            $variantModel->updateStock((int) $item['variant_id'], (int) $item['quantity']);
        }

        $orderModel->softDelete($id);

        $this->setFlash('success', 'Order deleted successfully!');
        $this->redirect('/orders');
    }

    public function exchange(): void
    {
        Auth::requireLogin();

        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) $this->abort(404, 'Order not found');

        $orderModel = new Order();
        $order = $orderModel->find($id);
        if (!$order) $this->abort(404, 'Order not found');

        if ($order['delivery_status'] !== 'delivered') {
            $this->setFlash('error', 'Only delivered orders can be exchanged.');
            $this->redirect("/orders/{$id}");
            return;
        }

        $variantModel = new ProductVariant();
        $variants = $variantModel->getAllWithProduct();

        $variantJson = [];
        foreach ($variants as $v) {
            $variantJson[$v['product_id']][] = [
                'id'    => $v['id'],
                'sku'   => $v['sku'],
                'size'  => $v['size'],
                'price' => $v['variant_price'] ?? $v['base_price'],
                'stock' => (int) ($v['stock'] ?? 0)
            ];
        }

        $itemModel = new OrderItem();
        $items = $itemModel->getByOrder($id);

        $this->render('orders/exchange', [
            'page_title'  => 'Exchange Order',
            'order'       => $order,
            'items'       => $items,
            'variantJson' => json_encode($variantJson),
            'variants'    => $variants,
            'errors'      => $_SESSION['errors'] ?? [],
        ]);

        unset($_SESSION['errors']);
    }

    public function storeExchange(): void
    {
        Auth::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectBack();
        }

        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) $this->abort(404, 'Order not found');

        $orderModel = new Order();
        $originalOrder = $orderModel->find($id);
        if (!$originalOrder) $this->abort(404, 'Order not found');

        $customerName     = $_POST['customer_name'] ?? '';
        $customerEmail    = $_POST['customer_email'] ?? '';
        $customerPhone    = $_POST['customer_phone'] ?? '';
        $deliveryAddress  = $_POST['delivery_address'] ?? '';
        $notes            = $_POST['notes'] ?? '';
        $paymentMethod    = $_POST['payment_method'] ?? 'cod';
        $paymentStatus    = $_POST['payment_status'] ?? 'unpaid';
        $deliveryStatus   = $_POST['delivery_status'] ?? 'pending';
        $pickupPersonName = $_POST['pickup_person_name'] ?? null;
        $deliveryCharge   = max(0, (float) ($_POST['delivery_charge'] ?? 80));

        $returnVariantIds   = $_POST['return_variant_id'] ?? [];
        $returnProductIds   = $_POST['return_product_id'] ?? [];
        $returnQuantities   = $_POST['return_quantity'] ?? [];
        $returnUnitPrices   = $_POST['return_unit_price'] ?? [];
        $returnAmountTotal  = max(0, (float) ($_POST['return_amount_total'] ?? 0));

        $newProductIds = $_POST['product_id'] ?? [];
        $newVariantIds = $_POST['variant_id'] ?? [];
        $newQuantities = $_POST['quantity'] ?? [];

        $errors = $this->validate(
            ['customer_name' => $customerName, 'customer_phone' => $customerPhone, 'delivery_address' => $deliveryAddress],
            ['customer_name' => 'required|min:2|max:255', 'customer_phone' => 'required|min:5|max:20', 'delivery_address' => 'required|min:5|max:500']
        );

        if (empty($returnVariantIds) && empty($newVariantIds)) {
            $errors['items'] = 'Add at least one returned or new item to the exchange list.';
        }

        if (!in_array($paymentMethod, ['cod', 'bkash', 'bank'])) {
            $errors['payment_method'] = 'Invalid payment method';
        }
        if (!in_array($paymentStatus, ['unpaid', 'paid'])) {
            $errors['payment_status'] = 'Invalid payment status';
        }
        if (!in_array($deliveryStatus, ['pending', 'waiting_for_print', 'package_ready', 'courier_pickup', 'personal_pickup', 'delivered', 'on_hold', 'cancelled', 'returned'])) {
            $errors['delivery_status'] = 'Invalid delivery status';
        }
        if ($deliveryStatus === 'personal_pickup' && (empty($pickupPersonName) || strlen(trim($pickupPersonName)) < 2)) {
            $errors['pickup_person_name'] = 'Pickup person name is required for personal pickup';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $this->redirect("/orders/exchange/{$id}");
            return;
        }

        $variantModel = new ProductVariant();
        $itemModel    = new OrderItem();

        try {
            $db = $orderModel->getConnection();
            $db->beginTransaction();

            $orderNumber = date('Y') . '-' . strtoupper(substr(uniqid(), -5));
            $totalAmount = 0;

            $now = date('Y-m-d H:i:s');
            $timestampFields = [];
            if ($deliveryStatus === 'delivered')  $timestampFields['delivered_at'] = $now;
            elseif ($deliveryStatus === 'cancelled') $timestampFields['cancelled_at'] = $now;
            elseif ($deliveryStatus === 'returned')  $timestampFields['returned_at']  = $now;

            $exchangeOrderId = $orderModel->create(array_merge([
                'exchange_for_order_id' => $id,
                'order_number'          => $orderNumber,
                'customer_name'         => $customerName,
                'customer_email'        => $customerEmail,
                'customer_phone'        => $customerPhone,
                'delivery_address'      => $deliveryAddress,
                'notes'                 => $notes,
                'total_amount'          => 0,
                'payment_method'        => $paymentMethod,
                'payment_status'        => $paymentStatus,
                'delivery_status'       => $deliveryStatus,
                'pickup_person_name'    => $pickupPersonName,
            ], $timestampFields));

            // Process returned items — stock is restored per item; financial total uses the user-edited subtotal
            $totalAmount -= $returnAmountTotal;
            foreach ($returnVariantIds as $i => $variantId) {
                $variantId = (int) $variantId;
                $qty       = (int) ($returnQuantities[$i] ?? 0);
                $price     = (float) ($returnUnitPrices[$i] ?? 0);
                if ($qty <= 0) continue;

                $itemModel->create([
                    'order_id'   => $exchangeOrderId,
                    'product_id' => (int) ($returnProductIds[$i] ?? 0),
                    'variant_id' => $variantId,
                    'quantity'   => $qty,
                    'unit_price' => -$price,
                    'line_total' => -($price * $qty),
                    'is_return'  => 1,
                ]);

                $variantModel->updateStock($variantId, $qty); // restore stock
            }

            // Process new items (positive line totals, deduct stock)
            for ($i = 0; $i < count($newProductIds); $i++) {
                $variantId = (int) ($newVariantIds[$i] ?? 0);
                $qty       = (int) ($newQuantities[$i] ?? 0);
                if ($qty <= 0 || !$variantId) continue;

                $variant   = $variantModel->find($variantId);
                if (!$variant) continue;

                $unitPrice    = (float) ($_POST['unit_price'][$i] ?? 0);
                if ($unitPrice <= 0) {
                    $unitPrice = (float) ($variant['variant_price'] ?? $variant['base_price'] ?? 0);
                }
                $patchesExtra = (float) ($_POST['patches_extra'][$i] ?? 0);
                $namekitExtra = (float) ($_POST['namekit_extra'][$i] ?? 0);
                $kitName      = trim($_POST['kit_name'][$i] ?? '');
                $kitNumber    = trim($_POST['kit_number'][$i] ?? '');
                $lineTotal    = ($unitPrice * $qty) + $patchesExtra + $namekitExtra;
                $totalAmount += $lineTotal;

                $itemModel->create([
                    'order_id'      => $exchangeOrderId,
                    'product_id'    => (int) $newProductIds[$i],
                    'variant_id'    => $variantId,
                    'quantity'      => $qty,
                    'unit_price'    => $unitPrice,
                    'line_total'    => $lineTotal,
                    'patches_extra' => $patchesExtra,
                    'namekit_extra' => $namekitExtra,
                    'kit_name'      => $kitName ?: null,
                    'kit_number'    => $kitNumber ?: null,
                    'is_return'     => 0,
                ]);

                $variantModel->updateStock($variantId, -$qty);
            }

            $orderModel->update($exchangeOrderId, ['total_amount' => $totalAmount + $deliveryCharge]);

            $db->commit();

            $this->setFlash('success', 'Exchange order created successfully!');
            $this->redirect("/orders/{$exchangeOrderId}");

        } catch (\Exception $e) {
            $db->rollback();
            $_SESSION['errors'] = ['database' => 'Failed to create exchange order: ' . $e->getMessage()];
            $this->redirect("/orders/exchange/{$id}");
        }
    }

    public function adjustStock(): void
    {
        Auth::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectBack();
        }

        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) $this->abort(404, 'Order not found');

        $orderModel   = new Order();
        $order        = $orderModel->find($id);
        if (!$order) $this->abort(404, 'Order not found');

        $itemModel    = new OrderItem();
        $variantModel = new ProductVariant();
        $items        = $itemModel->getByOrder($id);

        $db = $orderModel->getConnection();
        $db->beginTransaction();

        try {
            $hasIssue = false;
            foreach ($items as $item) {
                if (($item['is_return'] ?? 0) || ($item['stock_deducted'] ?? 1)) continue;

                $currentStock = (int) ($item['current_stock'] ?? 0);
                $qty          = (int) $item['quantity'];

                if ($currentStock >= $qty) {
                    $variantModel->updateStock((int) $item['variant_id'], -$qty);
                    $db->prepare("UPDATE order_items SET stock_deducted = 1 WHERE id = ?")->execute([(int) $item['id']]);
                } else {
                    $hasIssue = true;
                }
            }

            $orderModel->update($id, ['has_stock_issue' => (int) $hasIssue]);
            $db->commit();

            $msg = $hasIssue
                ? 'Available stock adjusted. Some items still have insufficient stock.'
                : 'Stock adjusted successfully! All items are now fulfilled.';
            $this->setFlash($hasIssue ? 'warning' : 'success', $msg);

        } catch (\Exception $e) {
            $db->rollback();
            $this->setFlash('error', 'Failed to adjust stock: ' . $e->getMessage());
        }

        $this->redirect("/orders/{$id}");
    }

    private function computeStockIssue(array $items, ProductVariant $variantModel, Order $orderModel, int $orderId): bool
    {
        // Any non-return item that hasn't had its stock deducted yet means there is an issue.
        $hasIssue = false;
        foreach ($items as $item) {
            if ($item['is_return'] ?? 0) continue;
            if ($item['stock_deducted'] ?? 1) continue;
            $hasIssue = true;
            break;
        }
        // Only ever set the flag TO 1 here — clearing it is the user's action via Adjust Stock.
        if ($hasIssue && !(int) ($orderModel->find($orderId)['has_stock_issue'] ?? 0)) {
            $orderModel->update($orderId, ['has_stock_issue' => 1]);
        }
        return $hasIssue;
    }

    public function updateStatus(): void
    {
        Auth::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectBack();
        }

        $id = (int) ($_GET['id'] ?? 0);
        $status = $_POST['delivery_status'] ?? '';

        if ($id <= 0) {
            $this->abort(404, 'Order not found');
        }

        $orderModel = new Order();
        if (!$orderModel->find($id)) {
            $this->abort(404, 'Order not found');
        }

        $validStatuses = ['pending', 'waiting_for_print', 'package_ready', 'courier_pickup', 'personal_pickup', 'in_transit', 'delivered', 'on_hold', 'cancelled', 'returned'];
        if (!in_array($status, $validStatuses)) {
            $this->setFlash('error', 'Invalid status');
            $this->redirectBack();
            return;
        }

        $orderModel->updateStatus($id, $status);

        $this->setFlash('success', 'Order status updated successfully!');
        $this->redirectBack();
    }
}
