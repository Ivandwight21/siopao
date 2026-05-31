<?php
require_once __DIR__ . '/config.php';
require_role('admin');
$flash = consume_flash();

// Get current admin info - require_role already validated session
$user_id = $_SESSION['user_id'] ?? ($_SESSION['user']['id'] ?? 0);

$stmt = $mysqli->prepare("SELECT u.id, u.username, u.email, u.created_at, ap.full_name, ap.phone_number, ap.profile_picture, ap.updated_at
                          FROM users u 
                          LEFT JOIN admin_profile ap ON u.id = ap.user_id 
                          WHERE u.id = ?");
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();
    $stmt->close();
    
    // Ensure admin data is properly loaded
    if (!$admin) {
        $admin = ['id' => $user_id, 'username' => 'Admin', 'email' => '', 'created_at' => date('Y-m-d H:i:s'), 'full_name' => '', 'phone_number' => '', 'profile_picture' => ''];
    }
} else {
    $admin = ['id' => $user_id, 'username' => 'Admin', 'email' => '', 'created_at' => date('Y-m-d H:i:s'), 'full_name' => '', 'phone_number' => '', 'profile_picture' => ''];
    error_log("Failed to prepare statement for admin profile: " . $mysqli->error);
}

// Get all admins for role management
$admins_stmt = $mysqli->prepare("SELECT u.id, u.username, u.email, u.created_at, ap.full_name, ap.phone_number 
                                 FROM users u 
                                 LEFT JOIN admin_profile ap ON u.id = ap.user_id 
                                 WHERE u.role = 'admin' 
                                 ORDER BY u.created_at DESC");
if ($admins_stmt) {
    $admins_stmt->execute();
    $all_admins = $admins_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $admins_stmt->close();
} else {
    $all_admins = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Account Settings | SiPao Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/portal.css">
    <style>
        .profile-picture-container {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 20px;
        }
        .profile-picture {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #f4a523;
        }
        .admin-list {
            margin-top: 20px;
        }
        .admin-item {
            padding: 12px;
            background: rgba(255,255,255,0.5);
            border-radius: 8px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .admin-item:hover {
            background: rgba(255,255,255,0.8);
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            overflow-y: auto;
        }
        .modal-content {
            background-color: #fce9d4;
            margin: 50px auto;
            padding: 30px;
            border: 1px solid #8c1c2f;
            width: 90%;
            max-width: 500px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .modal-header {
            margin-top: 0;
            color: #8c1c2f;
            border-bottom: 2px solid #f4a523;
            padding-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .close-modal {
            font-size: 28px;
            font-weight: bold;
            color: #8c1c2f;
            cursor: pointer;
        }
        .close-modal:hover {
            color: #f4a523;
        }
        .form-group {
            margin-bottom: 16px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
        }
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: 'Baloo 2';
            box-sizing: border-box;
        }
        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .form-actions button {
            flex: 1;
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
                <span class="nav-label">Overview</span>
                <a class="nav-link" href="admin-dashboard.php"><i class="fa-solid fa-chart-pie"></i>Dashboard</a>
                <a class="nav-link" href="admin-sales-report.php"><i class="fa-solid fa-chart-line"></i>Sales Report</a>
                <a class="nav-link" href="admin-inventory.php"><i class="fa-solid fa-boxes-stacked"></i>Inventory</a>
                <a class="nav-link" href="admin-pos.php"><i class="fa-solid fa-cash-register"></i>Point of Sale</a>
                <a class="nav-link" href="admin-resellers.php"><i class="fa-solid fa-users"></i>Resellers</a>
                <a class="nav-link active" href="admin-account-settings.php"><i class="fa-solid fa-gear"></i>Account Settings</a>
            </div>
        </aside>
        <main class="portal-main">
            <div class="topbar">
                <div>
                    <h1>Account preferences</h1>
                    <div class="breadcrumb">Monlei SiPao • Manage profile & security</div>
                </div>
                <div class="quick-actions">
                    <a href="https://help.monleisiopao.com" target="_blank" class="btn-ghost"><i class="fa-solid fa-circle-question"></i> Need assistance?</a>
                    <button class="btn-accent" onclick="window.print()"><i class="fa-solid fa-floppy-disk"></i> Save changes</button>
                    <a class="btn-logout" href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
                </div>
            </div>
            <?php if ($flash): ?>
            <div style="margin:16px;padding:12px;border-radius:8px;color:#fff;background-color:<?php echo $flash['type']==='error' ? '#c1121f' : '#06a77d'; ?>;font-weight:700;">
                <?php echo htmlspecialchars($flash['message']); ?>
            </div>
            <?php endif; ?>
            <section class="content-section">
                <div class="info-banner">
                    <i class="fa-solid fa-user-shield"></i>
                    <div>
                        <strong>Security reminder:</strong> Keep your profile information up-to-date. Change your password regularly to maintain account security.
                    </div>
                </div>
                <div class="settings-grid">
                    <!-- Profile Information Card -->
                    <div class="settings-card">
                        <h3><i class="fa-solid fa-id-badge"></i> Profile Information</h3>
                        <div class="profile-picture-container">
                            <img src="<?php echo !empty($admin['profile_picture']) ? htmlspecialchars($admin['profile_picture']) : '123.jpg'; ?>" alt="Profile" class="profile-picture" id="profilePreview">
                            <div>
                                <p><strong>Username:</strong> <?php echo htmlspecialchars($admin['username'] ?? 'Admin'); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($admin['email'] ?? 'Not set'); ?></p>
                            </div>
                        </div>
                        <p><strong>Name:</strong> <?php echo !empty($admin['full_name']) ? htmlspecialchars($admin['full_name']) : 'Not set'; ?></p>
                        <p><strong>Phone:</strong> <?php echo !empty($admin['phone_number']) ? htmlspecialchars($admin['phone_number']) : 'Not set'; ?></p>
                        <p><strong>Member since:</strong> <?php echo isset($admin['created_at']) ? date('F j, Y', strtotime($admin['created_at'])) : 'Unknown'; ?></p>
                        <button class="btn-ghost" onclick="openEditProfileModal()"><i class="fa-solid fa-pen"></i> Edit Profile</button>
                    </div>
                    
                    <!-- Password Controls Card -->
                    <div class="settings-card">
                        <h3><i class="fa-solid fa-lock"></i> Password Controls</h3>
                        <p>Update credentials regularly. Password must contain at least 8 characters.</p>
                        <p><strong>Last changed:</strong> View security settings for details</p>
                        <button class="btn-ghost" onclick="openModal('passModal')"><i class="fa-solid fa-key"></i> Change Password</button>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Change Password Modal -->
    <div id="passModal" class="modal">
        <div class="modal-content">
            <h2 class="modal-header">
                Change Password
                <span class="close-modal" onclick="closeModal('passModal')">&times;</span>
            </h2>
            <form method="POST" action="auth/admin_change_password.php">
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" placeholder="Enter your current password" required>
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" id="newPassword" placeholder="At least 8 characters" required minlength="8">
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" placeholder="Re-enter new password" required>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-accent">Update Password</button>
                    <button type="button" class="btn-ghost" onclick="closeModal('passModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div id="editProfileModal" class="modal">
        <div class="modal-content">
            <h2 class="modal-header">
                Edit Profile Information
                <span class="close-modal" onclick="closeModal('editProfileModal')">&times;</span>
            </h2>
            <form id="editProfileForm" method="POST" action="auth/admin_update_profile.php" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Profile Picture</label>
                    <input type="file" name="profile_picture" accept="image/*" onchange="previewImage(event)">
                    <img id="editProfilePreview" src="<?php echo !empty($admin['profile_picture']) ? htmlspecialchars($admin['profile_picture']) : '123.jpg'; ?>" style="width:80px;height:80px;border-radius:50%;margin-top:10px;">
                </div>
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" id="fullNameInput" value="<?php echo htmlspecialchars($admin['full_name'] ?? ''); ?>" placeholder="Enter your full name" required>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" id="emailInput" value="<?php echo htmlspecialchars($admin['email'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="tel" name="phone_number" id="phoneInput" value="<?php echo htmlspecialchars($admin['phone_number'] ?? ''); ?>" placeholder="+63 917 555 1234">
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-accent" id="saveChangesBtn">Save Changes</button>
                    <button type="button" class="btn-ghost" onclick="closeModal('editProfileModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Admin Modal -->
    <div id="addAdminModal" class="modal" style="display:none;">
        <div class="modal-content">
            <h2 class="modal-header">
                Add Administrator
                <span class="close-modal" onclick="closeModal('addAdminModal')">&times;</span>
            </h2>
            <form method="POST" action="auth/admin_add_admin.php">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="newadmin@monleisiopao.com" required>
                </div>
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" placeholder="Juan Dela Cruz" required>
                </div>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" placeholder="jdelacruz" required pattern="[a-zA-Z0-9_]{3,}" title="3+ alphanumeric characters">
                </div>
                <div class="form-group">
                    <label>Phone Number (Optional)</label>
                    <input type="tel" name="phone_number" placeholder="+63 917 555 1234">
                </div>
                <p style="font-size:0.85em;color:#666;margin:10px 0;">📌 Temporary password will be auto-generated and displayed after creation.</p>
                <div class="form-actions">
                    <button type="submit" class="btn-accent">Create Admin Account</button>
                    <button type="button" class="btn-ghost" onclick="closeModal('addAdminModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function openEditProfileModal() {
            openModal('editProfileModal');
        }

        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function() {
                document.getElementById('editProfilePreview').src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        }

        // Handle edit profile form submission
        document.getElementById('editProfileForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const btn = document.getElementById('saveChangesBtn');
            const form = document.getElementById('editProfileForm');
            
            btn.disabled = true;
            btn.textContent = 'Saving...';
            
            const formData = new FormData(form);
            
            console.log('Submitting profile update...');
            
            fetch('auth/admin_update_profile.php', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(response => {
                console.log('Response status:', response.status);
                // The server redirects on success or error
                // Check if the request completed
                if (response.status === 200 || response.ok || response.type === 'basic') {
                    console.log('Update request processed, redirecting...');
                    setTimeout(() => {
                        window.location.href = 'admin-account-settings.php?t=' + Date.now();
                    }, 800);
                } else {
                    console.log('Response not OK:', response.status);
                    btn.disabled = false;
                    btn.textContent = 'Save Changes';
                    alert('Error: Server returned status ' + response.status);
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                btn.disabled = false;
                btn.textContent = 'Save Changes';
                alert('Network error: ' + error.message);
            });
        });

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html>
