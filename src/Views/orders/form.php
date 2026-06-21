<?php include __DIR__ . '/../layouts/header.php'; ?>
<?php include __DIR__ . '/../layouts/sidebar.php'; ?>

<?php
$uniqueProducts = [];
foreach ($variants as $v) {
    if (!isset($uniqueProducts[$v['product_id']])) {
        $uniqueProducts[$v['product_id']] = $v['product_name'];
    }
}
$productOptionsHtml = '<option value="">Select Product</option>';
foreach ($uniqueProducts as $pid => $pname) {
    $productOptionsHtml .= '<option value="' . $pid . '">' . htmlspecialchars($pname) . '</option>';
}
?>

<div class="py-4">
    <h1 class="ps-2"><?= $order ? 'Edit Order' : 'Create Order'; ?></h1>
    <p class="text-muted ps-2"><?= $order ? 'Update order information' : 'Create a new customer order'; ?></p>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <strong>Please fix:</strong>
            <ul class="mb-0 mt-2">
                <?php foreach ($errors as $field => $message): ?>
                    <li><?= htmlspecialchars($message); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php
        $formUndeducted = array_filter($existingItems ?? [], fn($item) =>
            !($item['is_return'] ?? 0) && !($item['stock_deducted'] ?? 1)
        );
        $formAdjustable = array_filter($formUndeducted, fn($item) =>
            (int)($item['current_stock'] ?? 0) >= (int)$item['quantity']
        );
    ?>
    <?php if (count($formUndeducted) > 0 && $order): ?>
        <div class="alert alert-warning d-flex align-items-center gap-2 mb-3" data-permanent>
            <i class="fas fa-exclamation-triangle fa-lg flex-shrink-0"></i>
            <div class="flex-grow-1">
                <strong>Stock Unavailable</strong> — One or more items lack sufficient stock.
            </div>
            <?php if (count($formAdjustable) > 0): ?>
            <form method="POST" action="/orders/<?= $order['id']; ?>/adjustStock" class="flex-shrink-0">
                <button type="submit" class="btn btn-warning btn-sm"
                        onclick="return confirm('Deduct available stock now for items that can be fulfilled?')">
                    <i class="fas fa-boxes"></i> Adjust Order
                </button>
            </form>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="card border-0 mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Customer Information</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= $order ? '/orders/update/' . $order['id'] : '/orders'; ?>">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Customer Name *</label>
                            <input type="text" name="customer_name" class="form-control" required
                                   value="<?= htmlspecialchars($order['customer_name'] ?? $old['customer_name'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="customer_email" class="form-control"
                                   value="<?= htmlspecialchars($order['customer_email'] ?? $old['customer_email'] ?? ''); ?>">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Phone *</label>
                            <input type="tel" name="customer_phone" class="form-control" required
                                   value="<?= htmlspecialchars($order['customer_phone'] ?? $old['customer_phone'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Delivery Address *</label>
                            <input type="text" name="delivery_address" class="form-control" required
                                   value="<?= htmlspecialchars($order['delivery_address'] ?? $old['delivery_address'] ?? ''); ?>">
                        </div>
                    </div>
                </div>

                <!-- Payment & Delivery Information -->
                <div class="card border-0 bg-light mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">Payment & Delivery</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Payment Method *</label>
                                    <select name="payment_method" class="form-select" required>
                                        <option value="cod" <?= ($order['payment_method'] ?? $old['payment_method'] ?? '') === 'cod' ? 'selected' : ''; ?>>Cash on Delivery (CoD)</option>
                                        <option value="bkash" <?= ($order['payment_method'] ?? $old['payment_method'] ?? '') === 'bkash' ? 'selected' : ''; ?>>Bkash</option>
                                        <option value="bank" <?= ($order['payment_method'] ?? $old['payment_method'] ?? '') === 'bank' ? 'selected' : ''; ?>>Bank Transfer</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Payment Status *</label>
                                    <select name="payment_status" class="form-select" required>
                                        <option value="unpaid" <?= ($order['payment_status'] ?? $old['payment_status'] ?? '') === 'unpaid' ? 'selected' : ''; ?>>Unpaid</option>
                                        <option value="paid" <?= ($order['payment_status'] ?? $old['payment_status'] ?? '') === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Status *
                                        <?php if ($hasStockIssue ?? false): ?>
                                            <span class="badge bg-warning text-dark ms-1"><i class="fas fa-exclamation-triangle"></i> Stock Unavailable</span>
                                        <?php endif; ?>
                                    </label>
                                    <div id="stock-issue-warning" class="alert alert-warning py-1 px-2 mb-2 small" style="display:none;">
                                        <i class="fas fa-exclamation-triangle"></i> Stock unavailable — status locked to Pending.
                                    </div>
                                    <select name="delivery_status" id="deliveryStatus" class="form-select" required
                                            data-server-locked="<?= ($hasStockIssue ?? false) ? '1' : '0'; ?>"
                                            <?= ($hasStockIssue ?? false) ? 'disabled' : ''; ?>>
                                        <option value="pending" <?= ($order['delivery_status'] ?? $old['delivery_status'] ?? 'pending') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="waiting_for_print" <?= ($order['delivery_status'] ?? $old['delivery_status'] ?? '') === 'waiting_for_print' ? 'selected' : ''; ?>>Waiting For Print</option>
                                        <option value="package_ready" <?= ($order['delivery_status'] ?? $old['delivery_status'] ?? '') === 'package_ready' ? 'selected' : ''; ?>>Package Ready</option>
                                        <option value="courier_pickup" <?= ($order['delivery_status'] ?? $old['delivery_status'] ?? '') === 'courier_pickup' ? 'selected' : ''; ?>>Courier Pickup</option>
                                        <option value="personal_pickup" <?= ($order['delivery_status'] ?? $old['delivery_status'] ?? '') === 'personal_pickup' ? 'selected' : ''; ?>>Personal Pickup</option>
                                        <option value="delivered" <?= ($order['delivery_status'] ?? $old['delivery_status'] ?? '') === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                        <option value="on_hold" <?= ($order['delivery_status'] ?? $old['delivery_status'] ?? '') === 'on_hold' ? 'selected' : ''; ?>>On Hold</option>
                                        <option value="returned" <?= ($order['delivery_status'] ?? $old['delivery_status'] ?? '') === 'returned' ? 'selected' : ''; ?>>Returned</option>
                                        <option value="cancelled" <?= ($order['delivery_status'] ?? $old['delivery_status'] ?? '') === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                    <?php if ($hasStockIssue ?? false): ?>
                                        <input type="hidden" name="delivery_status" value="pending">
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6" id="pickupNameGroup" style="display: none;">
                                <div class="mb-3">
                                    <label class="form-label">Pickup Person's Name *</label>
                                    <input type="text" name="pickup_person_name" class="form-control"
                                           value="<?= htmlspecialchars($order['pickup_person_name'] ?? $old['pickup_person_name'] ?? ''); ?>"
                                           placeholder="Name of person picking up">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="card border-0 bg-light mb-4">
                    <div class="card-header bg-white border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Order Items</h5>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addOrderItem()">
                                <i class="fas fa-plus"></i> Add Item
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="order-items"></div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mb-4">
                    <div class="card border-0 bg-light" style="min-width: 280px;">
                        <div class="card-body py-3 px-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted small">Items Subtotal</span>
                                <strong>৳<span id="order-subtotal">0</span></strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <label class="text-muted small mb-0" for="delivery_charge">Delivery Charge</label>
                                <div style="width: 110px;">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">৳</span>
                                        <input type="number" id="delivery_charge" name="delivery_charge"
                                               class="form-control text-end"
                                               value="<?= htmlspecialchars((string)($order['delivery_charge'] ?? $old['delivery_charge'] ?? 80)); ?>"
                                               min="0" step="1" onchange="updateOrderTotal()">
                                    </div>
                                </div>
                            </div>
                            <div class="border-top pt-2">
                                <p class="text-muted small mb-1">Order Total</p>
                                <h3 class="mb-0">৳<span id="order-total">0</span></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="3"><?= htmlspecialchars($order['notes'] ?? $old['notes'] ?? ''); ?></textarea>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> <?= $order ? 'Update Order' : 'Create Order'; ?>
                    </button>
                    <a href="/orders" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const variantData = <?= $variantJson; ?>;
