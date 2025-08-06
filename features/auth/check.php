<?php // NOTE: Файл проверки авторизации пользователя
    set_time_limit(600); // NOTE: Увиличение времени запроса до 10 минут

session_start(); // NOTE: Стартуем сессию

// NOTE: Проверка времени, если наступила полночь (00:00), обнуляем cookie
$current_time = time();
$midnight = strtotime('tomorrow'); // Время 00:00 следующего дня
$seconds_until_midnight = $midnight - $current_time; // Сколько секунд до полуночи

// NOTE:Если до полуночи осталось меньше секунды, удаляем cookie и очищаем сессию
if ($seconds_until_midnight <= 1) {
    // NOTE:Удаляем cookie (если оно существует)
    if (isset($_COOKIE['user_logged_in'])) {
        setcookie('user_logged_in', '', time() - 3600, '/'); // NOTE: Удаляем cookie, установив время в прошлом
    }
    
    // NOTE: Удаляем сессионные данные
    session_unset();
    session_destroy();

    // NOTE: Перенаправляем на страницу логина
    header("Location: features/auth/login.php");
    exit();
}

// NOTE: Проверяем, залогинен ли пользователь
if (!isset($_SESSION['user_id'])) {
    // NOTE: Если нет, перенаправляем на страницу логина
    header("Location: features/auth/login.php");
    exit();
}
?>