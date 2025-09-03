<?php
    session_start();

    $firstSegment = explode('/', trim($_SERVER['SCRIPT_NAME'], '/'))[0] ?? '';
    $basePath = $firstSegment ? '/' . $firstSegment : '';

    // NOTE: Удаление данных сессии
    $_SESSION = array();
    session_unset();
    session_destroy();

    // NOTE: Удаление cookis авторизации
    if (isset($_COOKIE['user_logged_in'])) {
        setcookie('user_logged_in', '', time() - 3600, '/');
    }

    // NOTE: Перенаправление на страницу входа
    header('Location: ' . $basePath . '/features/auth/login.php');
    exit();
?>