const productOptionsHtml = <?= json_encode($productOptionsHtml); ?>;
let rowCounter = 0;

function createRowHtml(idx) {
    return `
    <div class="border rounded p-3 mb-3 bg-white" data-item-row="${idx}">
        <div class="row g-2 align-items-end mb-1">
            <div class="col-12 col-md-4">
                <label class="form-label small text-muted mb-1">Product</label>
                <select name="product_id[]" class="form-select" onchange="updateVariants(${idx})">${productOptionsHtml}</select>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label small text-muted mb-1">Size</label>
                <select name="variant_id[]" class="form-select variant-select-${idx}" onchange="onVariantChange(${idx})">
                    <option value="">Select Size</option>
                </select>
            </div>
            <div class="col-4 col-md-2">
                <label class="form-label small text-muted mb-1">Qty</label>
                <input type="number" name="quantity[]" class="form-control qty-input-${idx}" value="1" min="1" onchange="updateRowTotal(${idx}); checkStock(${idx});">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1">Unit Price (৳)</label>
                <input type="number" name="unit_price[]" class="form-control unit-price-${idx}" value="0" step="1" min="0" onchange="updateRowTotal(${idx})">
            </div>
            <div class="col-2 col-md-1 d-flex align-items-end pb-1">
                <button type="button" class="btn btn-link text-danger p-0" onclick="removeOrderItem(${idx})" title="Remove">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
        <div id="stock-info-${idx}" class="mb-2" style="min-height:1.3em;"></div>

        <div class="d-flex gap-3 mb-2">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="patches-chk-${idx}" onchange="togglePatches(${idx})">
                <label class="form-check-label small" for="patches-chk-${idx}">WC Patches</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="namekit-chk-${idx}" onchange="toggleNameKit(${idx})">
                <label class="form-check-label small" for="namekit-chk-${idx}">Name-Kit Number</label>
            </div>
        </div>

        <div id="extras-row-${idx}" class="row g-2 mb-2" style="display:none;">
            <div id="patches-amount-col-${idx}" class="col-6 col-md-3" style="display:none;">
                <label class="form-label small text-muted mb-1">Patches Amount (৳)</label>
                <input type="number" name="patches_extra[]" class="form-control patches-extra-${idx}" value="0" step="1" min="0" onchange="updateRowTotal(${idx})">
            </div>
            <div id="namekit-name-col-${idx}" class="col-12 col-md-3" style="display:none;">
                <label class="form-label small text-muted mb-1">Name</label>
                <input type="text" name="kit_name[]" class="form-control" placeholder="Player name">
            </div>
            <div id="namekit-number-col-${idx}" class="col-6 col-md-2" style="display:none;">
                <label class="form-label small text-muted mb-1">Number</label>
                <input type="text" name="kit_number[]" class="form-control" placeholder="Kit no.">
            </div>
            <div id="namekit-amount-col-${idx}" class="col-6 col-md-3" style="display:none;">
                <label class="form-label small text-muted mb-1">Name-Kit Amount (৳)</label>
                <input type="number" name="namekit_extra[]" class="form-control namekit-extra-${idx}" value="0" step="1" min="0" onchange="updateRowTotal(${idx})">
            </div>
        </div>

        <div class="text-end border-top pt-2 mt-1">
            <span class="text-muted small">Row Total: </span>
            <strong>৳<span id="row-total-${idx}">0</span></strong>
        </div>
    </div>`;
}

