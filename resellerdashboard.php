<?php
require_once __DIR__ . '/config.php';
require_role('reseller');
$flash = consume_flash();

// Get current reseller user ID
$reseller_id = $_SESSION['user_id'] ?? ($_SESSION['user']['id'] ?? 0);

// Month-to-date sales for this reseller
$monthSales = 0;
$lastMonthSales = 0;
$salesGrowth = 0;
$stmt = $mysqli->prepare("SELECT SUM(amount) as total FROM sales_records WHERE user_id = ? AND YEAR(sale_date) = YEAR(CURDATE()) AND MONTH(sale_date) = MONTH(CURDATE())");
if ($stmt) {
    $stmt->bind_param("i", $reseller_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $monthSales = (float)($res['total'] ?? 0);
    $stmt->close();
}
// Last month sales for growth
$stmt = $mysqli->prepare("SELECT SUM(amount) as total FROM sales_records WHERE user_id = ? AND YEAR(sale_date) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND MONTH(sale_date) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))");
if ($stmt) {
    $stmt->bind_param("i", $reseller_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $lastMonthSales = (float)($res['total'] ?? 0);
    $stmt->close();
    if ($lastMonthSales > 0) {
        $salesGrowth = (($monthSales - $lastMonthSales) / $lastMonthSales) * 100;
    }
}

// Orders this month for this reseller
$monthOrders = 0;
$stmt = $mysqli->prepare("SELECT COUNT(DISTINCT receipt_no) as count FROM sales_records WHERE user_id = ? AND YEAR(sale_date) = YEAR(CURDATE()) AND MONTH(sale_date) = MONTH(CURDATE())");
if ($stmt) {
    $stmt->bind_param("i", $reseller_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $monthOrders = (int)($res['count'] ?? 0);
    $stmt->close();
}

// On-time fulfillment (assume 96%)
$onTimeCount = (int)($monthOrders * 0.96);

// Loyalty redemptions for this reseller
$loyaltyRedemptions = 0;
$loyaltyLastMonth = 0;
$loyaltyDiff = 0;
$stmt = $mysqli->prepare("SELECT COUNT(*) as count FROM reseller_incentives WHERE reseller_id = ? AND YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())");
if ($stmt) {
    $stmt->bind_param("i", $reseller_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $loyaltyRedemptions = (int)($res['count'] ?? 0);
    $stmt->close();
}
// Last month loyalty for diff
$stmt = $mysqli->prepare("SELECT COUNT(*) as count FROM reseller_incentives WHERE reseller_id = ? AND YEAR(created_at) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND MONTH(created_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))");
if ($stmt) {
    $stmt->bind_param("i", $reseller_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $loyaltyLastMonth = (int)($res['count'] ?? 0);
    $stmt->close();
    $loyaltyDiff = $loyaltyRedemptions - $loyaltyLastMonth;
}

// Pending payouts for this reseller (count of pending orders)
$pendingPayouts = 0;
$stmt = $mysqli->prepare("SELECT COUNT(*) as count FROM stock_orders WHERE user_id = ? AND status = 'pending'");
if ($stmt) {
    $stmt->bind_param("i", $reseller_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $pendingPayouts = (int)($res['count'] ?? 0);
    $stmt->close();
}

// Weekly top products for this reseller (by revenue)
$topProducts = [];
$stmt = $mysqli->prepare("SELECT sr.product_id, i.name, SUM(sr.quantity) as units, SUM(sr.amount) as revenue FROM sales_records sr JOIN inventory i ON i.id = sr.product_id WHERE sr.user_id = ? AND YEARWEEK(sr.sale_date, 1) = YEARWEEK(CURDATE(), 1) GROUP BY sr.product_id, i.name ORDER BY revenue DESC LIMIT 10");
if ($stmt) {
	$stmt->bind_param("i", $reseller_id);
	$stmt->execute();
	$result = $stmt->get_result();
	while ($row = $result->fetch_assoc()) {
		$topProducts[] = [
			'product_id' => (int)($row['product_id'] ?? 0),
			'name' => $row['name'] ?? 'Unknown',
			'units' => (int)($row['units'] ?? 0),
			'revenue' => (float)($row['revenue'] ?? 0),
		];
	}
	$stmt->close();
}

// Last week's units per product (for trend calc)
$lastWeekUnits = [];
$stmt = $mysqli->prepare("SELECT sr.product_id, SUM(sr.quantity) as units FROM sales_records sr WHERE sr.user_id = ? AND YEARWEEK(sr.sale_date, 1) = YEARWEEK(DATE_SUB(CURDATE(), INTERVAL 1 WEEK), 1) GROUP BY sr.product_id");
if ($stmt) {
	$stmt->bind_param("i", $reseller_id);
	$stmt->execute();
	$result = $stmt->get_result();
	while ($row = $result->fetch_assoc()) {
		$lastWeekUnits[(int)$row['product_id']] = (int)($row['units'] ?? 0);
	}
	$stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Reseller Dashboard | SiPao Portal</title>
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
				<a class="nav-link active" href="resellerdashboard.php"><i class="fa-solid fa-chart-pie"></i>Dashboard 📊</a>
				<a class="nav-link" href="reseller-sales-report.php"><i class="fa-solid fa-chart-line"></i>Sales Report 📈</a>
				<a class="nav-link" href="reseller-pos.php"><i class="fa-solid fa-cash-register"></i>Point of Sale 🛒</a>
				<a class="nav-link" href="reseller-account-settings.php"><i class="fa-solid fa-user"></i>Account Settings 👤</a>
			</div>
		</aside>
		<main class="portal-main">
			<div class="topbar">
				<div>
					<h1>Welcome back, Partner!</h1>
					<div class="breadcrumb">Track your sales and restock needs at a glance.</div>
				</div>
				<div class="quick-actions">
					<a href="auth/order_stocks.php" class="btn-ghost"><i class="fa-solid fa-bag-shopping"></i> Order new stocks</a>
					<a href="auth/download_promo.php" class="btn-accent"><i class="fa-solid fa-bullhorn"></i> Download promo kit</a>
					<a class="btn-logout" href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
				</div>
			</div>
			<?php if ($flash): ?>
			<div style="margin:16px;padding:12px;border-radius:8px;color:<?php echo $flash['type']==='error' ? '#fff' : '#fff'; ?>;background-color:<?php echo $flash['type']==='error' ? '#c1121f' : '#06a77d'; ?>;font-weight:700;">
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
						<span class="metric">₱<?php echo number_format($pendingPayouts, 0); ?></span>
						<span class="trend">Awaiting settlement</span>
					</div>
				</div>
				<div class="table-card">
					<header>
						<h2>Top products this week</h2>
						<button class="btn-ghost"><i class="fa-solid fa-sliders"></i> Adjust view</button>
					</header>
					<table>
						<thead>
							<tr>
								<th>Product</th>
								<th>Units sold</th>
								<th>Gross sales</th>
								<th>Trend</th>
							</tr>
						</thead>
						<tbody>
							<?php if (!empty($topProducts)): ?>
								<?php foreach ($topProducts as $product): ?>
									<?php
										$productId = (int)$product['product_id'];
										$units = (int)$product['units'];
										$revenue = (float)$product['revenue'];
										$name = htmlspecialchars($product['name'] ?? 'Unknown');
										$lastUnits = $lastWeekUnits[$productId] ?? 0;
										$trend = $lastUnits > 0 ? (($units - $lastUnits) / $lastUnits) * 100 : ($units > 0 ? 100 : 0);
										$badgeClass = 'badge' . ($trend > 0 ? ' success' : ($trend < 0 ? ' danger' : ''));
										$iconClass = $trend > 0 ? 'fa-arrow-up' : ($trend < 0 ? 'fa-arrow-down' : 'fa-minus');
										$trendLabel = $trend === 0 ? '0%' : (($trend > 0 ? '+' : '') . round($trend, 1) . '%');
										if ($lastUnits === 0 && $units > 0) {
											$trendLabel = 'new';
											$iconClass = 'fa-star';
										}
									?>
									<tr>
										<td><?php echo $name; ?></td>
										<td><?php echo number_format($units); ?></td>
										<td>₱<?php echo number_format($revenue, 2); ?></td>
										<td><span class="<?php echo $badgeClass; ?>"><i class="fa-solid <?php echo $iconClass; ?>"></i><?php echo $trendLabel; ?></span></td>
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
						<strong>Growth tip:</strong> Post today’s promo on your social pages before 10 AM. Partners who share early enjoy 22% more lunchtime orders.
					</div>
				</div>
			</section>
		</main>
	</div>
</body>
</html>
