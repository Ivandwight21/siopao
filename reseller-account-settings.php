<?php
require_once __DIR__ . '/config.php';
require_role('reseller');
$flash = consume_flash();

// Current reseller
$reseller_id = $_SESSION['user_id'] ?? ($_SESSION['user']['id'] ?? 0);

// Load reseller profile + user info
$profile = [
    'username' => 'Reseller',
    'email' => '',
    'store_name' => '',
    'phone_number' => '',
    'bank_name' => '',
    'account_number' => '',
    'account_holder' => '',
    'created_at' => date('Y-m-d H:i:s'),
];

$stmt = $mysqli->prepare("SELECT u.username, u.email, u.created_at, rp.store_name, rp.phone_number, rp.bank_name, rp.account_number, rp.account_holder FROM users u LEFT JOIN reseller_profile rp ON u.id = rp.user_id WHERE u.id = ?");
if ($stmt) {
    $stmt->bind_param('i', $reseller_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    if ($res) {
        $profile = array_merge($profile, $res);
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reseller Account Settings | SiPao Portal</title>
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
                <a class="nav-link" href="reseller-pos.php"><i class="fa-solid fa-cash-register"></i>Point of Sale 🛒</a>
                <a class="nav-link active" href="reseller-account-settings.php"><i class="fa-solid fa-user"></i>Account Settings 👤</a>
            </div>
        </aside>
        <main class="portal-main">
            <div class="topbar">
                <div>
                    <h1>Keep your profile updated</h1>
                    <div class="breadcrumb">Edit your contact and payout details.</div>
                </div>
                <div class="quick-actions">
                    <a href="https://help.monleisiopao.com" target="_blank" class="btn-ghost"><i class="fa-solid fa-circle-question"></i> Need help?</a>
                    <button class="btn-accent" id="saveProfileBtn"><i class="fa-solid fa-floppy-disk"></i> Save profile</button>
                    <a class="btn-logout" href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
                </div>
            </div>
            <section class="content-section">
                <div class="info-banner">
                    <i class="fa-solid fa-id-card"></i>
                    <div>
                        <strong>Heads up:</strong> Accurate payout info ensures your Friday remittances arrive on time.
                    </div>
                </div>
                <?php if ($flash): ?>
                <div style="margin-bottom:16px;padding:12px;border-radius:8px;color:#fff;background-color:<?php echo $flash['type']==='error' ? '#c1121f' : '#06a77d'; ?>;font-weight:700;">
                    <?php echo htmlspecialchars($flash['message']); ?>
                </div>
                <?php endif; ?>
                <div class="settings-grid">
                    <div class="settings-card">
                        <h3><i class="fa-solid fa-user"></i> Contact details</h3>
                        <p><strong>Username:</strong> <?php echo htmlspecialchars($profile['username'] ?? 'Reseller'); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($profile['email'] ?? ''); ?></p>
                        <p><strong>Partner since:</strong> <?php echo isset($profile['created_at']) ? date('F j, Y', strtotime($profile['created_at'])) : 'Unknown'; ?></p>
                        <form id="contactForm" method="POST" action="auth/update_contact.php">
                            <div style="margin-bottom:10px;">
                                <label for="contact_name" style="display:block;margin-bottom:5px;font-size:14px;color:#5b1221;">Store / contact name</label>
                                <input type="text" id="contact_name" name="contact_name" value="<?php echo htmlspecialchars($profile['store_name'] ?? ''); ?>" placeholder="Your store name" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;" required>
                            </div>
                            <div style="margin-bottom:10px;">
                                <label for="phone" style="display:block;margin-bottom:5px;font-size:14px;color:#5b1221;">Phone</label>
                                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($profile['phone_number'] ?? ''); ?>" placeholder="+63 917 555 8899" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;" required>
                            </div>
                            <button type="submit" class="btn-ghost"><i class="fa-solid fa-pen"></i> Update contact</button>
                        </form>
                    </div>
                    <div class="settings-card">
                        <h3><i class="fa-solid fa-building-columns"></i> Payout details</h3>
                        <form id="payoutForm" method="POST" action="auth/update_payout.php">
                            <div style="margin-bottom:10px;">
                                <label for="bank_name" style="display:block;margin-bottom:5px;font-size:14px;color:#5b1221;">Bank</label>
                                <select id="bank_name" name="bank_name" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;" required>
                                    <option value="">Select Bank</option>
                                    <?php
                                    $banks = ['BPI','BDO','Metrobank','UnionBank','PNB','RCBC','Security Bank'];
                                    foreach ($banks as $bank) {
                                        $selected = ($profile['bank_name'] === $bank) ? 'selected' : '';
                                        echo "<option value=\"$bank\" $selected>$bank</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div style="margin-bottom:10px;">
                                <label for="account_holder" style="display:block;margin-bottom:5px;font-size:14px;color:#5b1221;">Account Holder</label>
                                <input type="text" id="account_holder" name="account_holder" value="<?php echo htmlspecialchars($profile['account_holder'] ?? ''); ?>" placeholder="Name on bank account" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;" required>
                            </div>
                            <div style="margin-bottom:10px;">
                                <label for="account_number" style="display:block;margin-bottom:5px;font-size:14px;color:#5b1221;">Account Number</label>
                                <input type="text" id="account_number" name="account_number" value="<?php echo htmlspecialchars($profile['account_number'] ?? ''); ?>" placeholder="Your account number" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;" required>
                            </div>
                            <button type="submit" class="btn-ghost"><i class="fa-solid fa-building-columns"></i> Save payout details</button>
                        </form>
                    </div>
                    <div class="settings-card">
                        <h3><i class="fa-solid fa-bell"></i> Notifications</h3>
                        <p>Choose what you want to hear about.</p>
                        <form method="POST" action="auth/customize_alerts.php">
                            <div style="margin-bottom:10px;">
                                <label style="display:flex;align-items:center;gap:8px;">
                                    <input type="checkbox" name="stock_delivery" checked>
                                    <span>Stock delivery notifications</span>
                                </label>
                            </div>
                            <div style="margin-bottom:10px;">
                                <label style="display:flex;align-items:center;gap:8px;">
                                    <input type="checkbox" name="payout_releases" checked>
                                    <span>Payout release alerts</span>
                                </label>
                            </div>
                            <div style="margin-bottom:10px;">
                                <label style="display:flex;align-items:center;gap:8px;">
                                    <input type="checkbox" name="promo_campaigns" checked>
                                    <span>Promotional campaigns</span>
                                </label>
                            </div>
                            <div style="margin-bottom:10px;">
                                <label style="display:flex;align-items:center;gap:8px;">
                                    <input type="checkbox" name="low_stock_alerts" checked>
                                    <span>Low stock alerts</span>
                                </label>
                            </div>
                            <div style="margin-bottom:10px;">
                                <label style="display:flex;align-items:center;gap:8px;">
                                    <input type="checkbox" name="sales_milestones">
                                    <span>Sales milestone updates</span>
                                </label>
                            </div>
                            <button type="submit" class="btn-ghost"><i class="fa-solid fa-sliders"></i> Save preferences</button>
                        </form>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script>
        // Shortcut save: submit both forms sequentially
        document.getElementById('saveProfileBtn')?.addEventListener('click', () => {
            const contactForm = document.getElementById('contactForm');
            const payoutForm = document.getElementById('payoutForm');
            if (contactForm) contactForm.submit();
            // payout form will run after redirect if contact form redirects; keep manual submit available
        });
    </script>
</body>
</html>
