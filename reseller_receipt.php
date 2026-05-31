<?php
require_once __DIR__ . '/config.php';
require_role('reseller');

$receipt = sanitize_text($_GET['receipt'] ?? '');
if ($receipt === '') {
    redirect_with_message('/monleisiopao/reseller-kiosk.php', 'error', 'Missing receipt number.');
}

$stmt = $mysqli->prepare("SELECT sr.product_id, sr.quantity, sr.amount, sr.created_at, sr.payment_method, sr.cash_received, sr.cash_change, i.name, i.sku, i.price_per_unit FROM sales_records sr JOIN inventory i ON i.id = sr.product_id WHERE sr.receipt_no = ? AND sr.user_id = ? ORDER BY sr.id ASC");
$stmt->bind_param('si', $receipt, $user['id']);
$stmt->execute();
$res = $stmt->get_result();
$lines = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (!$lines) {
    redirect_with_message('/monleisiopao/reseller-kiosk.php', 'error', 'Receipt not found.');
}

$total = 0.0; foreach ($lines as $l) { $total += (float)$l['amount']; }
$user = current_user();
$dateStr = date('Y-m-d H:i', strtotime($lines[0]['created_at'] ?? 'now'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Receipt <?php echo htmlspecialchars($receipt); ?></title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root{--brand:#8c1c2f}
    body{font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; background:#f6f6f6;}
    .paper{width:420px;max-width:100%;margin:20px auto;background:#fff;box-shadow:0 2px 8px rgba(0,0,0,.08);padding:16px 16px 20px;border-radius:8px}
    .header{display:flex;gap:12px;align-items:center;border-bottom:2px solid #f0f0f0;padding-bottom:10px;margin-bottom:10px}
    .logo{width:48px;height:48px;border-radius:8px;object-fit:cover}
    .brand-block{display:flex;flex-direction:column}
    .brand{font-weight:900;color:var(--brand);font-size:18px;line-height:1}
    .muted{color:#666;font-size:12px}
    .meta{display:flex;justify-content:space-between;gap:8px;margin-top:6px}
    .meta div{font-size:12px;color:#444}
    table{width:100%;border-collapse:collapse;margin-top:12px}
    th,td{font-size:13px;padding:8px 0;border-bottom:1px dashed #e5e5e5;text-align:left}
    th{font-weight:800;color:#333}
    .right{text-align:right}
    .total{font-weight:900}
    .summary{margin-top:8px;border-top:2px solid #f0f0f0;padding-top:8px}
    .summary-row{display:flex;justify-content:space-between;margin:4px 0;font-size:13px}
    .actions{display:flex;gap:8px;margin-top:12px}
    .btn{flex:1;padding:10px;border:1px solid #ddd;border-radius:6px;background:#fff;cursor:pointer}
    .btn-primary{background:var(--brand);color:#fff;border-color:var(--brand)}
    @media print{
      @page{size: A5 portrait; margin: 10mm}
      .actions{display:none}
      body{background:#fff}
      .paper{box-shadow:none;margin:0;width:auto;border-radius:0}
      .brand{color:#000}
    }
  </style>
</head>
<body>
  <div class="paper">
    <div class="header">
      <img class="logo" src="123.jpg" alt="Logo" onerror="this.style.display='none'">
      <div class="brand-block">
        <div class="brand">Monlei SiPao</div>
        <div class="muted">Authentic Steamed Buns and More</div>
      </div>
    </div>
    <div class="meta">
      <div><strong>Receipt:</strong> <?php echo htmlspecialchars($receipt); ?></div>
      <div><strong>Date:</strong> <?php echo htmlspecialchars($dateStr); ?></div>
    </div>
    <div class="muted" style="margin-top:4px">Cashier: <?php echo htmlspecialchars($user['email']); ?></div>

    <table>
      <thead>
        <tr><th>Item</th><th class="right">Qty</th><th class="right">Unit</th><th class="right">Total</th></tr>
      </thead>
      <tbody>
      <?php $itemCount = 0; foreach ($lines as $l): $price = $l['price_per_unit'] !== null ? (float)$l['price_per_unit'] : 0.0; $itemCount += (int)$l['quantity']; ?>
        <tr>
          <td><?php echo htmlspecialchars($l['name']); ?><div class="muted">SKU: <?php echo htmlspecialchars($l['sku']); ?></div></td>
          <td class="right"><?php echo (int)$l['quantity']; ?></td>
          <td class="right">₱<?php echo number_format($price,2); ?></td>
          <td class="right">₱<?php echo number_format((float)$l['amount'],2); ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr><td colspan="3" class="right total">Total</td><td class="right total">₱<?php echo number_format($total,2); ?></td></tr>
      </tfoot>
    </table>

    <?php
      // Fetch cash details (same across receipt rows)
      $paymentMethod = strtoupper($lines[0]['payment_method']);
      $cashReceived = isset($lines[0]['cash_received']) ? (float)$lines[0]['cash_received'] : null;
      $cashChange = isset($lines[0]['cash_change']) ? (float)$lines[0]['cash_change'] : null;
    ?>
    <div class="summary">
      <div class="summary-row"><span>Items</span><strong><?php echo (int)$itemCount; ?></strong></div>
      <div class="summary-row"><span>Payment</span><strong><?php echo htmlspecialchars($paymentMethod); ?></strong></div>
      <div class="summary-row"><span>Grand Total</span><strong>₱<?php echo number_format($total,2); ?></strong></div>
      <?php if ($paymentMethod === 'CASH' && $cashReceived !== null): ?>
        <div class="summary-row"><span>Total Cash</span><strong>₱<?php echo number_format($cashReceived,2); ?></strong></div>
        <div class="summary-row"><span>Total Change</span><strong>₱<?php echo number_format($cashChange ?? max(0,$cashReceived-$total),2); ?></strong></div>
      <?php endif; ?>
    </div>

    <div class="actions">
      <button class="btn" onclick="window.location.href='reseller-kiosk.php'">Back to kiosk</button>
          <button class="btn" onclick="window.open('reseller_receipt_pdf.php?receipt=<?php echo urlencode($receipt); ?>','_blank')"><i class="fa-solid fa-file-pdf"></i> Download PDF</button>
      <button class="btn btn-primary" onclick="window.print()"><i class="fa-solid fa-print"></i> Print receipt</button>
    </div>
  </div>
<?php if (isset($_GET['print']) && $_GET['print'] == '1'): ?>
<script>
  window.addEventListener('load', () => {
    try { window.print(); } catch(e) {}
    setTimeout(() => { try { window.close(); } catch(e) {} }, 300);
  });
  window.addEventListener('afterprint', () => {
    setTimeout(() => { try { window.close(); } catch(e) {} }, 100);
  });
  // Some browsers delay print dialog; ensure it's triggered
  setTimeout(() => { try { window.print(); } catch(e) {} }, 200);
}</script>
<?php endif; ?>
</body>
</html>