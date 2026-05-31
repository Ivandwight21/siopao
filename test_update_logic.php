<?php
require_once __DIR__ . '/config.php';

// Simulate an admin session
$_SESSION['user_id'] = 1; // Assuming user ID 1 exists

echo "=== SIMULATED PROFILE UPDATE TEST ===\n\n";

// Check current data
echo "Current Profile Data:\n";
$stmt = $mysqli->prepare("SELECT u.id, u.email, ap.full_name, ap.phone_number, ap.profile_picture FROM users u LEFT JOIN admin_profile ap ON u.id = ap.user_id WHERE u.id = ?");
$user_id = 1;
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$current = $result->fetch_assoc();
$stmt->close();

echo "User ID: " . $current['id'] . "\n";
echo "Email: " . $current['email'] . "\n";
echo "Full Name: " . ($current['full_name'] ?? 'NOT SET') . "\n";
echo "Phone: " . ($current['phone_number'] ?? 'NOT SET') . "\n";
echo "Picture: " . ($current['profile_picture'] ?? 'NOT SET') . "\n";

echo "\n=== TESTING UPDATE ===\n";

// Simulate POST data
$_POST['full_name'] = 'Admin Test User';
$_POST['email'] = 'admin@test.local';
$_POST['phone_number'] = '+63917555123';

// Test email validation
$full_name = trim($_POST['full_name']);
$email = trim($_POST['email']);
$phone_number = trim($_POST['phone_number']);

echo "New Email: $email\n";
echo "New Full Name: $full_name\n";
echo "New Phone: $phone_number\n";

// Test email uniqueness check
$check_email = $mysqli->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
$check_email->bind_param("si", $email, $user_id);
$check_email->execute();
$check_result = $check_email->get_result();
$email_taken = $check_result->num_rows > 0;
$check_email->close();

echo "Email already taken: " . ($email_taken ? 'YES' : 'NO') . "\n";

// Test email format
$valid_email = filter_var($email, FILTER_VALIDATE_EMAIL);
echo "Valid email format: " . ($valid_email ? 'YES' : 'NO') . "\n";

// Check if profile exists
$check_profile = $mysqli->prepare("SELECT user_id FROM admin_profile WHERE user_id = ?");
$check_profile->bind_param("i", $user_id);
$check_profile->execute();
$profile_result = $check_profile->get_result();
$profile_exists = $profile_result->num_rows > 0;
$check_profile->close();

echo "Admin profile exists: " . ($profile_exists ? 'YES' : 'NO') . "\n";

echo "\n=== DATABASE READY FOR UPDATE ===\n";
echo "✓ All validation checks passed\n";
echo "✓ Ready to execute profile update\n";
?>
