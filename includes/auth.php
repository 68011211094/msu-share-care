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
        show_error_page(403, 'ไม่มีสิทธิ์', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
    }
}

function show_error_page($httpCode, $title, $message)
{
    http_response_code($httpCode);
    $pageTitle = $title;

    require __DIR__ . '/header.php';
    echo '    <h1>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . "</h1>\n";
    echo '    <div class="page-note">'
        . htmlspecialchars($message, ENT_QUOTES, 'UTF-8')
        . "</div>\n";
    require __DIR__ . '/footer.php';
    exit;
}

function require_owned_item($itemId)
{
    $statement = db_connect()->prepare('SELECT * FROM items WHERE id = ?');
    $statement->execute([$itemId]);
    $item = $statement->fetch();

    if ($item === false) {
        show_error_page(404, 'ไม่พบประกาศ', 'ประกาศนี้อาจถูกลบไปแล้ว');
    }

    $currentUser = current_user();

    if ((int) $item['owner_id'] !== (int) $currentUser['id']) {
        show_error_page(403, 'ไม่มีสิทธิ์', 'คุณไม่มีสิทธิ์แก้ไขประกาศนี้');
    }

    return $item;
}
