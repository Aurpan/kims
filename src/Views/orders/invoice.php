<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
    h1 { font-size: 20px; margin: 0 0 4px; }
    .muted { color: #666; }
    table { width: 100%; border-collapse: collapse; margin-top: 16px; }
    th, td { padding: 6px 8px; border-bottom: 1px solid #ddd; text-align: left; }
    th { background: #f5f5f5; }
    .text-right { text-align: right; }
    .header { overflow: hidden; margin-bottom: 20px; }
    .header .left { float: left; }
    .header .right { float: right; text-align: right; }
    .logo-corner { position: fixed; top: 0; right: 0; width: 70px; }
    .watermark-wrap { position: fixed; top: 0; left: 0; width: 100%; height: 100%; text-align: center; z-index: -1; }
    .watermark { width: 320px; margin-top: 380px; opacity: 0.08; }
    .totals { width: 260px; margin-left: auto; margin-top: 16px; }
    .totals td { border: none; padding: 4px 8px; }
    .totals .grand { font-weight: bold; font-size: 14px; border-top: 2px solid #333; }
    .extra { color: #555; font-size: 10px; }
    .footer { margin-top: 40px; border-top: 1px solid #ddd; padding-top: 12px; font-size: 10px; }
    .footer td { border: none; padding: 2px 8px; }
</style>
</head>
<body>
    <img class="logo-corner" src="<?= $logoDataUri; ?>">
    <div class="watermark-wrap">
        <img class="watermark" src="<?= $logoDataUri; ?>">
    </div>

    <div class="header">
        <div class="left">
            <h1><?= htmlspecialchars(APP_NAME); ?></h1>
            <div class="muted">Invoice</div>
        </div>
        <div class="right">
            <div><strong>Invoice #:</strong> <?= htmlspecialchars(str_replace('ORD-', '', $order['order_number'])); ?></div>
            <div><strong>Date:</strong> <?= date('M d, Y', strtotime($order['created_at'])); ?></div>
        </div>
    </div>

    <table style="width:100%; border:none; margin-top:0;">
        <tr>
            <td style="border:none; width:50%; vertical-align:top;">
                <strong>Bill To</strong><br>
                <?= htmlspecialchars($order['customer_name']); ?><br>
                <?= htmlspecialchars($order['customer_phone']); ?><br>
                <?php if (!empty($order['customer_email'])): ?><?= htmlspecialchars($order['customer_email']); ?><br><?php endif; ?>
                <?= nl2br(htmlspecialchars($order['delivery_address'])); ?>
            </td>
            <td style="border:none; width:50%; vertical-align:top;">
                <strong>Payment Method:</strong> <?= htmlspecialchars(['cod' => 'Cash on Delivery', 'bkash' => 'Bkash', 'bank' => 'Bank Transfer'][$order['payment_method']] ?? $order['payment_method']); ?>
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Size</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Unit Price</th>
                <th class="text-right">Line Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td>
                        <?= htmlspecialchars($item['product_name']); ?>
                        <?php if (!empty($item['is_return'])): ?> (Returned)<?php endif; ?>
                        <?php if (!empty($item['kit_name']) || !empty($item['kit_number'])): ?>
                            <div class="extra">Name-Kit: <?= htmlspecialchars($item['kit_name']); ?> #<?= htmlspecialchars($item['kit_number']); ?></div>
                        <?php endif; ?>
                        <?php if (!empty($item['patches_extra']) && $item['patches_extra'] > 0): ?>
                            <div class="extra">Patches</div>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($item['size']); ?></td>
                    <td class="text-right"><?= (int) $item['quantity']; ?></td>
                    <td class="text-right"><?= number_format($item['unit_price'], 2); ?></td>
                    <td class="text-right"><?= number_format($item['line_total'], 2); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td>Items Subtotal</td>
            <td class="text-right">Tk <?= number_format($itemsSubtotal, 2); ?></td>
        </tr>
        <tr>
            <td>Delivery Charge</td>
            <td class="text-right">Tk <?= number_format($deliveryCharge, 2); ?></td>
        </tr>
        <tr class="grand">
            <td>Total</td>
            <td class="text-right">Tk <?= number_format($order['total_amount'], 2); ?></td>
        </tr>
    </table>

    <?php if (!empty($order['notes'])): ?>
        <div style="margin-top: 24px;">
            <strong>Notes</strong>
            <p><?= nl2br(htmlspecialchars($order['notes'])); ?></p>
        </div>
    <?php endif; ?>

    <table class="footer">
        <tr>
            <td style="border:none; width:50%; vertical-align:top;">
                <strong>Contact Us</strong><br>
                Email: kitzoholicbd@gmail.com<br>
                Contact Number: 01913551105<br>
                Facebook: www.facebook.com/kitzoholicbd
            </td>
            <td style="border:none; width:50%; vertical-align:top;">
                <strong>Payment Options</strong><br>
                BKash: 01680762256 (Send Money)<br>
                Bank Details:<br>
                Bank Name: Eastern Bank Ltd<br>
                Acc No: 1091510002206<br>
                Acc Name: Aurpan Dash<br>
                Branch: Banasree<br>
                Routing: 095260721
            </td>
        </tr>
    </table>
</body>
</html>
