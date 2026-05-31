<?php
require_once __DIR__ . '/config.php';
require_role('admin');
$flash = consume_flash();

// Fetch POS metrics (using global $mysqli from config.php)

// Today's transactions
$today = date('Y-m-d');
$result = $mysqli->query("SELECT COUNT(*) as count, SUM(amount) as total FROM sales_records WHERE sale_date = '$today'");
$today_stats = $result->fetch_assoc();

// Hourly transactions
$hour = date('Y-m-d H:');
$result = $mysqli->query("SELECT COUNT(*) as count FROM sales_records WHERE sale_date = '$today' AND created_at >= '$hour:00:00' AND created_at < DATE_ADD('$hour:00:00', INTERVAL 1 HOUR)");
$hour_stats = $result->fetch_assoc();

// Payment methods breakdown
$payment_result = $mysqli->query("
    SELECT 'Cash' as method, COUNT(*) as count, SUM(amount) as total FROM sales_records WHERE sale_date = '$today' AND amount > 0
    UNION
    SELECT 'Card', COUNT(*), SUM(amount) FROM sales_records WHERE sale_date = '$today' AND amount > 0
");

// Recent transactions (last 20)
$recent = $mysqli->query("
    SELECT sr.*, rp.store_name FROM sales_records sr
    LEFT JOIN reseller_profile rp ON sr.user_id = rp.user_id
    WHERE sr.sale_date = '$today'
    ORDER BY sr.created_at DESC LIMIT 20
");

// Top products today
$top_products = $mysqli->query("
    SELECT product_id, SUM(quantity) as sold, SUM(amount) as revenue FROM sales_records
    WHERE sale_date = '$today'
    GROUP BY product_id
    ORDER BY sold DESC LIMIT 10
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Point of Sale | SiPao Portal</title>
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
                <a class="nav-link" href="admin-dashboard.php"><i class="fa-solid fa-chart-pie"></i>Dashboard</a>
                <a class="nav-link" href="admin-sales-report.php"><i class="fa-solid fa-chart-line"></i>Sales Report</a>
                <a class="nav-link" href="admin-inventory.php"><i class="fa-solid fa-boxes-stacked"></i>Inventory</a>
                <a class="nav-link active" href="admin-pos.php"><i class="fa-solid fa-cash-register"></i>Point of Sale</a>
                <a class="nav-link" href="admin-resellers.php"><i class="fa-solid fa-users"></i>Resellers</a>
                <a class="nav-link" href="admin-account-settings.php"><i class="fa-solid fa-gear"></i>Account Settings</a>
            </div>
        </aside>
        <main class="portal-main">
            <div class="topbar">
                <div>
                    <h1>POS oversight</h1>
                    <div class="breadcrumb">Monlei SiPao • Real-time transaction monitoring</div>
                </div>
                <div class="quick-actions">
                    <button class="btn-ghost" onclick="document.getElementById('kiosksModal').style.display='block'"><i class="fa-solid fa-display"></i> Monitor kiosks</button>
                    <button class="btn-accent" onclick="location.reload()"><i class="fa-solid fa-arrow-rotate-right"></i> Refresh data</button>
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
                        <h3>Today's transactions</h3>
                        <span class="metric"><?php echo $today_stats['count'] ?? 0; ?></span>
                        <span class="trend"><i class="fa-solid fa-cash-register"></i> ₱<?php echo number_format($today_stats['total'] ?? 0, 2); ?> total</span>
                    </div>
                    <div class="card">
                        <h3>This hour</h3>
                        <span class="metric"><?php echo $hour_stats['count'] ?? 0; ?></span>
                        <span class="trend"><i class="fa-solid fa-hourglass-end"></i> Transactions</span>
                    </div>
                    <div class="card">
                        <h3>Active kiosks</h3>
                        <span class="metric">6 / 6</span>
                        <span class="trend"><i class="fa-solid fa-circle-check"></i> All online</span>
                    </div>
                    <div class="card">
                        <h3>Avg transaction</h3>
                        <span class="metric">₱<?php echo ($today_stats['count'] > 0) ? number_format($today_stats['total'] / $today_stats['count'], 0) : '0'; ?></span>
                        <span class="trend"><i class="fa-solid fa-chart-line"></i> Per order</span>
                    </div>
                </div>
                <div class="table-card">
                    <header>
                        <h2>Live transaction feed</h2>
                        <div style="display:flex;gap:10px;">
                            <button class="btn-ghost" onclick="document.getElementById('exportPosModal').style.display='block'"><i class="fa-solid fa-download"></i> Export</button>
                            <button class="btn-ghost" onclick="document.getElementById('filterModal').style.display='block'"><i class="fa-solid fa-filter"></i> Filter</button>
                        </div>
                    </header>
                    <table>
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Reseller/Outlet</th>
                                <th>Items Qty</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($recent && $recent->num_rows > 0) {
                                while ($row = $recent->fetch_assoc()) {
                                    $time = date('H:i', strtotime($row['created_at']));
                                    $store = $row['store_name'] ?? 'Direct Sale';
                                    $qty = $row['quantity'] ?? 1;
                                    $amount = number_format($row['amount'], 2);
                                    echo "<tr>
                                        <td>$time</td>
                                        <td>$store</td>
                                        <td>{$qty} item(s)</td>
                                        <td>₱{$amount}</td>
                                        <td><span style='background:#06a77d;color:#fff;padding:4px 12px;border-radius:4px;font-size:0.85em;'>Completed</span></td>
                                    </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5' style='text-align:center;color:#999;'>No transactions today</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <div class="info-banner">
                    <i class="fa-solid fa-lightbulb"></i>
                    <div>
                        <strong>Insight:</strong> Peak transaction hours are 11 AM-1 PM and 5-7 PM. Consider staffing adjustments to ensure kiosk availability during high-demand periods.
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Monitor Kiosks Modal -->
    <div id="kiosksModal" style="display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;background-color:rgba(0,0,0,0.5);overflow-y:auto;">
        <div style="background-color:#fce9d4;margin:50px auto;padding:30px;border:1px solid #8c1c2f;width:90%;max-width:600px;border-radius:8px;box-shadow:0 4px 8px rgba(0,0,0,0.2);">
            <h2 style="margin-top:0;color:#8c1c2f;border-bottom:2px solid #f4a523;padding-bottom:10px;display:flex;justify-content:space-between;align-items:center;">
                <span>Kiosk Status Monitor</span>
                <button type="button" onclick="document.getElementById('kiosksModal').style.display='none'" style="background:none;border:none;font-size:24px;cursor:pointer;color:#8c1c2f;">&times;</button>
            </h2>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;">
                <div style="padding:15px;background:#f0f0f0;border-radius:6px;border-left:4px solid #06a77d;">
                    <div style="font-weight:700;color:#333;">Kiosk 1</div>
                    <div style="color:#06a77d;font-size:0.9em;margin-top:5px;"><i class="fa-solid fa-circle"></i> ONLINE</div>
                    <div style="font-size:0.8em;color:#666;margin-top:5px;">Transactions: 28 | Uptime: 100%</div>
                </div>
                <div style="padding:15px;background:#f0f0f0;border-radius:6px;border-left:4px solid #06a77d;">
                    <div style="font-weight:700;color:#333;">Kiosk 2</div>
                    <div style="color:#06a77d;font-size:0.9em;margin-top:5px;"><i class="fa-solid fa-circle"></i> ONLINE</div>
                    <div style="font-size:0.8em;color:#666;margin-top:5px;">Transactions: 35 | Uptime: 100%</div>
                </div>
                <div style="padding:15px;background:#f0f0f0;border-radius:6px;border-left:4px solid #06a77d;">
                    <div style="font-weight:700;color:#333;">Kiosk 3</div>
                    <div style="color:#06a77d;font-size:0.9em;margin-top:5px;"><i class="fa-solid fa-circle"></i> ONLINE</div>
                    <div style="font-size:0.8em;color:#666;margin-top:5px;">Transactions: 31 | Uptime: 100%</div>
                </div>
                <div style="padding:15px;background:#f0f0f0;border-radius:6px;border-left:4px solid #06a77d;">
                    <div style="font-weight:700;color:#333;">Kiosk 4</div>
                    <div style="color:#06a77d;font-size:0.9em;margin-top:5px;"><i class="fa-solid fa-circle"></i> ONLINE</div>
                    <div style="font-size:0.8em;color:#666;margin-top:5px;">Transactions: 26 | Uptime: 100%</div>
                </div>
                <div style="padding:15px;background:#f0f0f0;border-radius:6px;border-left:4px solid #06a77d;">
                    <div style="font-weight:700;color:#333;">Kiosk 5</div>
                    <div style="color:#06a77d;font-size:0.9em;margin-top:5px;"><i class="fa-solid fa-circle"></i> ONLINE</div>
                    <div style="font-size:0.8em;color:#666;margin-top:5px;">Transactions: 33 | Uptime: 100%</div>
                </div>
                <div style="padding:15px;background:#f0f0f0;border-radius:6px;border-left:4px solid #06a77d;">
                    <div style="font-weight:700;color:#333;">Kiosk 6</div>
                    <div style="color:#06a77d;font-size:0.9em;margin-top:5px;"><i class="fa-solid fa-circle"></i> ONLINE</div>
                    <div style="font-size:0.8em;color:#666;margin-top:5px;">Transactions: 29 | Uptime: 100%</div>
                </div>
            </div>
            <div style="margin-top:20px;padding:15px;background:#e8f5e9;border-radius:6px;border-left:4px solid #06a77d;">
                <strong style="color:#06a77d;"><i class="fa-solid fa-check-circle"></i> All 6 kiosks operational</strong>
                <div style="font-size:0.9em;color:#666;margin-top:5px;">Last sync: <?php echo date('Y-m-d H:i:s'); ?></div>
            </div>
            <div style="text-align:right;margin-top:20px;">
                <button type="button" class="btn-ghost" style="cursor:pointer;" onclick="document.getElementById('kiosksModal').style.display='none'">Close</button>
            </div>
        </div>
    </div>

    <!-- Export POS Data Modal -->
    <div id="exportPosModal" style="display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;background-color:rgba(0,0,0,0.5);">
        <div style="background-color:#fce9d4;margin:100px auto;padding:30px;border:1px solid #8c1c2f;width:90%;max-width:400px;border-radius:8px;box-shadow:0 4px 8px rgba(0,0,0,0.2);">
            <h2 style="margin-top:0;color:#8c1c2f;border-bottom:2px solid #f4a523;padding-bottom:10px;">Export POS Report</h2>
            <form method="POST" action="auth/admin_export_pos.php">
                <div style="margin-bottom:16px;">
                    <label style="display:block;margin-bottom:8px;color:#333;font-weight:600;">Start Date</label>
                    <input type="date" name="start_date" value="<?php echo date('Y-m-d'); ?>" required style="width:100%;padding:10px;border:1px solid #ddd;border-radius:4px;font-family:Baloo 2;box-sizing:border-box;">
                </div>
                <div style="margin-bottom:16px;">
                    <label style="display:block;margin-bottom:8px;color:#333;font-weight:600;">End Date</label>
                    <input type="date" name="end_date" value="<?php echo date('Y-m-d'); ?>" required style="width:100%;padding:10px;border:1px solid #ddd;border-radius:4px;font-family:Baloo 2;box-sizing:border-box;">
                </div>
                <div style="margin-bottom:16px;">
                    <label style="display:block;margin-bottom:8px;color:#333;font-weight:600;">Report Type</label>
                    <select name="report_type" required style="width:100%;padding:10px;border:1px solid #ddd;border-radius:4px;font-family:Baloo 2;box-sizing:border-box;">
                        <option value="transactions">All Transactions</option>
                        <option value="summary">Daily Summary</option>
                        <option value="by_reseller">By Reseller/Outlet</option>
                    </select>
                </div>
                <div style="display:flex;gap:10px;margin-top:20px;">
                    <button type="submit" class="btn-accent" style="flex:1;">Download CSV</button>
                    <button type="button" class="btn-ghost" style="flex:1;cursor:pointer;" onclick="document.getElementById('exportPosModal').style.display='none'">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Filter Transactions Modal -->
    <div id="filterModal" style="display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;background-color:rgba(0,0,0,0.5);">
        <div style="background-color:#fce9d4;margin:100px auto;padding:30px;border:1px solid #8c1c2f;width:90%;max-width:400px;border-radius:8px;box-shadow:0 4px 8px rgba(0,0,0,0.2);">
            <h2 style="margin-top:0;color:#8c1c2f;border-bottom:2px solid #f4a523;padding-bottom:10px;">Filter Transactions</h2>
            <form method="GET" action="admin-pos.php">
                <div style="margin-bottom:16px;">
                    <label style="display:block;margin-bottom:8px;color:#333;font-weight:600;">Date</label>
                    <input type="date" name="filter_date" value="<?php echo date('Y-m-d'); ?>" required style="width:100%;padding:10px;border:1px solid #ddd;border-radius:4px;font-family:Baloo 2;box-sizing:border-box;">
                </div>
                <div style="margin-bottom:16px;">
                    <label style="display:block;margin-bottom:8px;color:#333;font-weight:600;">Minimum Amount</label>
                    <input type="number" name="min_amount" placeholder="0" step="0.01" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:4px;font-family:Baloo 2;box-sizing:border-box;">
                </div>
                <div style="margin-bottom:16px;">
                    <label style="display:block;margin-bottom:8px;color:#333;font-weight:600;">Maximum Amount</label>
                    <input type="number" name="max_amount" placeholder="999999" step="0.01" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:4px;font-family:Baloo 2;box-sizing:border-box;">
                </div>
                <div style="display:flex;gap:10px;margin-top:20px;">
                    <button type="submit" class="btn-accent" style="flex:1;">Apply Filter</button>
                    <button type="button" class="btn-ghost" style="flex:1;cursor:pointer;" onclick="document.getElementById('filterModal').style.display='none'">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
