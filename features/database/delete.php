<?php
    require_once __DIR__ . '/../auth/check.php';  // NOTE: Проверка авторизации пользователя

    require_once __DIR__ . '/../../shared/path.php';
    $basePath = getBasePath();
?>

<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// NOTE: Подключение к базе данных
$servername = "127.0.0.1"; // NOTE: Хост базы данных на Selectel
$username   = "standox_user"; // NOTE: Имя пользователя базы данных
$password   = "ZiNH7N987CR2"; // NOTE: Пароль базы данных
$dbname     = "standox_db"; // NOTE: Имя базы данных

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header('Location: '. $basePath .'/features/database/database.php');
        exit();
    } else {
        echo "Ошибка удаления заказа: " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "Не указан ID заказа.";
}
$conn->close();
?>