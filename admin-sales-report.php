<?php
require_once __DIR__ . '/config.php';
require_role('admin');
$flash = consume_flash();

// Fetch sales metrics (using global $mysqli from config.php)

// Date range handling - check if custom dates provided via GET
$week_start = isset($_GET['report_start']) && $_GET['report_start'] !== '' 
    ? sanitize_text($_GET['report_start']) 
    : date('Y-m-d', strtotime('monday this week'));
$week_end = isset($_GET['report_end']) && $_GET['report_end'] !== '' 
    ? sanitize_text($_GET['report_end']) 
    : date('Y-m-d', strtotime('sunday this week'));

// Validate and format dates
$week_start = date('Y-m-d', strtotime($week_start));
$week_end = date('Y-m-d', strtotime($week_end));

// Ensure start is before end
if (strtotime($week_start) > strtotime($week_end)) {
    $temp = $week_start;
    $week_start = $week_end;
    $week_end = $temp;
}

// Calculate period label
$date_diff = (strtotime($week_end) - strtotime($week_start)) / (60 * 60 * 24);
$period_label = $date_diff <= 7 ? 'Selected Week' : ($date_diff <= 31 ? 'Selected Period' : 'Custom Range');

// Weekly revenue
$result = $mysqli->query("SELECT SUM(amount) as total FROM sales_records WHERE sale_date >= '$week_start' AND sale_date <= '$week_end'");
$week_stats = $result->fetch_assoc();

// Average order size
$result = $mysqli->query("SELECT AVG(amount) as avg FROM sales_records WHERE sale_date >= '$week_start' AND sale_date <= '$week_end'");
$avg_stats = $result->fetch_assoc();

