<?php
    require_once __DIR__ . '/check.php';   // NOTE: Проверка авторизации пользователя

    include __DIR__ . '/../../shared/head.php';
?>

<?php

// FIXME: Включение отладки (Убрать в продакшине)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// NOTE: Подключение к базе данных
$servername = "g8r9w9tmspbwmsyo.cbetxkdyhwsb.us-east-1.rds.amazonaws.com"; // NOTE: Хост базы данных на Heroku
$username   = "q1i28z5zzuyro11l"; // NOTE: Имя пользователя базы данных
$password   = "kwdvun8ff1f8m6fs"; // NOTE: Пароль базы данных
$dbname     = "vtjb3fkssehwjx62"; // NOTE: Имя базы данных

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

// NOTE: Обработка отправки формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // NOTE: Получение данные из формы
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($username) || empty($password) || empty($confirm_password)) {
        $error = "Все поля обязательны для заполнения.";
    } elseif ($password !== $confirm_password) {
        $error = "Пароли не совпадают.";
    } else {
        // NOTE: Проверка наличия пользователь с таким именем
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = "Пользователь с таким именем уже существует.";
        } else {
            // NOTE: Шифруем пароль если имя пользователя свободно
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // NOTE: Сохранение нового пользователя в таблицу
            $stmt_insert = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt_insert->bind_param("ss", $username, $hashed_password);
            
            if ($stmt_insert->execute()) {
                // NOTE: Перенаправление пользователя на страницу создания заказа
                header('Location: ' . $basePath . '/features/auth/login.php');
                exit();
            } else {
                $error = "Ошибка регистрации: " . $stmt_insert->error;
            }
            $stmt_insert->close();
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
    <body>
        <h1> Регистрация сотрудника СТО </h1>
        
        <?php if (isset($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="post" action="registration.php">
            <label for="username"> Введите имя пользователя: </label>
            <input type="text" id="username" name="username" required>
            
            <label for="password"> Укажите пароль: </label>
            <input type="password" id="password" name="password" required>
            
            <label for="confirm_password"> Подтвердите пароль:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
            
            <button type="submit"> ЗАРЕГИСТРИРОВАТЬСЯ </button>
        </form>

        <?php include __DIR__ . '/../../shared/footer.php'; ?>     
    </body>
</html>