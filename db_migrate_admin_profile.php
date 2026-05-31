<?php
require_once __DIR__ . '/config.php';

echo "=== Admin Profile Table Migration ===\n\n";

// Check if admin_profile table exists
$check_table = $mysqli->query("SHOW TABLES LIKE 'admin_profile'");
$table_exists = $check_table->num_rows > 0;

if (!$table_exists) {
    echo "Creating admin_profile table...\n";
    $create_table = "CREATE TABLE admin_profile (
        user_id INT PRIMARY KEY,
        full_name VARCHAR(255) NOT NULL,
        phone_number VARCHAR(20),
        profile_picture VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($mysqli->query($create_table)) {
        echo "✓ admin_profile table created successfully\n";
    } else {
        echo "✗ Error creating admin_profile table: " . $mysqli->error . "\n";
        exit(1);
    }
} else {
    echo "✓ admin_profile table already exists\n";
    
    // Check and add missing columns
    $columns = ['full_name', 'phone_number', 'profile_picture', 'created_at', 'updated_at'];
    $result = $mysqli->query("DESCRIBE admin_profile");
    $existing_columns = [];
    while ($row = $result->fetch_assoc()) {
        $existing_columns[] = $row['Field'];
    }
    
    if (!in_array('full_name', $existing_columns)) {
        echo "Adding full_name column...\n";
        $mysqli->query("ALTER TABLE admin_profile ADD COLUMN full_name VARCHAR(255) NOT NULL AFTER user_id");
        echo "✓ full_name column added\n";
    }
    
    if (!in_array('phone_number', $existing_columns)) {
        echo "Adding phone_number column...\n";
        $mysqli->query("ALTER TABLE admin_profile ADD COLUMN phone_number VARCHAR(20)");
        echo "✓ phone_number column added\n";
    }
    
    if (!in_array('profile_picture', $existing_columns)) {
        echo "Adding profile_picture column...\n";
        $mysqli->query("ALTER TABLE admin_profile ADD COLUMN profile_picture VARCHAR(255)");
        echo "✓ profile_picture column added\n";
    }
    
    if (!in_array('created_at', $existing_columns)) {
        echo "Adding created_at column...\n";
        $mysqli->query("ALTER TABLE admin_profile ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
        echo "✓ created_at column added\n";
    }
    
    if (!in_array('updated_at', $existing_columns)) {
        echo "Adding updated_at column...\n";
        $mysqli->query("ALTER TABLE admin_profile ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
        echo "✓ updated_at column added\n";
    }
}

// Create profiles directory if it doesn't exist
$profiles_dir = __DIR__ . '/assets/profiles';
if (!is_dir($profiles_dir)) {
    mkdir($profiles_dir, 0755, true);
    echo "\n✓ Created assets/profiles directory\n";
} else {
    echo "\n✓ assets/profiles directory exists\n";
}

echo "\n=== Migration Complete ===\n";
echo "Admin account settings page is ready to use!\n";
