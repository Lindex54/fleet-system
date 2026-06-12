<?php
require 'config/database.php';
$pdo = fleetDb();
foreach ($pdo->query("SELECT d.id, d.full_name, d.email, u.id AS user_id, u.username FROM drivers d LEFT JOIN users u ON u.id = d.user_id ORDER BY d.id DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC) as $row) {
    echo implode(' | ', [$row['id'], $row['full_name'], $row['email'] ?? 'NULL', $row['user_id'] ?? 'NULL', $row['username'] ?? 'NULL']), PHP_EOL;
}