function addOrderItem() {
    const idx = rowCounter++;
    document.getElementById('order-items').insertAdjacentHTML('beforeend', createRowHtml(idx));
}

function removeOrderItem(idx) {
    document.querySelector(`[data-item-row="${idx}"]`).remove();
    updateOrderTotal();
}

function updateVariants(idx) {
    const productSelect = document.querySelector(`[data-item-row="${idx}"] select[name="product_id[]"]`);
    const variantSelect = document.querySelector(`.variant-select-${idx}`);
    const productId = productSelect.value;

    variantSelect.innerHTML = '<option value="">Select Size</option>';
    document.querySelector(`.unit-price-${idx}`).value = 0;
    updateRowTotal(idx);

    if (productId && variantData[productId]) {
        variantData[productId].forEach(v => {
            const opt = document.createElement('option');
            opt.value = v.id;
            opt.textContent = `${v.size} (${v.sku})`;
            opt.dataset.price = v.price;
            opt.dataset.stock = v.stock;
            variantSelect.appendChild(opt);
        });
    }
    checkStock(idx);
}

function onVariantChange(idx) {
    const variantSelect = document.querySelector(`.variant-select-${idx}`);
    const selectedOpt = variantSelect.options[variantSelect.selectedIndex];
    const price = parseFloat(selectedOpt?.dataset.price) || 0;
    document.querySelector(`.unit-price-${idx}`).value = price;
    updateRowTotal(idx);
    checkStock(idx);
}

