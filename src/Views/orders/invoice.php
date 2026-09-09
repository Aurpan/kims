<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
    #invoice-content { padding: 28px 32px 90px; }
    h1 { font-size: 32px; margin: 0 0 4px; letter-spacing: 1px; }
    .muted { color: #666; }
    table { width: 100%; border-collapse: collapse; margin-top: 16px; }
    th, td { padding: 6px 8px; border-bottom: 1px solid #ddd; text-align: left; }
    th { background: #f5f5f5; }
    .text-right { text-align: right; }
    .logo-corner { position: fixed; top: 20px; left: 20px; width: 55px; }
    .header-brand { text-align: center; margin-bottom: 12px; }
    .header-brand .muted { font-size: 24px; }
    .header-meta { text-align: right; margin-bottom: 4px; }
    .watermark-wrap { position: fixed; top: 0; left: 0; width: 100%; height: 100%; text-align: center; }
    .watermark { width: 320px; margin-top: 380px; opacity: 0.08; }
    /* ponytail: PDF watermark centering assumes a single A4 page (fixed 380px offset); revisit if invoices ever span multiple pages */
    .totals { width: 260px; margin-left: auto; margin-top: 16px; }
    .totals td { border: none; padding: 4px 8px; }
    .totals .grand { font-weight: bold; font-size: 14px; border-top: 2px solid #333; }
    .extra { color: #555; font-size: 10px; }
    .footer { position: fixed; bottom: 0; left: 0; width: 100%; border-top: 1px solid #ddd; padding-top: 12px; font-size: 10px; }
    .footer .left, .footer .right { display: inline-block; width: 48%; vertical-align: top; }
    .footer .left { text-align: left; }
    .footer .right { text-align: right; }
    .contact-icon { width: 10px; height: 10px; }
    .footer a { color: #222; text-decoration: none; }
    .bill-to-row { margin-top: 16px; }
    .bill-to-row .left, .bill-to-row .right { display: inline-block; width: 48%; vertical-align: top; text-align: left; }
    <?php if (!empty($imageExport)): ?>
    /* Image export is a single A4-sized image (same page size as the PDF), rendered
       off-screen (not display:none, so it still lays out and is capturable by html2canvas)
       while the visible page just shows a status message.
       ponytail: min-height (not a hard height + overflow:hidden) lets a long item list grow
       the image taller than one A4 page rather than truncating content — the trade-off is
       the output then isn't exactly A4-proportioned for very long orders. */
    #invoice-content {
        position: fixed; top: 0; left: -99999px;
        width: 794px; min-height: 1123px; box-sizing: border-box;
        padding-top: 60px; padding-left: 60px; padding-right: 60px;
        /* bottom = 60px outer margin (matching top/left/right) + ~100px reserved so the
           footer, absolutely positioned 60px above the container's edge, never overlaps
           the in-flow content (totals table) above it. */
        padding-bottom: 160px;
    }
    /* position:fixed always escapes to the real viewport regardless of ancestors, so the
       logo must switch to absolute (relative to #invoice-content) to move off-screen with it. */
    .logo-corner { position: absolute; }
    .watermark-wrap {
        position: absolute; top: 0; left: 0; right: 0; bottom: 0; width: auto; height: auto;
        display: flex; align-items: center; justify-content: center;
    }
    .watermark { margin-top: 0; }
    /* Pinned to the bottom of #invoice-content (which has a real min-height) instead of
       document flow, so the footer always sits at the bottom of the page like in the PDF. */
    .footer {
        position: absolute; bottom: 60px; left: 0; width: 100%; box-sizing: border-box;
        padding-left: 60px; padding-right: 60px;
    }
    <?php endif; ?>
</style>
</head>
<body>
    <?php if (!empty($imageExport)): ?>
    <div style="padding: 60px; text-align: center; font-family: sans-serif; color: #555;">Preparing your invoice image for download&hellip;</div>
    <?php endif; ?>
    <div id="invoice-content">
    <img class="logo-corner" src="<?= $logoDataUri; ?>">
    <div class="header-brand">
        <h1>KITZOHOLIC</h1>
        <div class="muted">Invoice</div>
    </div>
    <div class="header-meta">
        <div><strong>Invoice #:</strong> <?= htmlspecialchars(str_replace('ORD-', '', $order['order_number'])); ?></div>
        <div><strong>Date:</strong> <?= date('M d, Y', strtotime($order['created_at'])); ?></div>
    </div>

    <div class="bill-to-row">
        <div class="left">
            <div><strong>Bill To</strong></div>
            <div><?= htmlspecialchars($order['customer_name']); ?></div>
            <div><?= htmlspecialchars($order['customer_phone']); ?></div>
            <?php if (!empty($order['customer_email'])): ?><div><?= htmlspecialchars($order['customer_email']); ?></div><?php endif; ?>
            <div><?= nl2br(htmlspecialchars($order['delivery_address'])); ?></div>
        </div>
        <div class="right">
            <strong>Payment Method:</strong> <?= htmlspecialchars(['cod' => 'Cash on Delivery', 'bkash' => 'Bkash', 'bank' => 'Bank Transfer'][$order['payment_method']] ?? $order['payment_method']); ?>
        </div>
    </div>

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
                    <td class="text-right"><?= number_format($item['unit_price'], 0); ?></td>
                    <td class="text-right"><?= number_format($item['line_total'], 0); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td>Items Subtotal</td>
            <td class="text-right">Tk <?= number_format($itemsSubtotal, 0); ?></td>
        </tr>
        <tr>
            <td>Delivery Charge</td>
            <td class="text-right">Tk <?= number_format($deliveryCharge, 0); ?></td>
        </tr>
        <tr class="grand">
            <td>Total</td>
            <td class="text-right">Tk <?= number_format($order['total_amount'], 0); ?></td>
        </tr>
    </table>

    <?php
        $iconEmail = 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#333"><path d="M2 4h20v16H2V4zm2 2.24V6h16v.24l-8 5.99-8-5.99zM4 8.51V18h16V8.51l-7.4 5.55a1 1 0 0 1-1.2 0L4 8.51z"/></svg>');
        $iconPhone = 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#333"><path d="M6.62 10.79a15.05 15.05 0 0 0 6.59 6.59l2.2-2.2a1 1 0 0 1 1.02-.24c1.12.37 2.33.57 3.57.57a1 1 0 0 1 1 1V20a1 1 0 0 1-1 1C10.4 21 3 13.6 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.24.2 2.45.57 3.57a1 1 0 0 1-.25 1.02l-2.2 2.2z"/></svg>');
        $iconFacebook = 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#333"><path d="M22 12a10 10 0 1 0-11.6 9.88v-6.99H7.9V12h2.5V9.8c0-2.47 1.47-3.84 3.72-3.84 1.08 0 2.21.19 2.21.19v2.43h-1.25c-1.23 0-1.61.76-1.61 1.55V12h2.74l-.44 2.89h-2.3v6.99A10 10 0 0 0 22 12z"/></svg>');
    ?>
    <div class="footer">
        <div class="left">
            <div><strong>Payment Options</strong></div>
            <div><strong>BKash: 01680762256 (Send Money)</strong></div>
            <div><strong>Bank Details:</strong></div>
            <div>Eastern Bank Ltd</div>
            <div>Acc No: 1091510002206</div>
            <div>Acc Name: Aurpan Dash</div>
            <div>Branch: Banasree (Dhaka South)</div>
            <div>Routing: 095260721</div>
        </div>
        <div class="right">
            <div><strong>Contact Us</strong></div>
            <div><img class="contact-icon" src="<?= $iconEmail; ?>"> kitzoholicbd@gmail.com</div>
            <div><img class="contact-icon" src="<?= $iconPhone; ?>"> 01913551105</div>
            <div><img class="contact-icon" src="<?= $iconFacebook; ?>"> <a href="https://www.facebook.com/kitzoholicbd">kitzoholicbd</a></div>
        </div>
    </div>

    <div class="watermark-wrap">
        <img class="watermark" src="<?= $logoDataUri; ?>">
    </div>
    </div>

    <?php if (!empty($imageExport)): ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script>
        window.addEventListener('load', function () {
            html2canvas(document.getElementById('invoice-content'), { scale: 2, useCORS: true }).then(function (canvas) {
                var link = document.createElement('a');
                link.download = '<?= addslashes($downloadName); ?>.jpg';
                link.href = canvas.toDataURL('image/jpeg', 0.95);
                link.click();

                // When loaded inside the hidden iframe used by the Order Details page,
                // let the parent page know the download fired so it can stop the button spinner.
                if (window.parent !== window) {
                    window.parent.postMessage({ type: 'kims-invoice-image-ready' }, window.location.origin);
                }
            });
        });
    </script>
    <?php endif; ?>
</body>
</html>
