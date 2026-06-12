<?php
require 'config/database.php';
$pdo = fleetDb();
try {
    $rows = $pdo->query("SELECT id, subject, message_type, created_at FROM communications ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    echo 'communications_ok', PHP_EOL;
    foreach ($rows as $row) {
        echo implode(' | ', $row), PHP_EOL;
    }
} catch (Throwable $e) {
    echo 'communications_error: ', $e->getMessage(), PHP_EOL;
}
try {
    $rows = $pdo->query("SELECT id, communication_id, driver_id, user_id, recipient_email, delivery_status FROM communication_recipients ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    echo 'recipients_ok', PHP_EOL;
    foreach ($rows as $row) {
        echo implode(' | ', $row), PHP_EOL;
    }
} catch (Throwable $e) {
    echo 'recipients_error: ', $e->getMessage(), PHP_EOL;
}
