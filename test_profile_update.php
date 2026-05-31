<?php
require_once __DIR__ . '/config.php';

// Check database structure
echo "=== USERS TABLE ===\n";
$result = $mysqli->query("DESCRIBE users");
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . " (" . $row['Null'] . ")\n";
}

echo "\n=== ADMIN_PROFILE TABLE ===\n";
$result = $mysqli->query("DESCRIBE admin_profile");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . " (" . $row['Null'] . ")\n";
    }
} else {
    echo "admin_profile table does not exist\n";
    echo "Error: " . $mysqli->error . "\n";
}

// Check current session user
echo "\n=== SESSION INFO ===\n";
session_start();
if (isset($_SESSION['user_id'])) {
    echo "User ID: " . $_SESSION['user_id'] . "\n";
    
    // Get user data
    $stmt = $mysqli->prepare("SELECT u.id, u.username, u.email, ap.full_name, ap.phone_number FROM users u LEFT JOIN admin_profile ap ON u.id = ap.user_id WHERE u.id = ?");
    $user_id = $_SESSION['user_id'];
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    echo "Username: " . $user['username'] . "\n";
    echo "Email: " . $user['email'] . "\n";
    echo "Full Name: " . ($user['full_name'] ?? 'NOT SET') . "\n";
    echo "Phone: " . ($user['phone_number'] ?? 'NOT SET') . "\n";
} else {
    echo "No session user\n";
}
?>
