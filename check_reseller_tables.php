<?php
require_once __DIR__ . '/config.php';

echo "=== RESELLER_INCENTIVES TABLE STRUCTURE ===\n";
$result = $mysqli->query("DESCRIBE reseller_incentives");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
} else {
    echo "Error: " . $mysqli->error . "\n";
}

echo "\n=== SAMPLE DATA ===\n";
$result = $mysqli->query("SELECT * FROM reseller_incentives LIMIT 3");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo "No data\n";
}

echo "\n=== STOCK_ORDERS TABLE STRUCTURE ===\n";
$result = $mysqli->query("DESCRIBE stock_orders");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
} else {
    echo "Error: " . $mysqli->error . "\n";
}

echo "\n=== SAMPLE STOCK_ORDERS DATA ===\n";
$result = $mysqli->query("SELECT * FROM stock_orders LIMIT 3");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo "No data\n";
}
?>
