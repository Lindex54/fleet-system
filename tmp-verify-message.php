<?php
require 'config/database.php';
$pdo = fleetDb();
$sql = "SELECT c.subject, cr.recipient_email, cr.driver_id, cr.delivery_status FROM communication_recipients cr INNER JOIN communications c ON c.id = cr.communication_id WHERE cr.driver_id = 11 ORDER BY c.id DESC";
foreach ($pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) as $row) {
    echo implode(' | ', $row), PHP_EOL;
}
