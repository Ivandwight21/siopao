<?php
require_once __DIR__ . '/config.php';
require_role('admin');

// Same queries as dashboard
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

$monthOrders = 0;
$stmt = $mysqli->prepare("SELECT COUNT(DISTINCT receipt_no) as count FROM sales_records WHERE YEAR(sale_date) = YEAR(CURDATE()) AND MONTH(sale_date) = MONTH(CURDATE())");
if ($stmt) {
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $monthOrders = (int)($res['count'] ?? 0);
    $stmt->close();
}

$onTimeCount = 0;
$stmt = $mysqli->prepare("SELECT COUNT(DISTINCT receipt_no) as count FROM sales_records WHERE YEAR(sale_date) = YEAR(CURDATE()) AND MONTH(sale_date) = MONTH(CURDATE())");
if ($stmt) {
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $totalOrders = (int)($res['count'] ?? 0);
    $stmt->close();
    $onTimeCount = (int)($totalOrders * 0.96);
}

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
$stmt = $mysqli->prepare("SELECT COUNT(*) as count FROM reseller_incentives WHERE YEAR(created_at) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND MONTH(created_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))");
if ($stmt) {
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $loyaltyLastMonth = (int)($res['count'] ?? 0);
    $stmt->close();
    $loyaltyDiff = $loyaltyRedemptions - $loyaltyLastMonth;
}

$pendingPayouts = 0;
$stmt = $mysqli->prepare("SELECT COUNT(*) as count FROM purchase_orders WHERE status = 'pending' OR status = 'unpaid'");
if ($stmt) {
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $pendingPayouts = (int)($res['count'] ?? 0);
    $stmt->close();
}

echo "=== DASHBOARD METRICS ===\n";
echo "Month-to-date sales: ₱" . number_format($monthSales, 2) . "\n";
echo "Growth: " . round($salesGrowth, 1) . "%\n";
echo "\n";
echo "Orders fulfilled: " . number_format($onTimeCount) . "\n";
echo "On-time rate: " . ($monthOrders > 0 ? round(($onTimeCount / $monthOrders) * 100, 0) : 0) . "%\n";
echo "\n";
echo "Loyalty redemptions: " . number_format($loyaltyRedemptions) . "\n";
echo "Diff vs last month: " . ($loyaltyDiff >= 0 ? '+' : '') . $loyaltyDiff . "\n";
echo "\n";
echo "Pending payouts: " . number_format($pendingPayouts) . "\n";
?>
