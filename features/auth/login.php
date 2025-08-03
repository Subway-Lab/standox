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
    $mysqli = new mysqli('g8r9w9tmspbwmsyo.cbetxkdyhwsb.us-east-1.rds.amazonaws.com', 'q1i28z5zzuyro11l', 'kwdvun8ff1f8m6fs', 'vtjb3fkssehwjx62');

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
    
    <?php
        $loginCss = 'login.css';
        include __DIR__ . '/../../shared/head.php';
    ?>

    <body>

        <div class="left_block">
            <div class="logo">
                <img src="https://www.standox.pro/files/white_logo.svg" alt="STANDOX logo">
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
            var errorMessage = <?php echo json_encode($error_message); ?>;
        </script>
        <script src="login.js"></script>

    </body>
</html>
