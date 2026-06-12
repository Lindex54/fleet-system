<?php
require 'config/database.php';
require 'includes/driver-login-verification.php';
$pdo = fleetDb();
$stmt = $pdo->query("SELECT d.id AS driver_id, d.user_id, d.full_name, d.email, u.name, u.id AS uid FROM drivers d INNER JOIN users u ON u.id = d.user_id WHERE d.full_name = 'Simon MALINDE' LIMIT 1");
$driver = $stmt->fetch(PDO::FETCH_ASSOC);
var_export($driver);
if ($driver) {
    driverLoginVerificationSendWelcomeNotification($pdo, [
        'driver_id' => (int) $driver['driver_id'],
        'user_id' => (int) $driver['user_id'],
        'email' => (string) $driver['email'],
        'name' => (string) $driver['name'],
        'full_name' => (string) $driver['full_name'],
    ]);
    echo PHP_EOL, 'DONE', PHP_EOL;
}
