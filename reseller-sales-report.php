<?php
require_once __DIR__ . '/config.php';
require_role('reseller');

// Current reseller
$reseller_id = $_SESSION['user_id'] ?? ($_SESSION['user']['id'] ?? 0);

// Date ranges (ISO week: Monday-Sunday)
$week_start = date('Y-m-d', strtotime('monday this week'));
$week_end = date('Y-m-d', strtotime('sunday this week'));
$last_week_start = date('Y-m-d', strtotime('monday last week'));
$last_week_end = date('Y-m-d', strtotime('sunday last week'));

// Week-to-date revenue
$weekRevenue = 0;
$lastWeekRevenue = 0;
$revenueGrowth = 0;
$stmt = $mysqli->prepare("SELECT SUM(amount) as total FROM sales_records WHERE user_id = ? AND sale_date BETWEEN ? AND ?");
if ($stmt) {
    $stmt->bind_param("iss", $reseller_id, $week_start, $week_end);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $weekRevenue = (float)($row['total'] ?? 0);
    $stmt->close();
}
$stmt = $mysqli->prepare("SELECT SUM(amount) as total FROM sales_records WHERE user_id = ? AND sale_date BETWEEN ? AND ?");
if ($stmt) {
    $stmt->bind_param("iss", $reseller_id, $last_week_start, $last_week_end);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $lastWeekRevenue = (float)($row['total'] ?? 0);
    $stmt->close();
    if ($lastWeekRevenue > 0) {
        $revenueGrowth = (($weekRevenue - $lastWeekRevenue) / $lastWeekRevenue) * 100;
    }
}

// Average order value and order mix
$avgOrderValue = 0;
$onlineOrders = 0;
$instoreOrders = 0;
$totalOrders = 0;
$avgItemsPerTicket = 0; // fallback text in UI
$stmt = $mysqli->prepare("SELECT AVG(amount) as aov, COUNT(*) as orders, SUM(CASE WHEN payment_method <> 'cash' THEN 1 ELSE 0 END) as online_orders, SUM(CASE WHEN payment_method = 'cash' THEN 1 ELSE 0 END) as instore_orders, AVG(quantity) as avg_items FROM sales_records WHERE user_id = ? AND sale_date BETWEEN ? AND ?");
if ($stmt) {
    $stmt->bind_param("iss", $reseller_id, $week_start, $week_end);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $avgOrderValue = (float)($row['aov'] ?? 0);
    $totalOrders = (int)($row['orders'] ?? 0);
    $onlineOrders = (int)($row['online_orders'] ?? 0);
    $instoreOrders = (int)($row['instore_orders'] ?? 0);
    $avgItemsPerTicket = (float)($row['avg_items'] ?? 0);
    $stmt->close();
}

