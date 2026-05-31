<?php
require_once __DIR__ . '/config.php';

function table_exists(mysqli $db, string $name): bool {
    $res = $db->query("SHOW TABLES LIKE '" . $db->real_escape_string($name) . "'");
    return $res && $res->num_rows > 0;
}

echo "<pre>Sales records migration starting...\n\n";

if (!table_exists($mysqli, 'sales_records')) {
    echo "Creating sales_records table...\n";
    $sql = "CREATE TABLE sales_records (
      id INT AUTO_INCREMENT PRIMARY KEY,
      user_id INT NOT NULL,
      product_id INT NOT NULL,
      quantity INT NOT NULL,
      amount DECIMAL(10,2) NOT NULL,
      sale_date DATE NOT NULL,
      created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      payment_method VARCHAR(32) DEFAULT 'cash',
      receipt_no VARCHAR(64),
      INDEX (user_id), INDEX (product_id), INDEX (sale_date), INDEX (receipt_no),
      CONSTRAINT fk_sales_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
      CONSTRAINT fk_sales_product FOREIGN KEY (product_id) REFERENCES inventory(id) ON DELETE CASCADE
    ) ENGINE=InnoDB";
    if ($mysqli->query($sql)) {
        echo "✓ Created.\n\n";
    } else {
        echo "✗ Failed: {$mysqli->error}\n";
        exit;
    }
} else {
    echo "Table exists. Checking columns...\n";
    $cols = [];
    $res = $mysqli->query('DESCRIBE sales_records');
    while ($row = $res->fetch_assoc()) { $cols[$row['Field']] = $row; }

    $changes = 0;
    if (!isset($cols['receipt_no'])) {
        echo "Adding column receipt_no...\n";
        if ($mysqli->query("ALTER TABLE sales_records ADD COLUMN receipt_no VARCHAR(64)")) {
            $changes++;
        } else { echo "✗ Error: {$mysqli->error}\n"; }
    } else { echo "- receipt_no OK\n"; }

    if (!isset($cols['payment_method'])) {
        echo "Adding column payment_method...\n";
        if ($mysqli->query("ALTER TABLE sales_records ADD COLUMN payment_method VARCHAR(32) DEFAULT 'cash'")) {
            $changes++;
        } else { echo "✗ Error: {$mysqli->error}\n"; }
    } else { echo "- payment_method OK\n"; }

    if (!isset($cols['created_at'])) {
        echo "Adding column created_at...\n";
        if ($mysqli->query("ALTER TABLE sales_records ADD COLUMN created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP")) {
            $changes++;
        } else { echo "✗ Error: {$mysqli->error}\n"; }
    } else { echo "- created_at OK\n"; }

    if (!isset($cols['sale_date'])) {
        echo "Adding column sale_date...\n";
        if ($mysqli->query("ALTER TABLE sales_records ADD COLUMN sale_date DATE NOT NULL")) {
            $changes++;
        } else { echo "✗ Error: {$mysqli->error}\n"; }
    } else { echo "- sale_date OK\n"; }

    echo "\nColumns check complete. Changes applied: $changes\n\n";

    // Add index on receipt_no if missing
    $idxRes = $mysqli->query("SHOW INDEX FROM sales_records WHERE Key_name='receipt_no'");
    if (!$idxRes || $idxRes->num_rows === 0) {
        echo "Adding index on receipt_no...\n";
        if ($mysqli->query("CREATE INDEX receipt_no ON sales_records (receipt_no)")) {
            echo "✓ Index added.\n";
        } else {
            echo "✗ Index failed (may already exist): {$mysqli->error}\n";
        }
    } else {
        echo "- receipt_no index OK\n";
    }
}

echo "Done.\n";
echo "</pre>";
