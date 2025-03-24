<?php
session_start(); // NOTE: Стартуем сессию

// NOTE: Если пользователь залогинен, перенаправляем его на главную
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// NOTE: Переменные для ошибки
$error = ''; // NOTE: Сообщение об ошибке
$error_message = ''; // NOTE: Текст ошибки
$error_class = ''; // NOTE: Класс ошибки

// NOTE: Обработка формы логина
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // NOTE: Строки отвечающие за подключение к базе данных
    $mysqli = new mysqli('localhost', 'root', '', 'sto_orders'); // Используй свои данные для подключения

    // NOTE: Проверка на ошибки подключения
    if ($mysqli->connect_error) {
        die("Ошибка подключения: " . $mysqli->connect_error);
    }

    // NOTE: Полуение данные из формы
    $login = $_POST['login'];
    $password = $_POST['password'];

    // NOTE: Подготовленный запрос для защиты от SQL инъекций
    $stmt = $mysqli->prepare("SELECT id, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $login); // NOTE: Привязываем параметр
    $stmt->execute();
    $stmt->store_result();

    // NOTE: В случае если пользователь найден
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $hashed_password);
        $stmt->fetch();

        // NOTE: Сверяем введенный пароль с хешированным паролем из базы данных
        if (password_verify($password, $hashed_password)) {
            // NOTE: Успешный вход, сохраняем user_id в сессию
            $_SESSION['user_id'] = $user_id;
            header("Location: index.php"); // NOTE: Перенаправляем на главную страницу
            exit();
        } else {
            // NOTE: В случае если неверный логин или пароль, выводим сообщение об ошибке
            $error_message = "Неверный логин или пароль!";
            $error_class = 'error'; // NOTE: Примение класса для ошибки
        }
    } else {
        // NOTE: В случае если пользователь не найден, показываем ошибку
        $error_message = "Пользователь не найден!";
        $error_class = 'error'; // Применяем класс для ошибки
    }

    // NOTE: Закрытие сесси
    $stmt->close();
    $mysqli->close();
}
?>

<!DOCTYPE HTML>
<html lang="ru">
    <head>
        <meta charset="utf-8">
        <meta name="keywords" content="key words">
        <meta name="description" content="description of the page SEO">
        <title> STANDOX </title>
        <link rel="icon" href="filles/favicon.ico" type="image/x-icon">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.11.5/gsap.min.js"></script>
        <link rel="stylesheet" type="text/css" href="style.css">
        <link rel="stylesheet" type="text/css" href="login.css">
    </head>
    <body>

        <div class="left_block">
            <div class="logo">
                <img src="filles/white_logo.svg" alt="STANDOX logo">
            </div>
            <div class="address">
                СТО "STANDOX" <br>
                web-site: www.standox.chita.ru <br>
                672039, г. Чита, ул. Верхоленская 51 <br>
                тел: 8 914 472-10-10, 8 924 472-30-30, email: lider00@list.ru
            </div>
        </div>

        <div class="right_block">
            <div class="form">
                <h2> Добрый день! </h2>
                <p> Пожалуйста, укажите свой логин и пароль чтобы войти в систему </p>
                <form method="POST" action="login.php">
                    <label for="login" class="sr-only"> Логин </label>
                    <input class="user_input" id="login" type="text" name="login" placeholder="Login" title="Введите логин" required>
                    <label for="password" class="sr-only"> Пароль </label>
                    <input class="user_input" id="password" type="password" name="password" placeholder="Password" title="Введите пароль" required>
                    <button type="submit"> ВОЙТИ </button>
                </form>
            </div>
        </div>

        <script>
            // NOTE: Проверка на ошибки
            window.onload = function() {
                var errorMessage = '<?php echo $error_message; ?>';
                if (errorMessage) {
                    // NOTE: Изменяем placeholder
                    document.getElementById('login').placeholder = 'Неверный логин или пароль';
                    document.getElementById('login').classList.add('error');
                    
                    // NOTE: Добавляем класс ошибки
                    document.getElementById('password').classList.add('error');

                    // NOTE: Выводим сообщение об ошибке
                    alert(errorMessage); // NOTE: Здесь можно использовать любое уведомление, например, вывод в span или div
                }
            };
        </script>
    </body>
</html>
