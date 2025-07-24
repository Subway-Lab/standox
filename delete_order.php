<?php
// NOTE: Проверка авторизации пользователя
require_once('auth_check.php');
?>

<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Подключаемся к базе данных
$servername = "g8r9w9tmspbwmsyo.cbetxkdyhwsb.us-east-1.rds.amazonaws.com"; // Хост базы данных на Heroku
$username   = "q1i28z5zzuyro11l"; // Имя пользователя базы данных
$password   = "kwdvun8ff1f8m6fs"; // Пароль к базе данных
$dbname     = "vtjb3fkssehwjx62"; // Имя базы данных

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: database.php");
        exit;
    } else {
        echo "Ошибка удаления заказа: " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "Не указан ID заказа.";
}
$conn->close();
?>
