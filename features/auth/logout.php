<?php
    session_start();

    // NOTE: Удаление данных сессии
    $_SESSION = array();
    session_unset();
    session_destroy();

    // NOTE: Удаление cookis авторизации
    if (isset($_COOKIE['user_logged_in'])) {
        setcookie('user_logged_in', '', time() - 3600, '/');
    }

    // NOTE: Перенаправление на страницу входа
    header("Location: /../../login.php");
    exit();
?>