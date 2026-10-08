<?php

require_once __DIR__ . '/db.php';

error_reporting(E_ALL);
define('APP_DEBUG', getenv('APP_DEBUG') === 'true');
ini_set('display_errors', APP_DEBUG ? '1' : '0');

ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
