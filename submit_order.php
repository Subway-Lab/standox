<?php
// Включаем вывод ошибок (только для отладки)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Подключение к базе данных
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "sto_orders";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Получаем данные из формы для таблицы orders
    $last_name  = $_POST['surname'];    // Фамилия
    $first_name = $_POST['name'];       // Имя
    $patronymic = $_POST['patronymic']; // Отчество
    $phone      = $_POST['phone'];      // Телефон
    $car_model  = $_POST['car_model'];  // Модель автомобиля
    $car_number = $_POST['car_number']; // Регистрационный знак
    
    // Получаем итоговую сумму из скрытого поля формы
    $total_price = $_POST['total_price'];
    $total_price = (int)$total_price;  // Преобразуем к целому числу

    // Генерация текущей даты и времени
    $date = date("Y-m-d H:i:s");
    
    // Вставка данных в таблицу orders
    $sql_orders = "INSERT INTO orders (surname, name, patronymic, phone, car_model, car_number, services_total, order_date) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt_orders = $conn->prepare($sql_orders);
    if (!$stmt_orders) {
        die("Ошибка подготовки запроса для orders: " . $conn->error);
    }
    
    $stmt_orders->bind_param("ssssssis", $last_name, $first_name, $patronymic, $phone, $car_model, $car_number, $total_price, $date);
    if (!$stmt_orders->execute()) {
        die("Ошибка выполнения запроса для orders: " . $stmt_orders->error);
    }
    $order_id = $conn->insert_id;

// После успешной вставки в orders и получения $order_id
// Обработка и вставка данных для таблицы list_of_work
if (isset($_POST['services']) && is_array($_POST['services'])) {
    foreach ($_POST['services'] as $service) {
        // Приводим к безопасным типам
        $price = isset($service['price']) ? floatval($service['price']) : 0;
        if ($price <= 0) {
            continue;
        }
        $service_id = isset($service['service_id']) ? intval($service['service_id']) : 0;
        $name = isset($service['name']) ? $conn->real_escape_string($service['name']) : '';
        $section = isset($service['section']) ? $conn->real_escape_string($service['section']) : '';
        $full_work = $name . " " . $price . " руб.";

        $sql_services = "INSERT INTO list_of_work (order_id, service_id, name_work, price, section, full_work) 
                         VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_services = $conn->prepare($sql_services);
        if ($stmt_services === false) {
            die("Ошибка подготовки запроса для list_of_work: " . $conn->error);
        }
        $stmt_services->bind_param("iissss", $order_id, $service_id, $name, $price, $section, $full_work);
        if (!$stmt_services->execute()) {
            die("Ошибка выполнения запроса для list_of_work: " . $stmt_services->error);
        }
        $stmt_services->close();
    }
}    
    // Перенаправляем на страницу подтверждения с ID заказа
    header("Location: order_confirmation.php?id=" . $order_id);
    exit;
}
// Закрываем соединение с базой данных
$conn->close();
?>
