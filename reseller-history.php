<?php
require_once __DIR__ . '/config.php';
require_role('reseller');
$user = current_user();

$from = sanitize_text($_GET['from'] ?? '');
$to   = sanitize_text($_GET['to'] ?? '');
$limit = 100;

$sql = "SELECT receipt_no,
               MIN(created_at) AS created_at,
               MIN(payment_method) AS payment_method,
               SUM(quantity) AS items,
               SUM(amount) AS total
        FROM sales_records
        WHERE user_id = ?";
$params = [$user['id']];
$types = 'i';

if ($from !== '') { $sql .= " AND DATE(created_at) >= ?"; $params[] = $from; $types .= 's'; }
if ($to   !== '') { $sql .= " AND DATE(created_at) <= ?"; $params[] = $to;   $types .= 's'; }

$sql .= " GROUP BY receipt_no ORDER BY created_at DESC LIMIT $limit";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Transaction History | SiPao Portal</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/portal.css">
  <style>
    .filters{display:flex;gap:8px;align-items:end}
    .filters input{padding:8px;border:1px solid #ddd;border-radius:6px}
    .filters button{padding:8px 12px;border:1px solid #ddd;border-radius:6px;background:#fff;cursor:pointer}
    .filters .apply{background:#8c1c2f;color:#fff;border-color:#8c1c2f}
    .table-card table td{vertical-align:middle}
    .badge{padding:4px 8px;border-radius:10px;font-size:12px}
    .badge.cash{background:#e8f5e9;color:#06a77d}
    .badge.card{background:#e3f2fd;color:#1976d2}
    .badge.gcash{background:#fff3e0;color:#ef6c00}
  </style>
</head>
<body>
  <div class="portal-shell">
    <aside class="sidebar">
      <div class="brand-block">
        <img src="123.jpg" alt="Monlei SiPao logo">
        <span class="portal-subtitle">Reseller Toolkit</span>
      </div>
      <div class="nav-group">
        <span class="nav-label">Quick access</span>
        <a class="nav-link" href="resellerdashboard.php"><i class="fa-solid fa-chart-pie"></i>Dashboard</a>
        <a class="nav-link" href="reseller-sales-report.php"><i class="fa-solid fa-chart-line"></i>Sales Report</a>
        <a class="nav-link" href="reseller-pos.php"><i class="fa-solid fa-cash-register"></i>Point of Sale</a>
        <a class="nav-link" href="reseller-kiosk.php"><i class="fa-solid fa-store"></i>Kiosk</a>
        <a class="nav-link active" href="reseller-history.php"><i class="fa-solid fa-receipt"></i>History</a>
        <a class="nav-link" href="reseller-account-settings.php"><i class="fa-solid fa-user"></i>Account</a>
      </div>
    </aside>
    <main class="portal-main">
      <div class="topbar">
        <div>
          <h1>Transaction History</h1>
          <div class="breadcrumb">View past receipts, totals, and reprint receipts.</div>
        </div>
        <div class="quick-actions">
          <a class="btn-logout" href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
      </div>

      <section class="content-section">
        <div class="table-card">
          <header>
            <h2>Recent receipts</h2>
            <div class="filters">
              <div>
                <label style="display:block;font-size:12px;color:#666">From</label>
                <input type="date" id="from" value="<?php echo htmlspecialchars($from); ?>">
              </div>
              <div>
                <label style="display:block;font-size:12px;color:#666">To</label>
                <input type="date" id="to" value="<?php echo htmlspecialchars($to); ?>">
              </div>
              <button class="apply" onclick="applyFilter()"><i class="fa-solid fa-filter"></i> Apply</button>
              <input id="search" placeholder="Search by receipt no..." style="margin-left:8px;padding:8px;border:1px solid #ddd;border-radius:6px">
            </div>
          </header>
          <table>
            <thead>
              <tr>
                <th>Date/Time</th>
                <th>Receipt No</th>
                <th class="right">Items</th>
                <th class="right">Total</th>
                <th>Payment</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="tbody">
              <?php foreach ($rows as $r): ?>
              <tr data-search="<?php echo strtolower($r['receipt_no']); ?>">
                <td><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($r['created_at']))); ?></td>
                <td><strong><?php echo htmlspecialchars($r['receipt_no']); ?></strong></td>
                <td class="right"><?php echo (int)$r['items']; ?></td>
                <td class="right">₱<?php echo number_format((float)$r['total'],2); ?></td>
                <td>
                  <?php $pm = strtolower($r['payment_method'] ?? 'cash'); ?>
                  <span class="badge <?php echo $pm; ?>"><?php echo htmlspecialchars(strtoupper($pm)); ?></span>
                </td>
                <td>
                  <a class="btn-ghost" href="reseller_receipt_pdf.php?receipt=<?php echo urlencode($r['receipt_no']); ?>" target="_blank" rel="noopener" style="margin-left:6px"><i class="fa-solid fa-file-pdf"></i> Reprint</a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>
    </main>
  </div>

  <script>
  function applyFilter(){
    const f = document.getElementById('from').value;
    const t = document.getElementById('to').value;
    const url = new URL(location.href);
    if(f) url.searchParams.set('from', f); else url.searchParams.delete('from');
    if(t) url.searchParams.set('to', t); else url.searchParams.delete('to');
    location.href = url.toString();
  }
  document.getElementById('search').addEventListener('keyup', function(){
    const q = this.value.toLowerCase();
    document.querySelectorAll('#tbody tr').forEach(tr=>{
      const s = tr.getAttribute('data-search');
      tr.style.display = s.includes(q) ? '' : 'none';
    });
  });
  </script>
</body>
</html>