<?php
require 'config/database.php';
foreach (['communications','communication_recipients'] as $table) {
    echo '[' . $table . ']' . PHP_EOL;
    try {
        foreach (fleetDb()->query('DESCRIBE ' . $table)->fetchAll() as $row) {
            echo $row['Field'], '|', $row['Type'], PHP_EOL;
        }
    } catch (Throwable $e) {
        echo 'MISSING', PHP_EOL;
    }
}