// Product performance (top 10)
$products = $mysqli->query("
    SELECT product_id, SUM(quantity) as units, SUM(amount) as revenue FROM sales_records
    WHERE sale_date >= '$week_start' AND sale_date <= '$week_end'
    GROUP BY product_id
    ORDER BY revenue DESC LIMIT 10
");

// Daily breakdown
$daily = $mysqli->query("
    SELECT sale_date, COUNT(*) as orders, SUM(quantity) as units, SUM(amount) as revenue FROM sales_records
    WHERE sale_date >= '$week_start' AND sale_date <= '$week_end'
    GROUP BY sale_date
    ORDER BY sale_date ASC
");

// Payment method breakdown for charts
$payment_methods = $mysqli->query("
    SELECT payment_method, COUNT(*) as orders, SUM(amount) as revenue FROM sales_records
    WHERE sale_date >= '$week_start' AND sale_date <= '$week_end'
    GROUP BY payment_method
");

// Top products with names for charts
$top_products = $mysqli->query("
    SELECT i.name, SUM(sr.quantity) as units, SUM(sr.amount) as revenue 
    FROM sales_records sr
    JOIN inventory i ON i.id = sr.product_id
    WHERE sr.sale_date >= '$week_start' AND sr.sale_date <= '$week_end'
    GROUP BY sr.product_id, i.name
    ORDER BY revenue DESC LIMIT 10
");

// Prepare chart data arrays
$daily_labels = [];
$daily_revenue = [];
$daily_orders = [];
if ($daily && $daily->num_rows > 0) {
    $daily->data_seek(0);
    while ($row = $daily->fetch_assoc()) {
        $daily_labels[] = date('M d', strtotime($row['sale_date']));
        $daily_revenue[] = (float)$row['revenue'];
        $daily_orders[] = (int)$row['orders'];
    }
}

$payment_labels = [];
$payment_data = [];
if ($payment_methods && $payment_methods->num_rows > 0) {
    while ($row = $payment_methods->fetch_assoc()) {
        $payment_labels[] = strtoupper($row['payment_method']);
        $payment_data[] = (float)$row['revenue'];
    }
}

$product_labels = [];
$product_data = [];
if ($top_products && $top_products->num_rows > 0) {
    while ($row = $top_products->fetch_assoc()) {
        $product_labels[] = substr($row['name'], 0, 20);
        $product_data[] = (float)$row['revenue'];
    }
}

// Payment method breakdown for charts
$payment_methods = $mysqli->query("
    SELECT payment_method, COUNT(*) as orders, SUM(amount) as revenue FROM sales_records
    WHERE sale_date >= '$week_start' AND sale_date <= '$week_end'
    GROUP BY payment_method
");

// Top products with names for charts
$top_products = $mysqli->query("
    SELECT i.name, SUM(sr.quantity) as units, SUM(sr.amount) as revenue 
    FROM sales_records sr
    JOIN inventory i ON i.id = sr.product_id
    WHERE sr.sale_date >= '$week_start' AND sr.sale_date <= '$week_end'
    GROUP BY sr.product_id, i.name
    ORDER BY revenue DESC LIMIT 10
");

// Prepare chart data arrays
$daily_labels = [];
$daily_revenue = [];
$daily_orders = [];
if ($daily && $daily->num_rows > 0) {
    $daily->data_seek(0);
    while ($row = $daily->fetch_assoc()) {
        $daily_labels[] = date('M d', strtotime($row['sale_date']));
        $daily_revenue[] = (float)$row['revenue'];
        $daily_orders[] = (int)$row['orders'];
    }
}

$payment_labels = [];
$payment_data = [];
if ($payment_methods && $payment_methods->num_rows > 0) {
    while ($row = $payment_methods->fetch_assoc()) {
        $payment_labels[] = strtoupper($row['payment_method']);
        $payment_data[] = (float)$row['revenue'];
    }
}

$product_labels = [];
$product_data = [];
if ($top_products && $top_products->num_rows > 0) {
    while ($row = $top_products->fetch_assoc()) {
        $product_labels[] = substr($row['name'], 0, 20);
        $product_data[] = (float)$row['revenue'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Sales Report | SiPao Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/portal.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
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
                <a class="nav-link" href="admin-dashboard.php"><i class="fa-solid fa-chart-pie"></i>Dashboard</a>
                <a class="nav-link active" href="admin-sales-report.php"><i class="fa-solid fa-chart-line"></i>Sales Report</a>
                <a class="nav-link" href="admin-inventory.php"><i class="fa-solid fa-boxes-stacked"></i>Inventory</a>
                <a class="nav-link" href="admin-pos.php"><i class="fa-solid fa-cash-register"></i>Point of Sale</a>
                <a class="nav-link" href="admin-resellers.php"><i class="fa-solid fa-users"></i>Resellers</a>
                <a class="nav-link" href="admin-account-settings.php"><i class="fa-solid fa-gear"></i>Account Settings</a>
            </div>
        </aside>
        <main class="portal-main">
            <div class="topbar">
                <div>
                    <h1>Sales analytics</h1>
                    <div class="breadcrumb">
                        Monlei SiPao • <?php echo htmlspecialchars($period_label); ?>: 
                        <strong><?php echo date('M d, Y', strtotime($week_start)); ?></strong> to 
                        <strong><?php echo date('M d, Y', strtotime($week_end)); ?></strong>
                        <?php if (isset($_GET['report_start']) || isset($_GET['report_end'])): ?>
                            <a href="admin-sales-report.php" style="margin-left:10px;color:#8c1c2f;text-decoration:none;font-size:0.9em;">
                                <i class="fa-solid fa-refresh"></i> Reset to This Week
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="quick-actions">
                    <button class="btn-ghost" onclick="document.getElementById('dateModal').style.display='block'"><i class="fa-solid fa-calendar-week"></i> Date range</button>
                    <button class="btn-accent" onclick="document.getElementById('exportReportModal').style.display='block'"><i class="fa-solid fa-file-arrow-down"></i> Export CSV</button>
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
                        <h3>Period revenue</h3>
                        <span class="metric">₱<?php echo number_format($week_stats['total'] ?? 0, 0); ?></span>
                        <span class="trend">
                            <i class="fa-solid fa-calendar-week"></i> 
                            <?php echo date('M d', strtotime($week_start)); ?> - <?php echo date('M d', strtotime($week_end)); ?>
                        </span>
                    </div>
                    <div class="card">
                        <h3>Average order size</h3>
                        <span class="metric">₱<?php echo number_format($avg_stats['avg'] ?? 0, 0); ?></span>
                        <span class="trend"><i class="fa-solid fa-basket-shopping"></i> Per transaction</span>
                    </div>
                    <div class="card">
                        <h3>Total orders</h3>
                        <span class="metric"><?php 
                            $result = $mysqli->query("SELECT COUNT(*) as count FROM sales_records WHERE sale_date >= '$week_start' AND sale_date <= '$week_end'");
                            $count_stats = $result->fetch_assoc();
                            echo $count_stats['count'] ?? 0;
                        ?></span>
                        <span class="trend"><i class="fa-solid fa-receipt"></i> Selected period</span>
                    </div>
                    <div class="card">
                        <h3>Total units sold</h3>
                        <span class="metric"><?php 
                            $result = $mysqli->query("SELECT SUM(quantity) as total FROM sales_records WHERE sale_date >= '$week_start' AND sale_date <= '$week_end'");
                            $units_stats = $result->fetch_assoc();
                            echo $units_stats['total'] ?? 0;
                        ?></span>
                        <span class="trend"><i class="fa-solid fa-box"></i> Items moved</span>
                    </div>
                </div>
                <div class="table-card">
                    <header>
                        <h2>Daily sales breakdown</h2>
                        <div style="display:flex;gap:10px;align-items:center;">
                            <span style="font-size:0.9em;color:#666;">
                                <?php echo date('M d', strtotime($week_start)); ?> - <?php echo date('M d, Y', strtotime($week_end)); ?>
                            </span>
                            <button class="btn-ghost" onclick="openChartModal('daily')"><i class="fa-solid fa-chart-line"></i> View chart</button>
                        </div>
                    </header>
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Orders</th>
                                <th>Units</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($daily && $daily->num_rows > 0) {
                                while ($row = $daily->fetch_assoc()) {
                                    $date = date('M d (l)', strtotime($row['sale_date']));
                                    echo "<tr>
                                        <td>{$date}</td>
                                        <td>{$row['orders']}</td>
                                        <td>{$row['units']}</td>
                                        <td>₱" . number_format($row['revenue'], 2) . "</td>
                                    </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4' style='text-align:center;color:#999;'>No sales data</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <div class="table-card">
                    <header>
                        <h2>Payment Methods Breakdown</h2>
                        <button class="btn-ghost" onclick="openChartModal('payment')"><i class="fa-solid fa-chart-pie"></i> View chart</button>
                    </header>
                    <table>
                        <thead>
                            <tr>
                                <th>Payment Method</th>
                                <th>Orders</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($payment_methods && $payment_methods->num_rows > 0) {
                                $payment_methods->data_seek(0);
                                while ($row = $payment_methods->fetch_assoc()) {
                                    echo "<tr>
                                        <td>" . htmlspecialchars(strtoupper($row['payment_method'])) . "</td>
                                        <td>{$row['orders']}</td>
                                        <td>₱" . number_format($row['revenue'], 2) . "</td>
                                    </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='3' style='text-align:center;color:#999;'>No payment data</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <div class="table-card">
                    <header>
                        <h2>Top products this week</h2>
                        <button class="btn-ghost" onclick="openChartModal('products')"><i class="fa-solid fa-chart-bar"></i> View chart</button>
                        <span style="font-size:0.9em;color:#666;">By revenue</span>
                    </header>
                    <table>
                        <thead>
                            <tr>
                                <th>Product ID</th>
                                <th>Units sold</th>
                                <th>Revenue</th>
                                <th>Avg price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($products && $products->num_rows > 0) {
                                while ($row = $products->fetch_assoc()) {
                                    $avg_price = ($row['units'] > 0) ? ($row['revenue'] / $row['units']) : 0;
                                    echo "<tr>
                                        <td>#" . htmlspecialchars($row['product_id'] ?? 'N/A') . "</td>
                                        <td>{$row['units']}</td>
                                        <td>₱" . number_format($row['revenue'], 2) . "</td>
                                        <td>₱" . number_format($avg_price, 2) . "</td>
                                    </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4' style='text-align:center;color:#999;'>No product data</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <div class="info-banner">
                    <i class="fa-solid fa-lightbulb"></i>
                    <div>
                        <strong>Performance tip:</strong> Review top products and consider promoting lower-performing items through targeted discounts or cross-selling opportunities on the POS system.
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Date Range Modal -->
    <div id="dateModal" style="display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;background-color:rgba(0,0,0,0.5);">
        <div style="background-color:#fce9d4;margin:100px auto;padding:30px;border:1px solid #8c1c2f;width:90%;max-width:400px;border-radius:8px;box-shadow:0 4px 8px rgba(0,0,0,0.2);">
            <h2 style="margin-top:0;color:#8c1c2f;border-bottom:2px solid #f4a523;padding-bottom:10px;">Select date range</h2>
            <form method="GET" action="admin-sales-report.php">
                <div style="margin-bottom:16px;">
                    <label style="display:block;margin-bottom:8px;color:#333;font-weight:600;">From date</label>
                    <input type="date" name="report_start" value="<?php echo htmlspecialchars($week_start); ?>" required style="width:100%;padding:10px;border:1px solid #ddd;border-radius:4px;font-family:Baloo 2;box-sizing:border-box;">
                </div>
                <div style="margin-bottom:16px;">
                    <label style="display:block;margin-bottom:8px;color:#333;font-weight:600;">To date</label>
                    <input type="date" name="report_end" value="<?php echo htmlspecialchars($week_end); ?>" required style="width:100%;padding:10px;border:1px solid #ddd;border-radius:4px;font-family:Baloo 2;box-sizing:border-box;">
                </div>
                <div style="margin-bottom:16px;">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                        <button type="button" class="btn-ghost" style="width:100%;padding:8px;font-size:12px;" onclick="setQuickRange('this_week')">This Week</button>
                        <button type="button" class="btn-ghost" style="width:100%;padding:8px;font-size:12px;" onclick="setQuickRange('last_week')">Last Week</button>
                        <button type="button" class="btn-ghost" style="width:100%;padding:8px;font-size:12px;" onclick="setQuickRange('this_month')">This Month</button>
                        <button type="button" class="btn-ghost" style="width:100%;padding:8px;font-size:12px;" onclick="setQuickRange('last_month')">Last Month</button>
                    </div>
                </div>
                <div style="display:flex;gap:10px;margin-top:20px;">
                    <button type="submit" class="btn-accent" style="flex:1;">Apply Filter</button>
                    <button type="button" class="btn-ghost" style="flex:1;cursor:pointer;" onclick="document.getElementById('dateModal').style.display='none'">Cancel</button>
                </div>
            </form>
            <script>
                function setQuickRange(period) {
                    const today = new Date();
                    let startDate, endDate;
                    
                    switch(period) {
                        case 'this_week':
                            const dayOfWeek = today.getDay();
                            const diff = today.getDate() - dayOfWeek + (dayOfWeek === 0 ? -6 : 1);
                            startDate = new Date(today.setDate(diff));
                            endDate = new Date(startDate);
                            endDate.setDate(startDate.getDate() + 6);
                            break;
                        case 'last_week':
                            const lastWeekStart = new Date(today);
                            lastWeekStart.setDate(today.getDate() - today.getDay() - 6);
                            startDate = lastWeekStart;
                            endDate = new Date(lastWeekStart);
                            endDate.setDate(lastWeekStart.getDate() + 6);
                            break;
                        case 'this_month':
                            startDate = new Date(today.getFullYear(), today.getMonth(), 1);
                            endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                            break;
                        case 'last_month':
                            startDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                            endDate = new Date(today.getFullYear(), today.getMonth(), 0);
                            break;
                    }
                    
                    document.querySelector('input[name="report_start"]').value = startDate.toISOString().split('T')[0];
                    document.querySelector('input[name="report_end"]').value = endDate.toISOString().split('T')[0];
                }
            </script>
        </div>
    </div>

    <!-- Export Report Modal -->
    <div id="exportReportModal" style="display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;background-color:rgba(0,0,0,0.5);">
        <div style="background-color:#fce9d4;margin:100px auto;padding:30px;border:1px solid #8c1c2f;width:90%;max-width:400px;border-radius:8px;box-shadow:0 4px 8px rgba(0,0,0,0.2);">
            <h2 style="margin-top:0;color:#8c1c2f;border-bottom:2px solid #f4a523;padding-bottom:10px;">Export sales report</h2>
            <form method="POST" action="auth/admin_export_sales_report.php">
                <div style="margin-bottom:16px;">
                    <label style="display:block;margin-bottom:8px;color:#333;font-weight:600;">Report period</label>
                    <select name="period" required style="width:100%;padding:10px;border:1px solid #ddd;border-radius:4px;font-family:Baloo 2;box-sizing:border-box;">
                        <option value="this_week">This week</option>
                        <option value="last_week">Last week</option>
                        <option value="this_month">This month</option>
                        <option value="last_month">Last month</option>
                        <option value="custom">Custom range</option>
                    </select>
                </div>
                <div id="customDates" style="display:none;">
                    <div style="margin-bottom:16px;">
                        <label style="display:block;margin-bottom:8px;color:#333;font-weight:600;">Start date</label>
                        <input type="date" name="custom_start" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:4px;font-family:Baloo 2;box-sizing:border-box;">
                    </div>
                    <div style="margin-bottom:16px;">
                        <label style="display:block;margin-bottom:8px;color:#333;font-weight:600;">End date</label>
                        <input type="date" name="custom_end" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:4px;font-family:Baloo 2;box-sizing:border-box;">
                    </div>
                </div>
                <script>
                    document.querySelector('select[name="period"]').addEventListener('change', function() {
                        document.getElementById('customDates').style.display = this.value === 'custom' ? 'block' : 'none';
                    });
                </script>
                <div style="margin-bottom:16px;">
                    <label style="display:block;margin-bottom:8px;color:#333;font-weight:600;">Include</label>
                    <div style="margin:8px 0;">
                        <input type="checkbox" name="include_daily" checked id="daily"> 
                        <label for="daily" style="display:inline;margin:0 5px;">Daily breakdown</label>
                    </div>
                    <div style="margin:8px 0;">
                        <input type="checkbox" name="include_products" checked id="products">
                        <label for="products" style="display:inline;margin:0 5px;">Top products</label>
                    </div>
                </div>
                <div style="display:flex;gap:10px;margin-top:20px;">
                    <button type="submit" class="btn-accent" style="flex:1;">Download CSV</button>
                    <button type="button" class="btn-ghost" style="flex:1;cursor:pointer;" onclick="document.getElementById('exportReportModal').style.display='none'">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Chart Modal -->
    <div id="chartModal" style="display:none;position:fixed;z-index:1001;left:0;top:0;width:100%;height:100%;background-color:rgba(0,0,0,0.7);overflow-y:auto;">
        <div style="background-color:#fff;margin:50px auto;padding:30px;width:90%;max-width:1000px;border-radius:12px;box-shadow:0 8px 16px rgba(0,0,0,0.3);position:relative;">
            <button onclick="closeChartModal()" style="position:absolute;top:15px;right:15px;background:none;border:none;font-size:28px;cursor:pointer;color:#999;font-weight:bold;">&times;</button>
            <h2 id="chartTitle" style="color:#8c1c2f;margin-bottom:25px;display:flex;align-items:center;gap:10px;"></h2>
            <div style="background:#f9f9f9;padding:20px;border-radius:8px;">
                <canvas id="salesChart" style="max-height:500px;"></canvas>
            </div>
            <div style="margin-top:20px;display:flex;gap:10px;justify-content:flex-end;">
                <button id="chartTypeBtn" class="btn-ghost" onclick="toggleChartType()" style="cursor:pointer;"><i class="fa-solid fa-repeat"></i> Change Type</button>
                <button class="btn-accent" onclick="downloadChart()" style="cursor:pointer;"><i class="fa-solid fa-download"></i> Download PNG</button>
                <button class="btn-ghost" onclick="closeChartModal()" style="cursor:pointer;">Close</button>
            </div>
        </div>
    </div>

    <script>
        let currentChart = null;
        let currentChartType = 'bar';
        let currentDataType = 'daily';

        // Prepare data from PHP
        const chartData = {
            daily: {
                labels: <?php echo json_encode($daily_labels); ?>,
                revenue: <?php echo json_encode($daily_revenue); ?>,
                orders: <?php echo json_encode($daily_orders); ?>
            },
            payment: {
                labels: <?php echo json_encode($payment_labels); ?>,
                data: <?php echo json_encode($payment_data); ?>
            },
            products: {
                labels: <?php echo json_encode($product_labels); ?>,
                data: <?php echo json_encode($product_data); ?>
            }
        };

        function openChartModal(type) {
            currentDataType = type;
            currentChartType = type === 'payment' ? 'pie' : 'bar';
            document.getElementById('chartModal').style.display = 'block';
            renderChart();
        }

        function closeChartModal() {
            document.getElementById('chartModal').style.display = 'none';
            if (currentChart) {
                currentChart.destroy();
                currentChart = null;
            }
        }

        function toggleChartType() {
            if (currentDataType === 'daily') {
                currentChartType = currentChartType === 'bar' ? 'line' : 'bar';
            } else if (currentDataType === 'payment') {
                currentChartType = currentChartType === 'pie' ? 'doughnut' : 'pie';
            } else {
                currentChartType = currentChartType === 'bar' ? 'horizontalBar' : 'bar';
            }
            renderChart();
        }

        function renderChart() {
            const ctx = document.getElementById('salesChart').getContext('2d');
            
            if (currentChart) {
                currentChart.destroy();
            }

            let config = {};
            const titleEl = document.getElementById('chartTitle');

            if (currentDataType === 'daily') {
                titleEl.innerHTML = '<i class="fa-solid fa-chart-line"></i> Daily Sales Trend';
                config = {
                    type: currentChartType,
                    data: {
                        labels: chartData.daily.labels,
                        datasets: [{
                            label: 'Revenue (₱)',
                            data: chartData.daily.revenue,
                            backgroundColor: 'rgba(140, 28, 47, 0.6)',
                            borderColor: 'rgba(140, 28, 47, 1)',
                            borderWidth: 2,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: { display: true, position: 'top' },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return 'Revenue: ₱' + context.parsed.y.toLocaleString('en-PH', {minimumFractionDigits: 2});
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '₱' + value.toLocaleString('en-PH');
                                    }
                                }
                            }
                        }
                    }
                };
            } else if (currentDataType === 'payment') {
                titleEl.innerHTML = '<i class="fa-solid fa-chart-pie"></i> Payment Methods Distribution';
                const colors = ['#8c1c2f', '#f4a523', '#06a77d', '#3b82f6', '#a855f7'];
                config = {
                    type: currentChartType,
                    data: {
                        labels: chartData.payment.labels,
                        datasets: [{
                            label: 'Revenue',
                            data: chartData.payment.data,
                            backgroundColor: colors,
                            borderWidth: 2,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { position: 'right' },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = '₱' + context.parsed.toLocaleString('en-PH', {minimumFractionDigits: 2});
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = ((context.parsed / total) * 100).toFixed(1) + '%';
                                        return label + ': ' + value + ' (' + percentage + ')';
                                    }
                                }
                            }
                        }
                    }
                };
            } else if (currentDataType === 'products') {
                titleEl.innerHTML = '<i class="fa-solid fa-chart-bar"></i> Top Products by Revenue';
                config = {
                    type: currentChartType,
                    data: {
                        labels: chartData.products.labels,
                        datasets: [{
                            label: 'Revenue (₱)',
                            data: chartData.products.data,
                            backgroundColor: 'rgba(244, 165, 35, 0.7)',
                            borderColor: 'rgba(244, 165, 35, 1)',
                            borderWidth: 2
                        }]
                    },
                    options: {
                        indexAxis: currentChartType === 'horizontalBar' ? 'y' : 'x',
                        responsive: true,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return 'Revenue: ₱' + context.parsed.y.toLocaleString('en-PH', {minimumFractionDigits: 2});
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '₱' + value.toLocaleString('en-PH');
                                    }
                                }
                            }
                        }
                    }
                };
            }

            currentChart = new Chart(ctx, config);
        }

        function downloadChart() {
            if (currentChart) {
                const url = currentChart.toBase64Image();
                const a = document.createElement('a');
                a.href = url;
                a.download = 'sales-chart-' + currentDataType + '-' + Date.now() + '.png';
                a.click();
            }
        }

        // Close modal on outside click
        window.onclick = function(event) {
            const modal = document.getElementById('chartModal');
            if (event.target === modal) {
                closeChartModal();
            }
        };
    </script>
</body>
</html>
