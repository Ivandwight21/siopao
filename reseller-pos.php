<?php
require_once __DIR__ . '/config.php';
require_role('reseller');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reseller Point of Sale | SiPao Portal</title>
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
                <a class="nav-link" href="reseller-sales-report.php"><i class="fa-solid fa-chart-line"></i>Sales Report 📈</a>
                <a class="nav-link active" href="reseller-pos.php"><i class="fa-solid fa-cash-register"></i>Point of Sale 🛒</a>
                <a class="nav-link" href="reseller-kiosk.php"><i class="fa-solid fa-store"></i>Kiosk 🏪</a>
                <a class="nav-link" href="reseller-history.php"><i class="fa-solid fa-receipt"></i>History 🧾</a>
                <a class="nav-link" href="reseller-account-settings.php"><i class="fa-solid fa-user"></i>Account Settings 👤</a>
            </div>
        </aside>
        <main class="portal-main">
            <div class="topbar">
                <div>
                    <h1>Sell with speed</h1>
                    <div class="breadcrumb">Manage POS shortcuts, combos, and best practices.</div>
                </div>
                <div class="quick-actions">
                    <button class="btn-ghost" onclick="alert('POS configuration panel coming soon')"><i class="fa-solid fa-gear"></i> Configure buttons</button>
                    <button class="btn-accent" onclick="window.print()"><i class="fa-solid fa-print"></i> Print menu board</button>
                    <a class="btn-logout" href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
                </div>
            </div>
            <section class="content-section">
                <div class="card-grid">
                    <div class="card">
                        <h3>Active terminal</h3>
                        <span class="metric">POS-02</span>
                        <span class="trend"><i class="fa-solid fa-circle-check"></i> Connected</span>
                    </div>
                    <div class="card">
                        <h3>Today’s orders</h3>
                        <span class="metric">186</span>
                        <span class="trend"><i class="fa-solid fa-clock"></i> Lunch rush at 12:15 PM</span>
                    </div>
                    <div class="card">
                        <h3>Avg checkout time</h3>
                        <span class="metric">1m 56s</span>
                        <span class="trend"><i class="fa-solid fa-gauge"></i> Keep under 2m</span>
                    </div>
                    <div class="card">
                        <h3>Upsell success</h3>
                        <span class="metric">36%</span>
                        <span class="trend"><i class="fa-solid fa-arrow-trend-up"></i> Add drinks or desserts</span>
                    </div>
                </div>
                <div class="table-card">
                    <header>
                        <h2>Quick product buttons</h2>
                        <button class="btn-ghost"><i class="fa-solid fa-plus"></i> Add shortcut</button>
                    </header>
                    <table>
                        <thead>
                            <tr>
                                <th>Button name</th>
                                <th>SKU</th>
                                <th>Price</th>
                                <th>Modifier</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Classic pork</td>
                                <td>SIO-CL-01</td>
                                <td>₱60</td>
                                <td>+₱10 add egg</td>
                            </tr>
                            <tr>
                                <td>Chicken duo</td>
                                <td>SIO-CH-02</td>
                                <td>₱110</td>
                                <td>+₱15 add drink</td>
                            </tr>
                            <tr>
                                <td>Family pack 8s</td>
                                <td>SET-FM-08</td>
                                <td>₱420</td>
                                <td>+₱30 add sauce set</td>
                            </tr>
                            <tr>
                                <td>Ube cream bun</td>
                                <td>BUN-UB-05</td>
                                <td>₱49</td>
                                <td>+₱20 iced coffee</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="info-banner">
                    <i class="fa-solid fa-lightbulb"></i>
                    <div>
                        <strong>Reminder:</strong> Use the combo button before adding individual items—partners save 18 seconds per order when combos are preset.
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
