<?php
require_once __DIR__ . '/config.php';
require_role('reseller');
$user = current_user();

$receipt = sanitize_text($_GET['receipt'] ?? '');
if ($receipt === '') {
  http_response_code(400);
  echo 'Missing receipt number.';
  exit;
}

// Load dompdf
$autoload = __DIR__ . '/vendor/autoload.php';
if (!file_exists($autoload)) {
  http_response_code(500);
  echo 'PDF generator not installed. Please run: composer require dompdf/dompdf';
  exit;
}
require_once $autoload;

use Dompdf\Dompdf;
use Dompdf\Options;

// Fetch receipt lines securely for current user
$sql = "SELECT sr.product_id, sr.quantity, sr.amount, sr.created_at, sr.payment_method, sr.cash_received, sr.cash_change,
         i.name, i.sku, i.price_per_unit
        FROM sales_records sr
        JOIN inventory i ON i.id = sr.product_id
        WHERE sr.receipt_no = ? AND sr.user_id = ?
        ORDER BY sr.id ASC";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('si', $receipt, $user['id']);
$stmt->execute();
$res = $stmt->get_result();
$lines = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (!$lines) {
  http_response_code(404);
  echo 'Receipt not found.';
  exit;
}

$total = 0.0; $itemCount = 0; $dateStr = '';
foreach ($lines as $idx => $l) {
  $total += (float)$l['amount'];
  $itemCount += (int)$l['quantity'];
  if ($idx === 0) { $dateStr = date('Y-m-d H:i', strtotime($l['created_at'])); }
}
$payment = strtoupper($lines[0]['payment_method'] ?? 'CASH');
$cashReceived = isset($lines[0]['cash_received']) ? (float)$lines[0]['cash_received'] : null;
$cashChange = isset($lines[0]['cash_change']) ? (float)$lines[0]['cash_change'] : null;

// Embed logo as base64 if present
$logoPath = __DIR__ . '/123.jpg';
$logoData = '';
if (is_file($logoPath)) {
  $mime = 'image/jpeg';
  $ext = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
  if ($ext === 'png') $mime = 'image/png';
  $logoData = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoPath));
}

$rowsHtml = '';
foreach ($lines as $l) {
  $price = $l['price_per_unit'] !== null ? (float)$l['price_per_unit'] : 0.0;
  $rowsHtml .= '<tr>' .
               '<td><strong>' . htmlspecialchars($l['name']) . '</strong><div class="muted">SKU: ' . htmlspecialchars($l['sku']) . '</div></td>' .
               '<td class="right">' . (int)$l['quantity'] . '</td>' .
               '<td class="right">₱' . number_format($price, 2) . '</td>' .
               '<td class="right">₱' . number_format((float)$l['amount'], 2) . '</td>' .
               '</tr>';
}

$html = '<!DOCTYPE html><html><head><meta charset="UTF-8">'
  . '<style>'
  . 'body{font-family: DejaVu Sans, Arial, sans-serif;}'
  . '.header{display:flex;gap:10px;align-items:center;border-bottom:1px solid #ddd;padding-bottom:8px;margin-bottom:8px}'
  . '.logo{width:46px;height:46px;border-radius:6px;object-fit:cover}'
  . '.brand{font-weight:800;font-size:18px}'
  . '.muted{color:#555;font-size:11px}'
  . '.meta{display:flex;justify-content:space-between;font-size:12px;margin:6px 0}'
  . 'table{width:100%;border-collapse:collapse;margin-top:8px}'
  . 'th,td{font-size:12px;padding:6px 0;border-bottom:1px dashed #ccc;text-align:left}'
  . 'th{font-weight:700}'
  . '.right{text-align:right}'
  . '.total{font-weight:900}'
  . '.summary{margin-top:8px;border-top:1px solid #ddd;padding-top:6px;font-size:12px}'
  . '</style></head><body>'
  . '<div class="header">'
  . ($logoData ? '<img class="logo" src="' . $logoData . '" alt="Logo">' : '')
  . '<div><div class="brand">Monlei SiPao</div><div class="muted">Authentic Steamed Buns and More</div></div>'
  . '</div>'
  . '<div class="meta"><div><strong>Receipt:</strong> ' . htmlspecialchars($receipt) . '</div><div><strong>Date:</strong> ' . htmlspecialchars($dateStr) . '</div></div>'
  . '<div class="muted">Cashier: ' . htmlspecialchars($user['email']) . '</div>'
  . '<table><thead><tr><th>Item</th><th class="right">Qty</th><th class="right">Unit</th><th class="right">Total</th></tr></thead><tbody>'
  . $rowsHtml
  . '</tbody><tfoot><tr><td colspan="3" class="right total">Total</td><td class="right total">₱' . number_format($total, 2) . '</td></tr></tfoot></table>'
  . '<div class="summary">'
  . '<div>Items: <strong>' . (int)$itemCount . '</strong></div>'
  . '<div>Payment: <strong>' . htmlspecialchars($payment) . '</strong></div>'
  . ($payment === 'CASH' && $cashReceived !== null
      ? '<div>Grand Total: <strong>₱' . number_format($total,2) . '</strong></div>'
        . '<div>Total Cash: <strong>₱' . number_format($cashReceived,2) . '</strong></div>'
        . '<div>Total Change: <strong>₱' . number_format($cashChange ?? max(0,$cashReceived-$total),2) . '</strong></div>'
      : '<div>Grand Total: <strong>₱' . number_format($total,2) . '</strong></div>')
  . '</div>'
  . '</body></html>';

$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('isHtml5ParserEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A5', 'portrait');
$dompdf->render();

$filename = 'Receipt-' . preg_replace('/[^A-Za-z0-9\-]/', '', $receipt) . '.pdf';
$dompdf->stream($filename, ['Attachment' => true]);
exit;
?>
