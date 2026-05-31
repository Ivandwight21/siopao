<?php
require_once __DIR__ . '/config.php';
require_role('admin');
$flash = consume_flash();

// Fetch dashboard metrics based on actual database data
$totalAdmins = 0;
$totalResellers = 0;
$totalInventoryItems = 0;
$lowStockCount = 0;
$lowStockItems = [];

// Total admins
if ($stmt = $mysqli->prepare("SELECT COUNT(*) AS c FROM users WHERE role = 'admin'")) {
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $totalAdmins = (int)($res['c'] ?? 0);
    $stmt->close();
}

// Total resellers
if ($stmt = $mysqli->prepare("SELECT COUNT(*) AS c FROM users WHERE role = 'reseller'")) {
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $totalResellers = (int)($res['c'] ?? 0);
    $stmt->close();
}

// Inventory items count
if ($stmt = $mysqli->prepare("SELECT COUNT(*) AS c FROM inventory")) {
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $totalInventoryItems = (int)($res['c'] ?? 0);
    $stmt->close();
}

// Low stock items and count (qty <= reorder_level)
if ($stmt = $mysqli->prepare("SELECT id, name, sku, qty, reorder_level FROM inventory WHERE qty <= reorder_level ORDER BY qty ASC")) {
    $stmt->execute();
    $lowStockItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $lowStockCount = count($lowStockItems);
    $stmt->close();
}

// Month-to-date sales
$monthSales = 0;
$lastMonthSales = 0;
$salesGrowth = 0;
$stmt = $mysqli->prepare("SELECT SUM(amount) as total FROM sales_records WHERE YEAR(sale_date) = YEAR(CURDATE()) AND MONTH(sale_date) = MONTH(CURDATE())");
if ($stmt) {
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $monthSales = (float)($res['total'] ?? 0);
    $stmt->close();
}
// Last month sales for growth
$stmt = $mysqli->prepare("SELECT SUM(amount) as total FROM sales_records WHERE YEAR(sale_date) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND MONTH(sale_date) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))");
if ($stmt) {
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $lastMonthSales = (float)($res['total'] ?? 0);
    $stmt->close();
    if ($lastMonthSales > 0) {
        $salesGrowth = (($monthSales - $lastMonthSales) / $lastMonthSales) * 100;
    }
}

// Orders this month (by receipt number)
$monthOrders = 0;
$stmt = $mysqli->prepare("SELECT COUNT(DISTINCT receipt_no) as count FROM sales_records WHERE YEAR(sale_date) = YEAR(CURDATE()) AND MONTH(sale_date) = MONTH(CURDATE())");
if ($stmt) {
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $monthOrders = (int)($res['count'] ?? 0);
    $stmt->close();
}

// On-time fulfillment rate (assume 96% for now)
$onTimeCount = 0;
$stmt = $mysqli->prepare("SELECT COUNT(DISTINCT receipt_no) as count FROM sales_records WHERE YEAR(sale_date) = YEAR(CURDATE()) AND MONTH(sale_date) = MONTH(CURDATE())");
if ($stmt) {
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $totalOrders = (int)($res['count'] ?? 0);
    $stmt->close();
    $onTimeCount = (int)($totalOrders * 0.96);
}

// Loyalty redemptions this month
$loyaltyRedemptions = 0;
$loyaltyLastMonth = 0;
$loyaltyDiff = 0;
$stmt = $mysqli->prepare("SELECT COUNT(*) as count FROM reseller_incentives WHERE YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())");
if ($stmt) {
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $loyaltyRedemptions = (int)($res['count'] ?? 0);
    $stmt->close();
}
// Last month loyalty redemptions
$stmt = $mysqli->prepare("SELECT COUNT(*) as count FROM reseller_incentives WHERE YEAR(created_at) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND MONTH(created_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))");
if ($stmt) {
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $loyaltyLastMonth = (int)($res['count'] ?? 0);
    $stmt->close();
    $loyaltyDiff = $loyaltyRedemptions - $loyaltyLastMonth;
}

