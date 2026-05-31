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

        $status = $_GET['status'] ?? '';
        $search = $_GET['search'] ?? '';
        $searchType = $_GET['search_type'] ?? 'order_number';
        $page = (int) ($_GET['page'] ?? 1);
        $startDate = $_GET['start_date'] ?? '';
        $endDate = $_GET['end_date'] ?? '';

        $orderModel = new Order();
        $filters = [];

        if ($status) {
            $filters['status'] = $status;
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
            'status' => $status,
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
            'page_title' => 'Create Order',
            'order' => null,
            'variantJson' => json_encode($variantJson),
            'variants' => $variants,
            'errors' => $_SESSION['errors'] ?? [],
            'old' => $_SESSION['old_input'] ?? []
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
        if (!in_array($deliveryStatus, ['pending', 'courier_pickup', 'personal_pickup', 'delivered', 'on_hold', 'cancelled'])) {
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

            $orderId = $orderModel->create([
                'order_number' => $orderNumber,
                'customer_name' => $customerName,
                'customer_email' => $customerEmail,
                'customer_phone' => $customerPhone,
                'delivery_address' => $deliveryAddress,
                'notes' => $notes,
                'status' => 'pending',
                'total_amount' => 0,
                'payment_method' => $paymentMethod,
                'payment_status' => $paymentStatus,
                'delivery_status' => $deliveryStatus,
                'pickup_person_name' => $pickupPersonName
            ]);

            $itemModel = new OrderItem();

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
                $lineTotal = ($unitPrice * $quantity) + $patchesExtra + $namekitExtra;
                $totalAmount += $lineTotal;

                $itemModel->create([
                    'order_id' => $orderId,
                    'product_id' => (int) $productIds[$i],
                    'variant_id' => $variantId,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal
                ]);

                $variantModel->updateStock($variantId, -$quantity);
            }

            $orderModel->update($orderId, ['total_amount' => $totalAmount + $deliveryCharge]);

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

        $itemModel = new OrderItem();
        $items = $itemModel->getByOrder($id);

        $this->render('orders/show', [
            'page_title' => 'Order ' . htmlspecialchars(str_replace('ORD-', '', $order['order_number'])),
            'order' => $order,
            'items' => $items
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

        $itemModel = new OrderItem();
        $existingItems = $itemModel->getByOrder($id);

        $this->render('orders/form', [
            'page_title' => 'Edit Order',
            'order' => $order,
            'variantJson' => json_encode($variantJson),
            'variants' => $variants,
            'existingItems' => $existingItems,
            'errors' => $_SESSION['errors'] ?? [],
            'old' => $_SESSION['old_input'] ?? []
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
        if (!in_array($deliveryStatus, ['pending', 'courier_pickup', 'personal_pickup', 'delivered', 'on_hold', 'cancelled'])) {
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

            // Restore stock for existing items then delete them
            $existingItems = $itemModel->getByOrder($id);
            foreach ($existingItems as $item) {
                $variantModel->updateStock((int) $item['variant_id'], (int) $item['quantity']);
            }
            $db->prepare("DELETE FROM order_items WHERE order_id = ?")->execute([$id]);

            // Re-create items
            $totalAmount = 0;
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
                $lineTotal    = ($unitPrice * $quantity) + $patchesExtra + $namekitExtra;
                $totalAmount += $lineTotal;

                $itemModel->create([
                    'order_id'   => $id,
                    'product_id' => (int) $productIds[$i],
                    'variant_id' => $variantId,
                    'quantity'   => $quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ]);

                $variantModel->updateStock($variantId, -$quantity);
            }

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
            ]);

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

        $orderModel->delete($id);

        $this->setFlash('success', 'Order deleted successfully!');
        $this->redirect('/orders');
    }

    public function updateStatus(): void
    {
        Auth::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectBack();
        }

        $id = (int) ($_GET['id'] ?? 0);
        $status = $_POST['status'] ?? '';

        if ($id <= 0) {
            $this->abort(404, 'Order not found');
        }

        $orderModel = new Order();
        if (!$orderModel->find($id)) {
            $this->abort(404, 'Order not found');
        }

        $validStatuses = ['pending', 'processing', 'shipped', 'in_transit', 'delivered', 'returned', 'incomplete', 'cancelled'];
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
