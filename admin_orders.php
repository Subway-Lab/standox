<?php
// admin_orders.php

// Включаем отображение ошибок (для отладки)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Подключаемся к базе данных
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "sto_orders";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

// Обработка поискового запроса (по модели, ФИО или телефону)
$search = "";
$searchQuery = "";
$searchParams = [];
$searchTypes = "";

if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search = trim($_GET['search']);
    // Ищем по полям: car_model, name, surname, patronymic и phone
    $searchQuery = " WHERE car_model LIKE ? OR car_number LIKE? OR name LIKE ? OR surname LIKE ? OR patronymic LIKE ? OR phone LIKE ?";
    $likeSearch = "%" . $search . "%";
    $searchParams = [$likeSearch, $likeSearch, $likeSearch, $likeSearch, $likeSearch];
    $searchTypes = "sssss";
}

// Формируем запрос для получения заказов
if ($searchQuery) {
    $stmt = $conn->prepare("SELECT * FROM orders $searchQuery ORDER BY created_at DESC");
    // Привязываем параметры поиска
    $stmt->bind_param($searchTypes, ...$searchParams);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM orders ORDER BY created_at DESC");
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Управление заказами СТО</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        table, th, td { border: 1px solid #333; }
        th, td { padding: 8px; text-align: left; }
        form.search-form { margin-bottom: 20px; }
        .action-links a { margin-right: 10px; }
    </style>
    <script>
        // Функция подтверждения удаления заказа
        function confirmDeletion(orderId) {
            if (confirm("Вы уверены, что хотите удалить этот заказ?")) {
                window.location.href = "delete_order.php?id=" + orderId;
            }
        }
    </script>
</head>
<body>
    <h1>Управление заказами СТО</h1>
    <!-- Форма поиска заказов -->
    <form class="search-form" method="get" action="admin_orders.php">
        <input type="text" name="search" placeholder="Поиск по модели, ФИО или телефону" value="<?= htmlspecialchars($search) ?>">
        <button type="submit">Найти</button>
        <?php if ($search): ?>
            <a href="admin_orders.php">Сбросить поиск</a>
        <?php endif; ?>
    </form>
    
    <!-- Таблица заказов -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>ФИО</th>
                <th>Телефон</th>
                <th>Модель автомобиля</th>
                <th>Регистрационный знак</th>
                <th>Услуги</th>
                <th>Дата создания</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($order = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($order['id']) ?></td>
                        <td><?= htmlspecialchars($order['surname'] . ' ' . $order['name'] . ' ' . $order['patronymic']) ?></td>
                        <td><?= htmlspecialchars($order['phone']) ?></td>
                        <td><?= htmlspecialchars($order['car_model']) ?></td>
                        <td><?= htmlspecialchars($order['car_number']) ?></td>
                        <td><?= nl2br(htmlspecialchars($order['services'])) ?></td>
                        <td><?= htmlspecialchars($order['created_at']) ?></td>
                        <td class="action-links">
                            <!-- Ссылка на страницу редактирования заказа -->
                            <a href="edit_order.php?id=<?= $order['id'] ?>">Редактировать</a>
                            <!-- Ссылка для удаления заказа -->
                            <a href="javascript:void(0)" onclick="confirmDeletion(<?= $order['id'] ?>)">Удалить</a>
                            <!-- Ссылка для распечатки заказа -->
                            <a href="order_confirmation.php?id=<?= $order['id'] ?>" target="_blank">Распечатать</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="7">Заказов не найдено</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>

<?php
// Закрываем подключение к БД
$conn->close();
?>