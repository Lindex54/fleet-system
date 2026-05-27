<?php
// Shared database connection for the Fleet System backend.
//
// The database name is fleet_system, and the expected tables are the ones from
// fleet_system_relational.sql.

declare(strict_types=1);

$dbHost = getenv('FLEET_DB_HOST') ?: '127.0.0.1';
$dbPort = getenv('FLEET_DB_PORT') ?: '3306';
$dbName = getenv('FLEET_DB_NAME') ?: 'fleet_system';
$dbUser = getenv('FLEET_DB_USER') ?: 'root';
$dbPass = getenv('FLEET_DB_PASS') ?: '';

$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
    $dbHost,
    $dbPort,
    $dbName
);

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    die('Database connection failed. Please confirm MySQL is running and the fleet_system database exists.');
}

function fleetDb(): PDO
{
    global $pdo;

    return $pdo;
}
