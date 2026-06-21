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
    <h1 class="ps-2">Exchange Order</h1>
    <p class="text-muted ps-2">Processing exchange for Order <strong>#<?= htmlspecialchars(str_replace('ORD-', '', $order['order_number'])); ?></strong></p>

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

    <!-- Original Order Items (read-only with checkboxes) -->
    <div class="card border-0 mb-4">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Original Items</h5>
            <button type="button" class="btn btn-sm btn-warning" onclick="addToExchangeList()">
                <i class="fas fa-exchange-alt"></i> Add to Exchange List
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:40px;"><input type="checkbox" id="selectAll" onchange="toggleAll(this)"></th>
                        <th>Product</th>
                        <th>Size</th>
                        <th class="text-center">Qty</th>
                        <th class="text-end">Unit Price</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td><input type="checkbox" class="item-check"
                            data-variant-id="<?= $item['variant_id']; ?>"
                            data-product-id="<?= $item['product_id']; ?>"
                            data-product-name="<?= htmlspecialchars($item['product_name']); ?>"
                            data-size="<?= htmlspecialchars($item['size']); ?>"
                            data-quantity="<?= $item['quantity']; ?>"
                            data-unit-price="<?= $item['unit_price']; ?>">
                        </td>
                        <td><?= htmlspecialchars($item['product_name']); ?></td>
                        <td><?= htmlspecialchars($item['size']); ?></td>
                        <td class="text-center"><?= $item['quantity']; ?></td>
                        <td class="text-end">৳<?= number_format($item['unit_price'], 2); ?></td>
                        <td class="text-end">৳<?= number_format($item['line_total'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <form method="POST" action="/orders/exchange/store/<?= $order['id']; ?>">
        <input type="hidden" name="exchange_for_order_id" value="<?= $order['id']; ?>">

        <!-- Customer Information -->
        <div class="card border-0 mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Customer Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Customer Name *</label>
                            <input type="text" name="customer_name" class="form-control" required
                                   value="<?= htmlspecialchars($order['customer_name']); ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="customer_email" class="form-control"
                                   value="<?= htmlspecialchars($order['customer_email']); ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Phone *</label>
                            <input type="tel" name="customer_phone" class="form-control" required
                                   value="<?= htmlspecialchars($order['customer_phone']); ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Delivery Address *</label>
                            <input type="text" name="delivery_address" class="form-control" required
                                   value="<?= htmlspecialchars($order['delivery_address']); ?>">
                        </div>
                    </div>
                </div>

                <!-- Payment & Delivery -->
                <div class="card border-0 bg-light mb-0">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">Payment & Delivery</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Payment Method *</label>
                                    <select name="payment_method" class="form-select" required>
                                        <option value="cod" <?= $order['payment_method'] === 'cod' ? 'selected' : ''; ?>>Cash on Delivery (CoD)</option>
                                        <option value="bkash" <?= $order['payment_method'] === 'bkash' ? 'selected' : ''; ?>>Bkash</option>
                                        <option value="bank" <?= $order['payment_method'] === 'bank' ? 'selected' : ''; ?>>Bank Transfer</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Payment Status *</label>
                                    <select name="payment_status" class="form-select" required>
                                        <option value="unpaid" <?= $order['payment_status'] === 'unpaid' ? 'selected' : ''; ?>>Unpaid</option>
                                        <option value="paid" <?= $order['payment_status'] === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Status *</label>
                                    <select name="delivery_status" id="deliveryStatus" class="form-select" required>
                                        <option value="pending" selected>Pending</option>
                                        <option value="waiting_for_print">Waiting For Print</option>
                                        <option value="package_ready">Package Ready</option>
                                        <option value="courier_pickup">Courier Pickup</option>
                                        <option value="personal_pickup">Personal Pickup</option>
                                        <option value="delivered">Delivered</option>
                                        <option value="on_hold">On Hold</option>
                                        <option value="returned">Returned</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6" id="pickupNameGroup" style="display:none;">
                                <div class="mb-3">
                                    <label class="form-label">Pickup Person's Name *</label>
                                    <input type="text" name="pickup_person_name" class="form-control" placeholder="Name of person picking up">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Exchanged Items -->
        <div class="card border-0 bg-light mb-4">
            <div class="card-header bg-white border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Exchanged Items</h5>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addNewItem()">
                        <i class="fas fa-plus"></i> Add New Item
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div id="exchange-items">
                    <p class="text-muted text-center py-3 mb-0" id="exchange-empty-msg">
                        Select items above and click <strong>Add to Exchange List</strong>, or add new items.
                    </p>
                </div>
            </div>
        </div>

        <!-- Summary -->
        <div class="d-flex justify-content-end mb-4">
            <div class="card border-0 bg-light" style="min-width:300px;">
                <div class="card-body py-3 px-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small">Returned Items</span>
                        <div class="d-flex align-items-center gap-1">
                            <span class="text-danger fw-bold">-৳</span>
                            <input type="number" id="returned-subtotal" name="return_amount_total"
                                   class="form-control form-control-sm text-end text-danger fw-bold"
                                   style="width:100px; border:none; background:transparent;"
                                   value="0" min="0" step="1" oninput="updateExchangeTotal()">
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small">New Items</span>
                        <strong class="text-success">+৳<span id="new-subtotal">0</span></strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <label class="text-muted small mb-0" for="delivery_charge">Delivery Charge</label>
                        <div style="width:110px;">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">৳</span>
                                <input type="number" id="delivery_charge" name="delivery_charge"
                                       class="form-control text-end" value="80" min="0" step="1"
                                       onchange="updateExchangeTotal()">
                            </div>
                        </div>
                    </div>
                    <div class="border-top pt-2">
                        <p class="text-muted small mb-1">Exchange Total</p>
                        <h3 class="mb-0">৳<span id="exchange-total">80</span></h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-control" rows="3">Exchange for order #<?= htmlspecialchars(str_replace('ORD-', '', $order['order_number'])); ?></textarea>
        </div>

        <div class="d-flex gap-2 mb-4">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-exchange-alt"></i> Process Exchange
            </button>
            <a href="/orders/<?= $order['id']; ?>" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
const variantData = <?= $variantJson; ?>;
const productOptionsHtml = <?= json_encode($productOptionsHtml); ?>;
let rowCounter = 0;

// Return items state: array of { variantId, productId, productName, size, quantity, unitPrice }
let returnItems = [];

function toggleAll(checkbox) {
    document.querySelectorAll('.item-check').forEach(c => c.checked = checkbox.checked);
}

function addToExchangeList() {
    const checked = document.querySelectorAll('.item-check:checked');
    if (!checked.length) {
        alert('Please select at least one item.');
        return;
    }

    checked.forEach(cb => {
        const variantId = parseInt(cb.dataset.variantId);
        if (returnItems.find(r => r.variantId === variantId)) return; // skip duplicates

        returnItems.push({
            variantId,
            productId:   parseInt(cb.dataset.productId),
            productName: cb.dataset.productName,
            size:        cb.dataset.size,
            quantity:    parseInt(cb.dataset.quantity),
            unitPrice:   parseFloat(cb.dataset.unitPrice)
        });
        cb.checked = false;
    });

    document.getElementById('selectAll').checked = false;
    renderReturnItems();
    syncReturnedSubtotal();
}

function removeReturnItem(variantId) {
    returnItems = returnItems.filter(r => r.variantId !== variantId);
    renderReturnItems();
    syncReturnedSubtotal();
}

function renderReturnItems() {
    const container = document.getElementById('exchange-items');
    const emptyMsg  = document.getElementById('exchange-empty-msg');

    // Remove existing return rows
    container.querySelectorAll('[data-return-row]').forEach(el => el.remove());
    // Remove existing return hidden inputs
    container.querySelectorAll('[data-return-input]').forEach(el => el.remove());

    if (emptyMsg) emptyMsg.style.display = returnItems.length || container.querySelectorAll('[data-item-row]').length ? 'none' : '';

    returnItems.forEach(item => {
        const lineTotal = item.quantity * item.unitPrice;
        const div = document.createElement('div');
        div.className = 'border rounded p-3 mb-3 bg-white';
        div.style.borderColor = '#dc3545';
        div.setAttribute('data-return-row', item.variantId);
        div.innerHTML = `
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="badge bg-danger me-2">Return</span>
                    <strong>${item.productName}</strong>
                    <span class="text-muted ms-2 small">Size: ${item.size}</span>
                    <span class="text-muted ms-2 small">Qty: ${item.quantity}</span>
                    <span class="text-muted ms-2 small">@ ৳${item.unitPrice.toFixed(0)}</span>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span class="text-danger fw-bold">-৳${lineTotal.toFixed(0)}</span>
                    <button type="button" class="btn btn-link text-danger p-0" onclick="removeReturnItem(${item.variantId})" title="Remove">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <input type="hidden" name="return_variant_id[]" value="${item.variantId}" data-return-input>
            <input type="hidden" name="return_product_id[]" value="${item.productId}" data-return-input>
            <input type="hidden" name="return_quantity[]"   value="${item.quantity}"  data-return-input>
            <input type="hidden" name="return_unit_price[]" value="${item.unitPrice}" data-return-input>
        `;
        // Insert return rows before new item rows
        const firstNewRow = container.querySelector('[data-item-row]');
        if (firstNewRow) {
            container.insertBefore(div, firstNewRow);
        } else {
            container.appendChild(div);
        }
    });

    if (emptyMsg) emptyMsg.style.display = (returnItems.length || container.querySelectorAll('[data-item-row]').length) ? 'none' : '';
}

// ── New item rows (same pattern as form.php) ─────────────────────────────────

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
                <button type="button" class="btn btn-link text-danger p-0" onclick="removeNewItem(${idx})" title="Remove">
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

        <div class="d-flex justify-content-between border-top pt-2 mt-1">
            <span class="badge bg-success align-self-center">New Item</span>
            <div>
                <span class="text-muted small">Row Total: </span>
                <strong>+৳<span id="row-total-${idx}">0</span></strong>
            </div>
        </div>
    </div>`;
}

function addNewItem() {
    const container = document.getElementById('exchange-items');
    const emptyMsg  = document.getElementById('exchange-empty-msg');
    if (emptyMsg) emptyMsg.style.display = 'none';
    const idx = rowCounter++;
    container.insertAdjacentHTML('beforeend', createRowHtml(idx));
    updateExchangeTotal();
}

function removeNewItem(idx) {
    document.querySelector(`[data-item-row="${idx}"]`).remove();
    const container = document.getElementById('exchange-items');
    const emptyMsg  = document.getElementById('exchange-empty-msg');
    if (emptyMsg) emptyMsg.style.display = (returnItems.length || container.querySelectorAll('[data-item-row]').length) ? 'none' : '';
    updateExchangeTotal();
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
        return;
    }

    const stock = parseInt(selectedOpt.dataset.stock) || 0;
    const qty   = parseInt(document.querySelector(`.qty-input-${idx}`)?.value) || 0;

    if (stock === 0) {
        stockInfoEl.innerHTML = '<span class="badge bg-danger">Out of Stock</span>';
    } else if (qty > stock) {
        stockInfoEl.innerHTML = `<span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle"></i> Only ${stock} in stock</span>`;
    } else {
        stockInfoEl.innerHTML = `<span class="text-muted small">In stock: ${stock}</span>`;
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
    const qty          = parseInt(document.querySelector(`.qty-input-${idx}`)?.value) || 0;
    const unitPrice    = parseFloat(document.querySelector(`.unit-price-${idx}`)?.value) || 0;
    const patchesExtra = document.getElementById(`patches-chk-${idx}`)?.checked
        ? (parseFloat(document.querySelector(`.patches-extra-${idx}`)?.value) || 0) : 0;
    const namekitExtra = document.getElementById(`namekit-chk-${idx}`)?.checked
        ? (parseFloat(document.querySelector(`.namekit-extra-${idx}`)?.value) || 0) : 0;
    const rowTotal = (unitPrice * qty) + patchesExtra + namekitExtra;
    const el = document.getElementById(`row-total-${idx}`);
    if (el) el.textContent = rowTotal.toFixed(0);
    updateExchangeTotal();
}

function syncReturnedSubtotal() {
    const total = returnItems.reduce((sum, item) => sum + item.quantity * item.unitPrice, 0);
    const input = document.getElementById('returned-subtotal');
    if (input) input.value = total.toFixed(0);
    updateExchangeTotal();
}

function updateExchangeTotal() {
    const returnedTotal = Math.max(0, parseFloat(document.getElementById('returned-subtotal')?.value) || 0);

    let newTotal = 0;
    document.querySelectorAll('[data-item-row]').forEach(row => {
        const idx       = row.dataset.itemRow;
        const qty       = parseInt(document.querySelector(`.qty-input-${idx}`)?.value) || 0;
        const unitPrice = parseFloat(document.querySelector(`.unit-price-${idx}`)?.value) || 0;
        const patches   = document.getElementById(`patches-chk-${idx}`)?.checked
            ? (parseFloat(document.querySelector(`.patches-extra-${idx}`)?.value) || 0) : 0;
        const namekit   = document.getElementById(`namekit-chk-${idx}`)?.checked
            ? (parseFloat(document.querySelector(`.namekit-extra-${idx}`)?.value) || 0) : 0;
        newTotal += (unitPrice * qty) + patches + namekit;
    });

    const delivery = parseFloat(document.getElementById('delivery_charge')?.value) || 0;
    const total    = newTotal - returnedTotal + delivery;

    document.getElementById('new-subtotal').textContent   = newTotal.toFixed(0);
    document.getElementById('exchange-total').textContent = total.toFixed(0);
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

    updateExchangeTotal();
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
