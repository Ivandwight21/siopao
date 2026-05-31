<?php
require 'config.php';
$result = $mysqli->query('DESCRIBE users');
echo "Users table structure:\n";
while($row = $result->fetch_assoc()) {
    echo $row['Field'] . ' - ' . $row['Type'] . "\n";
}