function checkStock(idx) {
    const variantSelect = document.querySelector(`.variant-select-${idx}`);
    const stockInfoEl = document.getElementById(`stock-info-${idx}`);
    if (!stockInfoEl) return;

    const selectedOpt = variantSelect?.options[variantSelect.selectedIndex];
    if (!variantSelect?.value || selectedOpt?.dataset.stock === undefined) {
        stockInfoEl.innerHTML = '';
        updateDeliveryDropdownState();
        return;
    }

    const stock = parseInt(selectedOpt.dataset.stock) || 0;
    const qty = parseInt(document.querySelector(`.qty-input-${idx}`)?.value) || 0;

    if (stock === 0) {
        stockInfoEl.innerHTML = '<span class="badge bg-danger">Out of Stock</span>';
    } else if (qty > stock) {
        stockInfoEl.innerHTML = `<span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle"></i> Only ${stock} in stock</span>`;
    } else {
        stockInfoEl.innerHTML = `<span class="text-muted small">In stock: ${stock}</span>`;
    }
    updateDeliveryDropdownState();
}

function updateDeliveryDropdownState() {
    const deliverySelect = document.getElementById('deliveryStatus');
    if (!deliverySelect || deliverySelect.dataset.serverLocked === '1') return;

    let hasIssue = false;
    document.querySelectorAll('[data-item-row]').forEach(row => {
        const idx         = row.dataset.itemRow;
        const variantSel  = document.querySelector(`.variant-select-${idx}`);
        const selectedOpt = variantSel?.options[variantSel.selectedIndex];
        if (!variantSel?.value) return;
        const stock = parseInt(selectedOpt?.dataset.stock) || 0;
        const qty   = parseInt(document.querySelector(`.qty-input-${idx}`)?.value) || 0;
        if (stock === 0 || qty > stock) hasIssue = true;
    });

    const stockWarning = document.getElementById('stock-issue-warning');
    if (hasIssue) {
        deliverySelect.value    = 'pending';
        deliverySelect.disabled = true;
        if (stockWarning) stockWarning.style.display = '';
    } else {
        deliverySelect.disabled = false;
        if (stockWarning) stockWarning.style.display = 'none';
    }
}

function updateExtrasRow(idx) {
    const patchesChecked = document.getElementById(`patches-chk-${idx}`).checked;
    const namekitChecked = document.getElementById(`namekit-chk-${idx}`).checked;
    document.getElementById(`extras-row-${idx}`).style.display = (patchesChecked || namekitChecked) ? '' : 'none';
}

function togglePatches(idx) {
    const checked = document.getElementById(`patches-chk-${idx}`).checked;
    document.getElementById(`patches-amount-col-${idx}`).style.display = checked ? '' : 'none';
    document.querySelector(`.patches-extra-${idx}`).value = checked ? 120 : 0;
    updateExtrasRow(idx);
    updateRowTotal(idx);
}

