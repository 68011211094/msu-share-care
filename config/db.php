<?php

function load_env_file($envFilePath)
{
    if (!is_file($envFilePath)) {
        return;
    }

    $lines = file($envFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }

        $separatorPosition = strpos($line, '=');

        if ($separatorPosition === false) {
            continue;
        }

        $key = trim(substr($line, 0, $separatorPosition));
        $value = trim(substr($line, $separatorPosition + 1));

        if ($key !== '' && getenv($key) === false) {
            putenv($key . '=' . $value);
            $_ENV[$key] = $value;
        }
    }
}

load_env_file(dirname(__DIR__) . '/.env');

function get_db_config()
{
    $dbPassword = getenv('DB_PASS');

    return [
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'port' => getenv('DB_PORT') ?: '3306',
        'name' => getenv('DB_NAME') ?: 'msu_share_care',
        'user' => getenv('DB_USER') ?: 'root',
        'password' => $dbPassword === false ? '' : $dbPassword,
    ];
}
