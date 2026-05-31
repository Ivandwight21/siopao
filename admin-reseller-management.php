<?php
require_once __DIR__ . '/config.php';
require_role('admin');
$flash = consume_flash();

// Fetch all resellers with their profile data
$resellers = $mysqli->query("
    SELECT u.id, u.email, u.username, u.created_at, 
           rp.store_name, rp.address, rp.phone_number, 
           rp.bank_name, rp.account_number, rp.account_holder,
           COUNT(sr.id) as total_sales, SUM(sr.amount) as total_revenue
    FROM users u
    LEFT JOIN reseller_profile rp ON u.id = rp.user_id
    LEFT JOIN sales_records sr ON u.id = sr.user_id
    WHERE u.role = 'reseller'
    GROUP BY u.id
    ORDER BY u.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reseller Management | SiPao Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/portal.css">
    <style>
        .action-buttons {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        .btn-small {
            padding: 6px 12px;
            font-size: 0.85em;
            border: 1px solid #ddd;
            background: #f9f9f9;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-small:hover {
            background: #e8e8e8;
        }
        .btn-small.danger {
            color: #c1121f;
            border-color: #c1121f;
        }
        .btn-small.danger:hover {
            background: #ffebee;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.85em;
            font-weight: 600;
        }
        .status-active {
            background: #e8f5e9;
            color: #06a77d;
        }
    </style>
</head>
<body>
    <div class="portal-shell">
        <aside class="sidebar">
            <div class="brand-block">
                <img src="123.jpg" alt="Monlei SiPao logo">
                <span class="portal-subtitle">Admin Control Center</span>
            </div>
            <div class="nav-group">
                <span class="nav-label">Management</span>
                <a class="nav-link" href="admin-dashboard.php"><i class="fa-solid fa-chart-pie"></i>Dashboard 📊</a>
                <a class="nav-link" href="admin-sales-report.php"><i class="fa-solid fa-chart-line"></i>Sales Report 📈</a>
                <a class="nav-link" href="admin-inventory.php"><i class="fa-solid fa-boxes-stacked"></i>Inventory 📦</a>
                <a class="nav-link" href="admin-pos.php"><i class="fa-solid fa-cash-register"></i>Point of Sale 🛒</a>
                <a class="nav-link active" href="admin-reseller-management.php"><i class="fa-solid fa-users"></i>Resellers 👥</a>
                <a class="nav-link" href="admin-account-settings.php"><i class="fa-solid fa-gear"></i>Account Settings ⚙️</a>
            </div>
        </aside>
        <main class="portal-main">
            <div class="topbar">
                <div>
                    <h1>Reseller management</h1>
                    <div class="breadcrumb">Monlei SiPao • CRUD operations for all reseller accounts</div>
                </div>
                <div class="quick-actions">
                    <button class="btn-ghost" onclick="document.getElementById('searchInput').focus()"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
                    <button class="btn-accent" onclick="document.getElementById('newResellerModal').style.display='block'"><i class="fa-solid fa-user-plus"></i> Add reseller</button>
                    <a class="btn-logout" href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
                </div>
            </div>
            <?php if ($flash): ?>
            <div style="margin:16px;padding:12px;border-radius:8px;color:#fff;background-color:<?php echo $flash['type']==='error' ? '#c1121f' : '#06a77d'; ?>;font-weight:700;">
                <?php echo htmlspecialchars($flash['message']); ?>
            </div>
            <?php endif; ?>
            <section class="content-section">
                <div style="margin-bottom:20px;">
                    <input type="text" id="searchInput" placeholder="Search by email, name, or store..." 
                           onkeyup="filterTable()" 
                           style="width:100%;max-width:400px;padding:10px;border:1px solid #ddd;border-radius:4px;font-family:Baloo 2;box-sizing:border-box;">
                </div>
                <div class="table-card">
                    <header>
                        <h2>All reseller accounts (<?php echo $resellers->num_rows ?? 0; ?>)</h2>
                        <span style="font-size:0.9em;color:#666;">Click to view details, edit, or delete</span>
                    </header>
                    <table id="resellersTable">
                        <thead>
                            <tr>
                                <th>Email</th>
                                <th>Store name</th>
                                <th>Phone</th>
                                <th>Total sales</th>
                                <th>Revenue</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($resellers && $resellers->num_rows > 0) {
                                while ($row = $resellers->fetch_assoc()) {
                                    $email = htmlspecialchars($row['email']);
                                    $store = htmlspecialchars($row['store_name'] ?? 'N/A');
                                    $phone = htmlspecialchars($row['phone_number'] ?? 'N/A');
                                    $sales = $row['total_sales'] ?? 0;
                                    $revenue = number_format($row['total_revenue'] ?? 0, 2);
                                    $joined = date('M d, Y', strtotime($row['created_at']));
                                    $reseller_id = $row['id'];
                                    
                                    echo "<tr class='reseller-row' data-search=\"$email $store $phone\">
                                        <td>$email</td>
                                        <td>$store</td>
                                        <td>$phone</td>
                                        <td>$sales</td>
                                        <td>₱$revenue</td>
                                        <td>$joined</td>
                                        <td>
                                            <div class='action-buttons'>
                                                <button class='btn-small' onclick=\"viewReseller($reseller_id)\"><i class='fa-solid fa-eye'></i> View</button>
                                                <button class='btn-small' onclick=\"editReseller($reseller_id)\"><i class='fa-solid fa-pen'></i> Edit</button>
                                                <button class='btn-small danger' onclick=\"if(confirm('Delete this reseller?')) deleteReseller($reseller_id)\"><i class='fa-solid fa-trash'></i> Delete</button>
                                            </div>
                                        </td>
                                    </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='7' style='text-align:center;color:#999;padding:30px;'>No resellers found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <div class="info-banner">
                    <i class="fa-solid fa-lightbulb"></i>
                    <div>
                        <strong>Management tip:</strong> Use the search to quickly find resellers. Click Edit to update profile information or Delete to remove inactive accounts. All changes are logged.
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- View Reseller Modal -->
    <div id="viewModal" style="display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;background-color:rgba(0,0,0,0.5);overflow-y:auto;">
        <div style="background-color:#fce9d4;margin:50px auto;padding:30px;border:1px solid #8c1c2f;width:90%;max-width:600px;border-radius:8px;box-shadow:0 4px 8px rgba(0,0,0,0.2);">
            <h2 style="margin-top:0;color:#8c1c2f;border-bottom:2px solid #f4a523;padding-bottom:10px;display:flex;justify-content:space-between;align-items:center;">
                <span>Reseller details</span>
                <button type="button" onclick="document.getElementById('viewModal').style.display='none'" style="background:none;border:none;font-size:24px;cursor:pointer;color:#8c1c2f;">&times;</button>
            </h2>
            <div id="viewContent" style="display:flex;flex-direction:column;gap:15px;">
                <!-- Content loaded via AJAX -->
            </div>
        </div>
    </div>

    <!-- Edit Reseller Modal -->
    <div id="editModal" style="display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;background-color:rgba(0,0,0,0.5);overflow-y:auto;">
        <div style="background-color:#fce9d4;margin:50px auto;padding:30px;border:1px solid #8c1c2f;width:90%;max-width:600px;border-radius:8px;box-shadow:0 4px 8px rgba(0,0,0,0.2);">
            <h2 style="margin-top:0;color:#8c1c2f;border-bottom:2px solid #f4a523;padding-bottom:10px;display:flex;justify-content:space-between;align-items:center;">
                <span>Edit reseller account</span>
                <button type="button" onclick="document.getElementById('editModal').style.display='none'" style="background:none;border:none;font-size:24px;cursor:pointer;color:#8c1c2f;">&times;</button>
            </h2>
            <form id="editForm" method="POST" action="auth/admin_edit_reseller.php">
                <input type="hidden" name="reseller_id" id="editResellerId">
                
                <div style="margin-bottom:16px;">
                    <label style="display:block;margin-bottom:8px;color:#333;font-weight:600;">Email</label>
                    <input type="email" name="email" id="editEmail" required style="width:100%;padding:10px;border:1px solid #ddd;border-radius:4px;font-family:Baloo 2;box-sizing:border-box;">
                </div>

                <div style="margin-bottom:16px;">
                    <label style="display:block;margin-bottom:8px;color:#333;font-weight:600;">Store name</label>
                    <input type="text" name="store_name" id="editStoreName" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:4px;font-family:Baloo 2;box-sizing:border-box;">
                </div>

                <div style="margin-bottom:16px;">
                    <label style="display:block;margin-bottom:8px;color:#333;font-weight:600;">Address</label>
                    <textarea name="address" id="editAddress" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:4px;font-family:Baloo 2;box-sizing:border-box;min-height:60px;"></textarea>
                </div>

                <div style="margin-bottom:16px;">
                    <label style="display:block;margin-bottom:8px;color:#333;font-weight:600;">Phone number</label>
                    <input type="tel" name="phone_number" id="editPhone" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:4px;font-family:Baloo 2;box-sizing:border-box;">
                </div>

                <div style="margin-bottom:16px;">
                    <label style="display:block;margin-bottom:8px;color:#333;font-weight:600;">Bank name</label>
                    <input type="text" name="bank_name" id="editBank" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:4px;font-family:Baloo 2;box-sizing:border-box;">
                </div>

                <div style="margin-bottom:16px;">
                    <label style="display:block;margin-bottom:8px;color:#333;font-weight:600;">Account number</label>
                    <input type="text" name="account_number" id="editAccount" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:4px;font-family:Baloo 2;box-sizing:border-box;">
                </div>

                <div style="margin-bottom:16px;">
                    <label style="display:block;margin-bottom:8px;color:#333;font-weight:600;">Account holder</label>
                    <input type="text" name="account_holder" id="editAccountHolder" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:4px;font-family:Baloo 2;box-sizing:border-box;">
                </div>

                <div style="display:flex;gap:10px;margin-top:20px;">
                    <button type="submit" class="btn-accent" style="flex:1;">Save changes</button>
                    <button type="button" class="btn-ghost" style="flex:1;cursor:pointer;" onclick="document.getElementById('editModal').style.display='none'">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add New Reseller Modal -->
    <div id="newResellerModal" style="display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;background-color:rgba(0,0,0,0.5);overflow-y:auto;">
        <div style="background-color:#fce9d4;margin:50px auto;padding:30px;border:1px solid #8c1c2f;width:90%;max-width:600px;border-radius:8px;box-shadow:0 4px 8px rgba(0,0,0,0.2);">
            <h2 style="margin-top:0;color:#8c1c2f;border-bottom:2px solid #f4a523;padding-bottom:10px;display:flex;justify-content:space-between;align-items:center;">
                <span>Create new reseller account</span>
                <button type="button" onclick="document.getElementById('newResellerModal').style.display='none'" style="background:none;border:none;font-size:24px;cursor:pointer;color:#8c1c2f;">&times;</button>
            </h2>
            <form method="POST" action="auth/admin_add_reseller.php">
                <div style="margin-bottom:16px;">
                    <label style="display:block;margin-bottom:8px;color:#333;font-weight:600;">Email *</label>
                    <input type="email" name="email" placeholder="reseller@example.com" required style="width:100%;padding:10px;border:1px solid #ddd;border-radius:4px;font-family:Baloo 2;box-sizing:border-box;">
                </div>

                <div style="margin-bottom:16px;">
                    <label style="display:block;margin-bottom:8px;color:#333;font-weight:600;">Username *</label>
                    <input type="text" name="username" placeholder="reseller_username" required pattern="[a-zA-Z0-9_]{3,}" title="3+ alphanumeric characters" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:4px;font-family:Baloo 2;box-sizing:border-box;">
                </div>

                <div style="margin-bottom:16px;">
                    <label style="display:block;margin-bottom:8px;color:#333;font-weight:600;">Store name</label>
                    <input type="text" name="store_name" placeholder="Store name" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:4px;font-family:Baloo 2;box-sizing:border-box;">
                </div>

                <div style="margin-bottom:16px;">
                    <label style="display:block;margin-bottom:8px;color:#333;font-weight:600;">Phone number</label>
                    <input type="tel" name="phone_number" placeholder="+63 917 555 0000" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:4px;font-family:Baloo 2;box-sizing:border-box;">
                </div>

                <p style="font-size:0.85em;color:#666;margin:10px 0;">📌 A temporary password will be generated and the reseller can reset it on first login.</p>

                <div style="display:flex;gap:10px;margin-top:20px;">
                    <button type="submit" class="btn-accent" style="flex:1;">Create account</button>
                    <button type="button" class="btn-ghost" style="flex:1;cursor:pointer;" onclick="document.getElementById('newResellerModal').style.display='none'">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function filterTable() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toLowerCase();
            const rows = document.querySelectorAll('.reseller-row');
            
            rows.forEach(row => {
                const searchText = row.getAttribute('data-search').toLowerCase();
                row.style.display = searchText.includes(filter) ? '' : 'none';
            });
        }

        function viewReseller(id) {
            document.getElementById('viewModal').style.display = 'block';
            document.getElementById('viewContent').innerHTML = '<div style="text-align:center;color:#999;">Loading...</div>';
            
            fetch('auth/admin_get_reseller.php?id=' + id)
                .then(r => r.text())
                .then(html => {
                    document.getElementById('viewContent').innerHTML = html;
                })
                .catch(e => {
                    document.getElementById('viewContent').innerHTML = '<div style="color:#c1121f;">Error loading reseller details</div>';
                });
        }

        function editReseller(id) {
            fetch('auth/admin_get_reseller_data.php?id=' + id)
                .then(r => r.json())
                .then(data => {
                    document.getElementById('editResellerId').value = id;
                    document.getElementById('editEmail').value = data.email || '';
                    document.getElementById('editStoreName').value = data.store_name || '';
                    document.getElementById('editAddress').value = data.address || '';
                    document.getElementById('editPhone').value = data.phone_number || '';
                    document.getElementById('editBank').value = data.bank_name || '';
                    document.getElementById('editAccount').value = data.account_number || '';
                    document.getElementById('editAccountHolder').value = data.account_holder || '';
                    document.getElementById('editModal').style.display = 'block';
                })
                .catch(e => alert('Error loading reseller data'));
        }

        function deleteReseller(id) {
            fetch('auth/admin_delete_reseller.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'reseller_id=' + id
            })
            .then(response => {
                // Auto-refresh the page after deletion
                window.location.reload();
            })
            .catch(error => {
                alert('Error deleting reseller');
                console.error('Delete error:', error);
            });
        }
    </script>
</body>
</html>
