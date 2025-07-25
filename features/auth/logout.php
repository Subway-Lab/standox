<?php
// logout.php
session_start();

// Уничтожаем все данные сессии
$_SESSION = array();
session_unset();
session_destroy();

// Удаляем куки авторизации
if (isset($_COOKIE['user_logged_in'])) {
    setcookie('user_logged_in', '', time() - 3600, '/');
}

// Перенаправляем на страницу входа
header("Location: login.php");
exit();
?>