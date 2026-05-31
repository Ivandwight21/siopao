<?php
require_once __DIR__ . '/config.php';

echo "=== DATABASE TABLES ===\n";
$result = $mysqli->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'monleisiopao'");
while ($row = $result->fetch_assoc()) {
    echo $row['TABLE_NAME'] . "\n";
}

echo "\n=== SALES_RECORDS TABLE STRUCTURE ===\n";
$result = $mysqli->query("DESCRIBE sales_records");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
} else {
    echo "Table does not exist\n";
}

echo "\n=== SAMPLE SALES DATA ===\n";
$result = $mysqli->query("SELECT * FROM sales_records LIMIT 5");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo "No data\n";
}

echo "\n=== ORDERS/TRANSACTIONS TABLES ===\n";
$result = $mysqli->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'monleisiopao' AND TABLE_NAME LIKE '%order%' OR TABLE_NAME LIKE '%transaction%'");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo $row['TABLE_NAME'] . "\n";
    }
} else {
    echo "No order/transaction tables found\n";
}
?>
