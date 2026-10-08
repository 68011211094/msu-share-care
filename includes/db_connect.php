<?php

require_once dirname(__DIR__) . '/config/db.php';

function db_connect()
{
    static $connection = null;

    if ($connection !== null) {
        return $connection;
    }

    $config = get_db_config();
    $dsn = 'mysql:host=' . $config['host']
        . ';port=' . $config['port']
        . ';dbname=' . $config['name']
        . ';charset=utf8mb4';

    try {
        $connection = new PDO($dsn, $config['user'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $exception) {
        error_log('Database connection failed: ' . $exception->getMessage());
        http_response_code(500);
        die('ไม่สามารถเชื่อมต่อฐานข้อมูลได้');
    }

    return $connection;
}
