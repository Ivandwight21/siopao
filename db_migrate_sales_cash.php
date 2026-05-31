<?php
require_once __DIR__ . '/config.php';

echo "Checking sales_records for cash columns...\n";

$needCashReceived = true;
$needCashChange = true;

$res = $mysqli->query("SHOW COLUMNS FROM sales_records LIKE 'cash_received'");
if ($res && $res->num_rows > 0) { $needCashReceived = false; }

$res = $mysqli->query("SHOW COLUMNS FROM sales_records LIKE 'cash_change'");
if ($res && $res->num_rows > 0) { $needCashChange = false; }

$changes = 0;
if ($needCashReceived) {
  echo "Adding column cash_received...\n";
  if (!$mysqli->query("ALTER TABLE sales_records ADD COLUMN cash_received DECIMAL(10,2) NULL AFTER receipt_no")) {
    echo "Failed to add cash_received: " . $mysqli->error . "\n";
    exit(1);
  }
  $changes++;
}
if ($needCashChange) {
  echo "Adding column cash_change...\n";
  if (!$mysqli->query("ALTER TABLE sales_records ADD COLUMN cash_change DECIMAL(10,2) NULL AFTER cash_received")) {
    echo "Failed to add cash_change: " . $mysqli->error . "\n";
    exit(1);
  }
  $changes++;
}

echo "Columns check complete. Changes applied: $changes\n";
?>