// Daily performance this week
$dailyRows = [];
$dailyAvgRevenue = 0;
$stmt = $mysqli->prepare("SELECT DATE(sale_date) as day, COUNT(*) as orders, SUM(amount) as revenue FROM sales_records WHERE user_id = ? AND sale_date BETWEEN ? AND ? GROUP BY DATE(sale_date) ORDER BY day ASC");
if ($stmt) {
    $stmt->bind_param("iss", $reseller_id, $week_start, $week_end);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $dailyRows[] = [
            'day' => $row['day'],
            'orders' => (int)($row['orders'] ?? 0),
            'revenue' => (float)($row['revenue'] ?? 0),
        ];
    }
    $stmt->close();
    if (count($dailyRows) > 0) {
        $sum = array_sum(array_column($dailyRows, 'revenue'));
        $dailyAvgRevenue = $sum / count($dailyRows);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reseller Sales Report | SiPao Portal</title>
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
                <span class="portal-subtitle">Reseller Toolkit</span>
            </div>
            <div class="nav-group">
                <span class="nav-label">Quick access</span>
                <a class="nav-link" href="resellerdashboard.php"><i class="fa-solid fa-chart-pie"></i>Dashboard 📊</a>
                <a class="nav-link active" href="reseller-sales-report.php"><i class="fa-solid fa-chart-line"></i>Sales Report 📈</a>
                <a class="nav-link" href="reseller-pos.php"><i class="fa-solid fa-cash-register"></i>Point of Sale 🛒</a>
                <a class="nav-link" href="reseller-account-settings.php"><i class="fa-solid fa-user"></i>Account Settings 👤</a>
            </div>
        </aside>
        <main class="portal-main">
            <div class="topbar">
                <div>
                    <h1>Your sales performance</h1>
                    <div class="breadcrumb">Monitor weekly and monthly wins.</div>
                </div>
                <div class="quick-actions">
                    <form method="POST" action="auth/download_sales_pdf.php" style="display:inline;">
                        <input type="hidden" name="start_date" value="<?php echo date('Y-m-d', strtotime('-7 days')); ?>">
                        <input type="hidden" name="end_date" value="<?php echo date('Y-m-d'); ?>">
                        <button type="submit" class="btn-ghost"><i class="fa-solid fa-calendar-week"></i> This week</button>
                    </form>
                    <button class="btn-ghost" onclick="alert('Custom date range feature coming soon')"><i class="fa-solid fa-calendar-days"></i> Custom dates</button>
                    <form method="POST" action="auth/download_sales_pdf.php" style="display:inline;">
                        <input type="hidden" name="start_date" value="<?php echo date('Y-m-01'); ?>">
                        <input type="hidden" name="end_date" value="<?php echo date('Y-m-d'); ?>">
                        <button type="submit" class="btn-accent"><i class="fa-solid fa-download"></i> Download PDF</button>
                    </form>
                    <a class="btn-logout" href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
                </div>
            </div>
            <section class="content-section">
                <div class="card-grid">
                    <div class="card">
                        <h3>Week-to-date revenue</h3>
                        <span class="metric">₱<?php echo number_format($weekRevenue, 0); ?></span>
                        <span class="trend"><i class="fa-solid fa-arrow-trend-<?php echo $revenueGrowth >= 0 ? 'up' : 'down'; ?>"></i> <?php echo ($revenueGrowth >= 0 ? '+' : ''); ?><?php echo round($revenueGrowth, 1); ?>% vs last week</span>
                    </div>
                    <div class="card">
                        <h3>Average order value</h3>
                        <span class="metric">₱<?php echo number_format($avgOrderValue, 0); ?></span>
                        <span class="trend"><i class="fa-solid fa-basket-shopping"></i> <?php echo number_format($avgItemsPerTicket, 1); ?> items per ticket</span>
                    </div>
                    <div class="card">
                        <h3>Online orders</h3>
                        <span class="metric"><?php echo number_format($onlineOrders); ?></span>
                        <span class="trend"><i class="fa-solid fa-mobile-screen"></i> <?php echo ($totalOrders > 0) ? round(($onlineOrders / $totalOrders) * 100, 1) : 0; ?>% of total</span>
                    </div>
                    <div class="card">
                        <h3>In-store orders</h3>
                        <span class="metric"><?php echo number_format($instoreOrders); ?></span>
                        <span class="trend"><i class="fa-solid fa-store"></i> <?php echo ($totalOrders > 0) ? round(($instoreOrders / $totalOrders) * 100, 1) : 0; ?>% of total</span>
                    </div>
                </div>
                <div class="table-card">
                    <header>
                        <h2>Daily performance</h2>
                        <button class="btn-ghost"><i class="fa-solid fa-lines-leaning"></i> View trend line</button>
                    </header>
                    <table>
                        <thead>
                            <tr>
                                <th>Day</th>
                                <th>Orders</th>
                                <th>Revenue</th>
                                <th>Conversion</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($dailyRows)): ?>
                                <?php foreach ($dailyRows as $row): ?>
                                    <?php
                                        $dayLabel = date('l', strtotime($row['day']));
                                        $orders = (int)$row['orders'];
                                        $revenue = (float)$row['revenue'];
                                        $isHigh = $dailyAvgRevenue > 0 && $revenue >= $dailyAvgRevenue;
                                        $badgeClass = $isHigh ? 'badge success' : 'badge danger';
                                        $badgeIcon = $isHigh ? 'fa-circle-up' : 'fa-circle-down';
                                        $badgeText = $isHigh ? 'High' : 'Low';
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($dayLabel); ?></td>
                                        <td><?php echo number_format($orders); ?></td>
                                        <td>₱<?php echo number_format($revenue, 2); ?></td>
                                        <td><span class="<?php echo $badgeClass; ?>"><i class="fa-solid <?php echo $badgeIcon; ?>"></i><?php echo $badgeText; ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4" style="text-align:center;color:#999;">No sales yet this week.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="info-banner">
                    <i class="fa-solid fa-lightbulb"></i>
                    <div>
                        <strong>Tip:</strong> Wednesdays are trending slower. Schedule a "midweek merienda" promo to boost foot traffic.
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
