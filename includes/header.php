<?php

if (!isset($pageTitle)) {
    $pageTitle = 'MSU Share & Care';
}

if (!function_exists('app_url')) {
    require_once __DIR__ . '/auth.php';
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#437B98">
    <meta name="color-scheme" content="light">
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?> | MSU Share &amp; Care</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@400;500;600;700&family=Prompt:wght@500;600;700&display=swap">
    <link rel="icon" type="image/svg+xml" href="<?php echo app_url('assets/favicon.svg'); ?>">
    <link rel="stylesheet" href="<?php echo app_url('assets/css/style.css'); ?>">
</head>
<body>
<?php require __DIR__ . '/nav.php'; ?>
<main class="container">
