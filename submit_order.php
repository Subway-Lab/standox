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

    // Получаем id созданного заказа
    $order_id = $conn->insert_id;

    // Обработка и вставка данных для таблицы list_of_work
    // Массив с данными услуг
    $service_count = isset($_POST['service_count']) ? (int)$_POST['service_count'] : 0;
    // Обработка данных для услуг, теперь ограничиваемся числом из POST
    $maxServices = 500;
    $services = [];
    for ($i = 1; $i <= $maxServices; $i++) {
        if (isset($_POST["service{$i}_price"]) && $_POST["service{$i}_price"] > 0) {
            $services[] = [
                'name'      => $_POST["service{$i}_name"] ?? '',
                'section'   => $_POST["service{$i}_section"] ?? '',
                'service_id'=> $_POST["service{$i}_service_id"] ?? '',
                'price'     => $_POST["service{$i}_price"] ?? 0,
            ];
        }
    }
    
    foreach ($services as $service) {
        if ($service['price'] > 0) { // Проверяем, чтобы цена была больше 0
            // Вставка данных для каждой услуги в таблицу list_of_work
            $sql_services = "INSERT INTO list_of_work (order_id, service_id, name_work, price, section, full_work) 
                             VALUES (?, ?, ?, ?, ?, ?)";
                             
            // Подготовка запроса
            $stmt_services = $conn->prepare($sql_services);
            
            // Проверка успешности подготовки запроса
            if ($stmt_services === false) {
                die("Ошибка подготовки запроса для list_of_work: " . $conn->error);
            }
    
            $full_work = $service['name'] . " " . $service['price'] . " руб."; // Формируем полное описание работы
            $stmt_services->bind_param("iissss", $order_id, $service['service_id'], $service['name'], $service['price'], $service['section'], $full_work);
    
            // Выполнение запроса
            if (!$stmt_services->execute()) {
                die("Ошибка выполнения запроса для list_of_work: " . $stmt_services->error);
            }
            
            // Закрытие запроса после выполнения
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
