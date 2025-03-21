<?php
// NOTE: Проверка авторизации пользователя
require_once('auth_check.php');
?>

<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "sto_orders";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: admin_orders.php");
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
