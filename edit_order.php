<?php
// edit_order.php

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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Обработка отправленной формы для обновления заказа
    $id          = intval($_POST['id']);
    $first_name  = $_POST['name'];         // Имя
    $last_name   = $_POST['surname'];      // Фамилия
    $patronymic  = $_POST['patronymic'];   // Отчество
    $phone       = $_POST['phone'];
    $car_model   = $_POST['car_model'];
    $car_number  = $_POST['car_number'];
    $services    = $_POST['services'];         // Услуги
    
    // Обновлённый SQL-запрос с учетом новых полей ФИО
    $sql = "UPDATE orders SET name = ?, surname = ?, patronymic = ?, phone = ?, car_model = ?, car_number = ?, services = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Ошибка подготовки запроса: " . $conn->error);
    }
    
    // Привязываем параметры: 7 строковых значений и 1 целочисленное значение
    $stmt->bind_param("sssssssi", $first_name, $last_name, $patronymic, $phone, $car_model, $car_number, $services, $id);
    
    if ($stmt->execute()) {
        // После успешного обновления перенаправляем обратно к списку заказов
        header("Location: admin_orders.php");
        exit;
    } else {
        echo "Ошибка: " . $stmt->error;
    }
    $stmt->close();
} else {
    // Если запрос GET — получаем данные заказа для редактирования
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $order = $result->fetch_assoc();
        } else {
            die("Заказ не найден.");
        }
        $stmt->close();
    } else {
        die("Не указан ID заказа.");
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактировать заказ №<?= htmlspecialchars($order['id']) ?></title>
    <style>
        form { max-width: 600px; margin: 20px auto; }
        label { display: block; margin-top: 10px; }
        input, textarea { width: 100%; padding: 8px; }
        button { margin-top: 10px; }
    </style>
</head>
<body>
    <h1>Редактировать заказ №<?= htmlspecialchars($order['id']) ?></h1>
    <form method="post" action="edit_order.php">
        <input type="hidden" name="id" value="<?= htmlspecialchars($order['id']) ?>">
        
        <label>Фамилия:</label>
        <input type="text" name="surname" value="<?= htmlspecialchars($order['surname']) ?>" required>
        
        <label>Имя:</label>
        <input type="text" name="name" value="<?= htmlspecialchars($order['name']) ?>" required>
        
        <label>Отчество:</label>
        <input type="text" name="patronymic" value="<?= htmlspecialchars($order['patronymic']) ?>" required>
        
        <label>Телефон:</label>
        <input type="text" name="phone" value="<?= htmlspecialchars($order['phone']) ?>" required>
        
        <label>Модель автомобиля:</label>
        <input type="text" name="car_model" value="<?= htmlspecialchars($order['car_model']) ?>" required>

        <label>Регестрационный знак:</label>
        <input type="text" name="car_number" value="<?= htmlspecialchars($order['car_number']) ?>" required>
        
        <label>Услуги:</label>
        <textarea name="services" rows="4"><?= htmlspecialchars($order['services']) ?></textarea>
        
        <button type="submit">Сохранить изменения</button>
    </form>
</body>
</html>