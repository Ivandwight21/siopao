<?php
// Seed sample data (admin/reseller users and inventory)
require_once __DIR__ . '/config.php';

// Ensure schema exists
$schemaSql = file_get_contents(__DIR__ . '/schema.sql');
$mysqli->multi_query($schemaSql);
while ($mysqli->more_results() && $mysqli->next_result()) { /* flush */ }

$adminEmail = 'admin@example.com';
$adminUser  = 'admin';
$adminPass  = hash_password('Admin123!');

$resellerEmail = 'reseller@example.com';
$resellerUser  = 'reseller';
$resellerPass  = hash_password('Reseller123!');

// Upsert users
$userStmt = $mysqli->prepare('INSERT INTO users (email, username, password_hash, role) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE password_hash=VALUES(password_hash), role=VALUES(role)');
$userStmt->bind_param('ssss', $adminEmail, $adminUser, $adminPass, $roleAdmin);
$roleAdmin = 'admin';
$userStmt->execute();

$userStmt->bind_param('ssss', $resellerEmail, $resellerUser, $resellerPass, $roleReseller);
$roleReseller = 'reseller';
$userStmt->execute();
$userStmt->close();

// Seed inventory
$inventory = [
    ['Classic Siopao (Pork)', 'SIO-CL-PORK', 120, 40],
    ['Asado Siopao', 'SIO-ASADO', 60, 25],
    ['Bola-Bola Siopao', 'SIO-BB', 45, 20],
    ['Veggie Siopao', 'SIO-VEG', 18, 15],
    ['Milk Tea Cups', 'MT-CUPS', 250, 80]
];

$invStmt = $mysqli->prepare('INSERT INTO inventory (name, sku, qty, reorder_level) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE qty=VALUES(qty), reorder_level=VALUES(reorder_level)');
foreach ($inventory as [$name, $sku, $qty, $reorder]) {
    $invStmt->bind_param('ssii', $name, $sku, $qty, $reorder);
    $invStmt->execute();
}
$invStmt->close();

echo "Seed complete.\nAdmin login: {$adminEmail} / Admin123!\nReseller login: {$resellerEmail} / Reseller123!\n";