function toggleNameKit(idx) {
    const checked = document.getElementById(`namekit-chk-${idx}`).checked;
    const show = checked ? '' : 'none';
    document.getElementById(`namekit-name-col-${idx}`).style.display = show;
    document.getElementById(`namekit-number-col-${idx}`).style.display = show;
    document.getElementById(`namekit-amount-col-${idx}`).style.display = show;
    document.querySelector(`.namekit-extra-${idx}`).value = checked ? 250 : 0;
    updateExtrasRow(idx);
    updateRowTotal(idx);
}

function updateRowTotal(idx) {
    const qty = parseInt(document.querySelector(`.qty-input-${idx}`)?.value) || 0;
    const unitPrice = parseFloat(document.querySelector(`.unit-price-${idx}`)?.value) || 0;
    const patchesChecked = document.getElementById(`patches-chk-${idx}`)?.checked;
    const namekitChecked = document.getElementById(`namekit-chk-${idx}`)?.checked;
    const patchesExtra = patchesChecked ? (parseFloat(document.querySelector(`.patches-extra-${idx}`)?.value) || 0) : 0;
    const namekitExtra = namekitChecked ? (parseFloat(document.querySelector(`.namekit-extra-${idx}`)?.value) || 0) : 0;
    const rowTotal = (unitPrice * qty) + patchesExtra + namekitExtra;
    const el = document.getElementById(`row-total-${idx}`);
    if (el) el.textContent = rowTotal.toFixed(0);
    updateOrderTotal();
}

function updateOrderTotal() {
    let subtotal = 0;
    document.querySelectorAll('[data-item-row]').forEach(row => {
        const idx = row.dataset.itemRow;
        const qty = parseInt(document.querySelector(`.qty-input-${idx}`)?.value) || 0;
        const unitPrice = parseFloat(document.querySelector(`.unit-price-${idx}`)?.value) || 0;
        const patchesChecked = document.getElementById(`patches-chk-${idx}`)?.checked;
        const namekitChecked = document.getElementById(`namekit-chk-${idx}`)?.checked;
        const patchesExtra = patchesChecked ? (parseFloat(document.querySelector(`.patches-extra-${idx}`)?.value) || 0) : 0;
        const namekitExtra = namekitChecked ? (parseFloat(document.querySelector(`.namekit-extra-${idx}`)?.value) || 0) : 0;
        subtotal += (unitPrice * qty) + patchesExtra + namekitExtra;
    });
    const deliveryCharge = parseFloat(document.getElementById('delivery_charge')?.value) || 0;
    document.getElementById('order-subtotal').textContent = subtotal.toFixed(0);
    document.getElementById('order-total').textContent = (subtotal + deliveryCharge).toFixed(0);
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelector('form').addEventListener('keydown', function (e) {
        if (e.key === 'Enter') e.preventDefault();
    });

    const deliveryStatusSelect = document.getElementById('deliveryStatus');
    if (deliveryStatusSelect) {
        function togglePickupNameField() {
            const pickupNameGroup = document.getElementById('pickupNameGroup');
            if (deliveryStatusSelect.value === 'personal_pickup') {
                pickupNameGroup.style.display = 'block';
                pickupNameGroup.querySelector('input').required = true;
            } else {
                pickupNameGroup.style.display = 'none';
                pickupNameGroup.querySelector('input').required = false;
            }
        }
        deliveryStatusSelect.addEventListener('change', togglePickupNameField);
        togglePickupNameField();
    }

    <?php if ($order && !empty($existingItems)): ?>
    const existingItems = <?= json_encode($existingItems); ?>;
    existingItems.forEach(item => {
        const idx = rowCounter++;
        document.getElementById('order-items').insertAdjacentHTML('beforeend', createRowHtml(idx));

        const productSelect = document.querySelector(`[data-item-row="${idx}"] select[name="product_id[]"]`);
        productSelect.value = item.product_id;
        updateVariants(idx);

        const variantSelect = document.querySelector(`.variant-select-${idx}`);
        variantSelect.value = item.variant_id;

        document.querySelector(`.qty-input-${idx}`).value = item.quantity;
        document.querySelector(`.unit-price-${idx}`).value = item.unit_price;
        checkStock(idx);
        updateRowTotal(idx);
    });
    <?php else: ?>
    addOrderItem();
    <?php endif; ?>
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
