<?php

require_once __DIR__ . '/db_connect.php';

function current_user()
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    static $currentUser = null;
    static $loaded = false;

    if ($loaded) {
        return $currentUser;
    }

    $loaded = true;

    $statement = db_connect()->prepare(
        'SELECT id, full_name, email, role, contact_info FROM users WHERE id = ?'
    );
    $statement->execute([(int) $_SESSION['user_id']]);
    $user = $statement->fetch();

    if ($user === false) {
        unset($_SESSION['user_id']);
        return null;
    }

    $currentUser = $user;

    return $currentUser;
}

function is_logged_in()
{
    return current_user() !== null;
}

function require_login()
{
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function require_admin()
{
    require_login();

    $user = current_user();

    if ($user['role'] !== 'admin') {
        http_response_code(403);
        die('คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
    }
}
