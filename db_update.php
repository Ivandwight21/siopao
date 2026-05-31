<?php
require_once __DIR__ . '/config.php';

// Update inventory table to add new columns
$columns_to_add = [
    'unit' => "VARCHAR(50)",
    'supplier' => "VARCHAR(190)",
    'price_per_unit' => "DECIMAL(10,2)",
    'image_path' => "VARCHAR(255)",
    'created_by' => "INT"
];

echo "<pre>";
echo "Starting database updates...\n\n";

// Get existing columns
$result = $mysqli->query("DESCRIBE inventory");
$existing_columns = [];
while ($row = $result->fetch_assoc()) {
    $existing_columns[] = $row['Field'];
}

foreach ($columns_to_add as $col_name => $col_type) {
    if (in_array($col_name, $existing_columns)) {
        echo "✓ Column '$col_name' already exists (OK)\n\n";
    } else {
        $query = "ALTER TABLE inventory ADD COLUMN $col_name $col_type";
        echo "Adding column: $col_name ($col_type)\n";
        if ($mysqli->query($query)) {
            echo "✓ Success\n\n";
        } else {
            echo "✗ Error: " . $mysqli->error . "\n\n";
        }
    }
}

echo "Database updates completed!\n";
echo "</pre>";
echo "<a href='admin-inventory.php'>Back to Inventory</a>";
