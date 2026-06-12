<?php
require 'config/database.php';
$pdo = fleetDb();
$sql = "SELECT c.id, c.subject, c.message_type, c.created_at, cr.driver_id, cr.user_id, cr.recipient_name, cr.recipient_email, cr.delivery_status, cr.sent_at
        FROM communication_recipients cr
        INNER JOIN communications c ON c.id = cr.communication_id
        ORDER BY c.id DESC
        LIMIT 20";
foreach ($pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) as $row) {
    echo implode(' | ', [
        $row['id'],
        $row['subject'],
        $row['message_type'],
        $row['driver_id'] ?? 'NULL',
        $row['user_id'] ?? 'NULL',
        $row['recipient_name'] ?? 'NULL',
        $row['recipient_email'],
        $row['delivery_status'],
        $row['sent_at'] ?? 'NULL',
    ]), PHP_EOL;
}
