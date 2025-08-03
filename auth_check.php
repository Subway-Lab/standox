<?php
// Проверка авторизации пользователя

session_start(); // Начинаем сессию

// Проверка времени, если наступила полночь (00:00), обнуляем cookie
$current_time = time();
$midnight = strtotime('tomorrow'); // Время 00:00 следующего дня
$seconds_until_midnight = $midnight - $current_time; // Сколько секунд до полуночи

// Если до полуночи осталось меньше секунды, удаляем cookie и очищаем сессию
if ($seconds_until_midnight <= 1) {
    // Удаляем cookie (если оно существует)
    if (isset($_COOKIE['user_logged_in'])) {
        setcookie('user_logged_in', '', time() - 3600, '/'); // Удаляем cookie, установив время в прошлом
    }
    
    // Удаляем сессионные данные
    session_unset();
    session_destroy();

    // Перенаправляем на страницу логина
    header("Location: features/auth/login.php");
    exit();
}

// Проверяем, залогинен ли пользователь
if (!isset($_SESSION['user_id'])) {
    // Если нет, перенаправляем на страницу логина
    header("Location: features/auth/login.php");
    exit();
}
?>