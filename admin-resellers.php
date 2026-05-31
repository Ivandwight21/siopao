<?php
require_once __DIR__ . '/config.php';
require_role('admin');
$flash = consume_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Resellers | SiPao Portal</title>
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
                <a class="nav-link" href="admin-pos.php"><i class="fa-solid fa-cash-register"></i>Point of Sale</a>
                <a class="nav-link active" href="admin-resellers.php"><i class="fa-solid fa-users"></i>Resellers</a>
                <a class="nav-link" href="admin-account-settings.php"><i class="fa-solid fa-gear"></i>Account Settings</a>
            </div>
        </aside>
        <main class="portal-main">
            <div class="topbar">
                <div>
                    <h1>Reseller network</h1>
                    <div class="breadcrumb">Monlei SiPao • Performance by partner</div>
                </div>
                <div class="quick-actions">
                    <a href="admin-reseller-management.php" class="btn-ghost"><i class="fa-solid fa-list"></i> Manage accounts</a>
                    <button class="btn-ghost" onclick="document.getElementById('inviteModal').style.display='block'"><i class="fa-solid fa-user-plus"></i> Invite reseller</button>
                    <button class="btn-accent" onclick="document.getElementById('incentiveModal').style.display='block'"><i class="fa-solid fa-handshake"></i> Launch incentive</button>
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
                        <h3>Active partners</h3>
                        <span class="metric">86</span>
                        <span class="trend"><i class="fa-solid fa-users"></i> 9 new this month</span>
                    </div>
                    <div class="card">
                        <h3>Average monthly sales</h3>
                        <span class="metric">₱52,800</span>
                        <span class="trend"><i class="fa-solid fa-chart-line"></i> +11% vs last month</span>
                    </div>
                    <div class="card">
                        <h3>Top performing city</h3>
                        <span class="metric">Quezon City</span>
                        <span class="trend"><i class="fa-solid fa-location-dot"></i> ₱118k monthly run rate</span>
                    </div>
                    <div class="card">
                        <h3>Support tickets</h3>
                        <span class="metric">5</span>
                        <span class="trend" style="color: var(--danger);"><i class="fa-solid fa-headset"></i> 2 urgent</span>
                    </div>
                </div>
                <div class="table-card">
                    <header>
                        <h2>Top reseller performance</h2>
                        <div style="display:flex;gap:10px;align-items:center;">
                            <input type="text" id="partnerSearch" placeholder="Search by name, region..." onkeyup="searchPartners()" 
                                   style="padding:8px 12px;border:1px solid #ddd;border-radius:4px;font-family:Baloo 2;width:250px;">
                            <button class="btn-ghost" onclick="document.getElementById('partnerSearch').value=''; searchPartners();"><i class="fa-solid fa-x"></i></button>
                        </div>
                    </header>
                    <table>
                        <thead>
                            <tr>
                                <th>Reseller</th>
                                <th>Region</th>
                                <th>Monthly sales</th>
                                <th>Growth</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="resellerTable">
                            <tr class="reseller-row" data-search="steam masters ncr">
                                <td>Steam Masters</td>
                                <td>NCR</td>
                                <td>₱146,200</td>
                                <td><span class="badge success"><i class="fa-solid fa-arrow-up"></i>+18%</span></td>
                                <td><span class="badge success"><i class="fa-solid fa-circle-check"></i>Prime</span></td>
                            </tr>
                            <tr class="reseller-row" data-search="paoking express south luzon">
                                <td>PaoKing Express</td>
                                <td>South Luzon</td>
                                <td>₱98,450</td>
                                <td><span class="badge success"><i class="fa-solid fa-arrow-up"></i>+9%</span></td>
                                <td><span class="badge success"><i class="fa-solid fa-circle-check"></i>Prime</span></td>
                            </tr>
                            <tr class="reseller-row" data-search="steamy bites north luzon">
                                <td>Steamy Bites</td>
                                <td>North Luzon</td>
                                <td>₱76,930</td>
                                <td><span class="badge danger"><i class="fa-solid fa-arrow-down"></i>-3%</span></td>
                                <td><span class="badge danger"><i class="fa-solid fa-circle-exclamation"></i>Assist</span></td>
                            </tr>
                            <tr class="reseller-row" data-search="bao buddies visayas">
                                <td>Bao Buddies</td>
                                <td>Visayas</td>
                                <td>₱64,270</td>
                                <td><span class="badge success"><i class="fa-solid fa-arrow-up"></i>+6%</span></td>
                                <td><span class="badge success"><i class="fa-solid fa-circle-check"></i>Prime</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="info-banner">
                    <i class="fa-solid fa-lightbulb"></i>
                    <div>
                        <strong>Program highlight:</strong> Launch the "Steamy Rewards" tier upgrade for partners hitting ₱120k in rolling 30-day sales. Incentives include free marketing kits and 7% product discount.
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Invite Reseller Modal -->
    <div id="inviteModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:1000;">
        <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);background:white;padding:30px;border-radius:12px;width:500px;">
            <h2 style="color:#8c1c2f;margin-bottom:20px;">Invite New Reseller</h2>
            <form method="POST" action="auth/admin_invite_reseller.php">
                <div style="margin-bottom:15px;">
                    <label style="display:block;margin-bottom:5px;font-weight:bold;">Email Address:</label>
                    <input type="email" name="email" placeholder="reseller@example.com" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:4px;" required>
                </div>
                <div style="margin-bottom:15px;">
                    <label style="display:block;margin-bottom:5px;font-weight:bold;">Full Name:</label>
                    <input type="text" name="name" placeholder="John Doe" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:4px;" required>
                </div>
                <div style="margin-bottom:15px;">
                    <label style="display:block;margin-bottom:5px;font-weight:bold;">Phone Number:</label>
                    <input type="tel" name="phone" placeholder="+63 917 555 1234" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:4px;" required>
                </div>
                <div style="display:flex;gap:10px;">
                    <button type="submit" style="flex:1;padding:12px;background:#8c1c2f;color:white;border:none;border-radius:6px;font-weight:bold;cursor:pointer;">Send Invitation</button>
                    <button type="button" onclick="document.getElementById('inviteModal').style.display='none'" style="flex:1;padding:12px;background:#ccc;color:#333;border:none;border-radius:6px;font-weight:bold;cursor:pointer;">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Launch Incentive Modal -->
    <div id="incentiveModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:1000;">
        <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);background:white;padding:30px;border-radius:12px;width:500px;">
            <h2 style="color:#8c1c2f;margin-bottom:20px;">Launch Incentive</h2>
            <form method="POST" action="auth/admin_launch_incentive.php">
                <div style="margin-bottom:15px;">
                    <label style="display:block;margin-bottom:5px;font-weight:bold;">Select Reseller:</label>
                    <select name="reseller_id" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:4px;" required>
                        <option value="">Choose reseller...</option>
                        <?php
                        $result = $mysqli->query('SELECT u.id, u.email FROM users u WHERE u.role = "reseller" ORDER BY u.email');
                        while ($row = $result->fetch_assoc()) {
                            echo '<option value="' . $row['id'] . '">' . htmlspecialchars($row['email']) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div style="margin-bottom:15px;">
                    <label style="display:block;margin-bottom:5px;font-weight:bold;">Incentive Type:</label>
                    <select name="incentive_type" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:4px;" required>
                        <option value="bonus">Cash Bonus</option>
                        <option value="discount_coupon">Discount Coupon</option>
                        <option value="commission_boost">Commission Boost</option>
                    </select>
                </div>
                <div style="margin-bottom:15px;">
                    <label style="display:block;margin-bottom:5px;font-weight:bold;">Value:</label>
                    <input type="text" name="incentive_value" placeholder="₱5,000 or 20%" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:4px;" required>
                </div>
                <div style="display:flex;gap:10px;">
                    <button type="submit" style="flex:1;padding:12px;background:#8c1c2f;color:white;border:none;border-radius:6px;font-weight:bold;cursor:pointer;">Launch Incentive</button>
                    <button type="button" onclick="document.getElementById('incentiveModal').style.display='none'" style="flex:1;padding:12px;background:#ccc;color:#333;border:none;border-radius:6px;font-weight:bold;cursor:pointer;">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function searchPartners() {
            const input = document.getElementById('partnerSearch');
            const filter = input.value.toLowerCase();
            const rows = document.querySelectorAll('.reseller-row');
            let visibleCount = 0;
            
            rows.forEach(row => {
                const searchText = row.getAttribute('data-search').toLowerCase();
                const isMatch = searchText.includes(filter);
                row.style.display = isMatch ? '' : 'none';
                if (isMatch) visibleCount++;
            });
            
            // Show "no results" message if needed
            if (visibleCount === 0 && filter.length > 0) {
                const table = document.getElementById('resellerTable');
                let noResults = document.getElementById('noResults');
                if (!noResults) {
                    noResults = document.createElement('tr');
                    noResults.id = 'noResults';
                    noResults.innerHTML = '<td colspan="5" style="text-align:center;color:#999;padding:30px;">No partners found matching "' + input.value + '"</td>';
                    table.appendChild(noResults);
                } else {
                    noResults.innerHTML = '<td colspan="5" style="text-align:center;color:#999;padding:30px;">No partners found matching "' + input.value + '"</td>';
                    noResults.style.display = '';
                }
            } else {
                const noResults = document.getElementById('noResults');
                if (noResults) noResults.style.display = 'none';
            }
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl+K or Cmd+K to focus search
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                document.getElementById('partnerSearch').focus();
            }
        });
    </script>
</body>
</html>
