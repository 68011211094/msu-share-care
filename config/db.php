<?php

$ENV = [];

function load_env_file($envFilePath)
{
    global $ENV;

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

        if ($key !== '') {
            $ENV[$key] = $value;
            putenv($key . '=' . $value);
            $_ENV[$key] = $value;
        }
    }
}

load_env_file(dirname(__DIR__) . '/.env');

function env_value($key, $default)
{
    global $ENV;

    $serverValue = getenv($key);

    if ($serverValue !== false && $serverValue !== '') {
        return $serverValue;
    }

    if (isset($ENV[$key]) && $ENV[$key] !== '') {
        return $ENV[$key];
    }

    return $default;
}

function get_db_config()
{
    return [
        'host' => env_value('DB_HOST', '127.0.0.1'),
        'port' => env_value('DB_PORT', '3306'),
        'name' => env_value('DB_NAME', 'msu_share_care'),
        'user' => env_value('DB_USER', 'root'),
        'password' => env_value('DB_PASS', ''),
    ];
}
