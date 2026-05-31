<?php
// Run this script via scheduler to email low-stock alerts.
require_once __DIR__ . '/config.php';

$sql = "
SELECT i.id, i.name, i.qty, i.reorder_level
FROM inventory i
WHERE i.qty <= i.reorder_level
  AND NOT EXISTS (
    SELECT 1 FROM low_stock_notifications n
    WHERE n.inventory_id = i.id AND n.sent_at >= (NOW() - INTERVAL 12 HOUR)
)
";
$res = $mysqli->query($sql);

while ($row = $res->fetch_assoc()) {
    try {
        $mail = mailer();
        $mail->addAddress(getenv('ALERT_TO') ?: $mail->Username);
        $mail->Subject = 'Low stock: ' . $row['name'];
        $mail->Body = $row['name'] . ' is low. Qty: ' . $row['qty'] . ' (reorder level ' . $row['reorder_level'] . ').';
        $mail->send();

        $log = $mysqli->prepare('INSERT INTO low_stock_notifications (inventory_id, qty_at_alert) VALUES (?, ?)');
        $log->bind_param('ii', $row['id'], $row['qty']);
        $log->execute();
        $log->close();
    } catch (Throwable $e) {
        // In production, log the error.
    }
}

echo "Low-stock check complete.";