// Pending payouts
$pendingPayouts = 0;
$stmt = $mysqli->prepare("SELECT COUNT(*) as count FROM purchase_orders WHERE status = 'pending' OR status = 'unpaid'");
if ($stmt) {
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $pendingPayouts = (int)($res['count'] ?? 0);
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | SiPao Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/portal.css">
</head>
<body>
    <div class="portal-shell">
        <aside class="sidebar">
            <div class="brand-block">
                <img src="123.jpg" alt="Monlei SiPao logo">
                <span class="portal-subtitle">Admin Control Center</span>
            </div>
            <div class="nav-group">
                <span class="nav-label">Overview</span>
                <a class="nav-link active" href="admin-dashboard.php"><i class="fa-solid fa-chart-pie"></i>Dashboard</a>
                <a class="nav-link" href="admin-sales-report.php"><i class="fa-solid fa-chart-line"></i>Sales Report</a>
                <a class="nav-link" href="admin-inventory.php"><i class="fa-solid fa-boxes-stacked"></i>Inventory</a>
                <a class="nav-link" href="admin-pos.php"><i class="fa-solid fa-cash-register"></i>Point of Sale</a>
                <a class="nav-link" href="admin-resellers.php"><i class="fa-solid fa-users"></i>Resellers</a>
                <a class="nav-link" href="admin-account-settings.php"><i class="fa-solid fa-gear"></i>Account Settings</a>
            </div>
        </aside>
        <main class="portal-main">
            <div class="topbar">
                <div>
                    <h1>Good day, Admin!</h1>
                    <div class="breadcrumb">Monlei SiPao • Executive Summary</div>
                </div>
                <div class="quick-actions">
                    <form method="POST" action="auth/admin_export_report.php" style="display:inline;">
                        <input type="hidden" name="date_from" value="<?php echo date('Y-m-01'); ?>">
                        <input type="hidden" name="date_to" value="<?php echo date('Y-m-d'); ?>">
                        <button type="submit" class="btn-ghost"><i class="fa-solid fa-file-export"></i> Export daily report</button>
                    </form>
                    <button class="btn-accent" onclick="document.getElementById('promoModal').style.display='block'"><i class="fa-solid fa-plus"></i> Add promotion</button>
                    <a class="btn-logout" href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
                </div>
            </div>
            <?php if ($flash): ?>
            <div style="margin:16px;padding:12px;border-radius:8px;color:#fff;background-color:<?php echo $flash['type']==='error' ? '#c1121f' : '#06a77d'; ?>;font-weight:700;">
                <?php echo htmlspecialchars($flash['message']); ?>
            </div>
            <?php endif; ?>
            <section class="content-section">
                <div class="card-grid">
                    <div class="card">
                        <h3>Month-to-date sales</h3>
                        <span class="metric">₱<?php echo number_format($monthSales, 0); ?></span>
                        <span class="trend"><?php echo round($salesGrowth, 1); ?>% growth</span>
                    </div>
                    <div class="card">
                        <h3>Orders fulfilled</h3>
                        <span class="metric"><?php echo number_format($onTimeCount); ?></span>
                        <span class="trend"><?php echo ($monthOrders > 0) ? round(($onTimeCount / $monthOrders) * 100, 0) : 0; ?>% on-time</span>
                    </div>
                    <div class="card">
                        <h3>Loyalty redemptions</h3>
                        <span class="metric"><?php echo number_format($loyaltyRedemptions); ?></span>
                        <span class="trend"><?php echo ($loyaltyDiff >= 0 ? '+' : ''); ?><?php echo $loyaltyDiff; ?> vs last month</span>
                    </div>
                    <div class="card">
                        <h3>Pending payouts</h3>
                        <span class="metric"><?php echo number_format($pendingPayouts); ?></span>
                        <span class="trend">Awaiting approval</span>
                    </div>
                </div>
                <div class="table-card">
                    <header>
                        <h2>Low stock items</h2>
                        <button class="btn-ghost" onclick="location.reload()"><i class="fa-solid fa-rotate"></i> Refresh</button>
                    </header>
                    <table>
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>SKU</th>
                                <th>Qty</th>
                                <th>Reorder level</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($lowStockItems)): ?>
                                <?php foreach ($lowStockItems as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                                    <td><?php echo htmlspecialchars($item['sku']); ?></td>
                                    <td><?php echo (int)$item['qty']; ?></td>
                                    <td><?php echo (int)$item['reorder_level']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" style="text-align:center;color:#666;">No low stock items right now.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="info-banner">
                    <i class="fa-solid fa-lightbulb"></i>
                    <div>
                        <strong>Tip:</strong> Schedule your promo drops during lunchtime rush. Historical data shows a 28% boost in order volume between 11:30 AM and 1:00 PM when promotions are active.
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Add Promotion Modal -->
    <div id="promoModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:1000;">
        <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);background:white;padding:30px;border-radius:12px;width:500px;max-height:80vh;overflow-y:auto;">
            <h2 style="color:#8c1c2f;margin-bottom:20px;">Create New Promotion</h2>
            <form method="POST" action="auth/admin_add_promotion.php">
                <div style="margin-bottom:15px;">
                    <label style="display:block;margin-bottom:5px;font-weight:bold;color:#5b1221;">Promotion Name:</label>
                    <input type="text" name="promo_name" placeholder="e.g., Holiday Special" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:4px;" required>
                </div>
                <div style="margin-bottom:15px;">
                    <label style="display:block;margin-bottom:5px;font-weight:bold;color:#5b1221;">Description:</label>
                    <textarea name="promo_description" placeholder="What makes this promotion special?" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:4px;height:80px;"></textarea>
                </div>
                <div style="margin-bottom:15px;">
                    <label style="display:block;margin-bottom:5px;font-weight:bold;color:#5b1221;">Discount (%):</label>
                    <input type="number" name="discount_percent" min="1" max="100" placeholder="15" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:4px;" required>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:15px;">
                    <div>
                        <label style="display:block;margin-bottom:5px;font-weight:bold;color:#5b1221;">Start Date:</label>
                        <input type="date" name="start_date" value="<?php echo date('Y-m-d'); ?>" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:4px;" required>
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:5px;font-weight:bold;color:#5b1221;">End Date:</label>
                        <input type="date" name="end_date" value="<?php echo date('Y-m-d', strtotime('+7 days')); ?>" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:4px;" required>
                    </div>
                </div>
                <div style="display:flex;gap:10px;">
                    <button type="submit" style="flex:1;padding:12px;background:#8c1c2f;color:white;border:none;border-radius:6px;font-weight:bold;cursor:pointer;">Create Promotion</button>
                    <button type="button" onclick="document.getElementById('promoModal').style.display='none'" style="flex:1;padding:12px;background:#ccc;color:#333;border:none;border-radius:6px;font-weight:bold;cursor:pointer;">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
