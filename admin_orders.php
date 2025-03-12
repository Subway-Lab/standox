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

<!DOCTYPE HTML>
<html lang="ru">
    <head>
        <meta charset="utf-8">
        <meta name="keywords" content="key words">
        <meta name="description" content="description of the page SEO">
        <title> STANDOX </title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.11.5/gsap.min.js"></script>
        <link rel="stylesheet" type="text/css" href="style.css">
        <link rel="stylesheet" type="text/css" href="admin_orders.css">
    </head>
    <body>

        <header>
            <h1> STANDOX </h1>
            <nav class="menu">
                <ul>
                    <li><a href="index.html" class="menu_link"> новый заказ-наряд </a></li>
                    <li><a href="registration.php" class="menu_link"> выйти </a></li>
                </ul>
            </nav>
        </header>

        <div class="search_block">
            <h4> База даных заказ-нарядов </h4>
            <div class="search">
                <form class="search-form" method="get" action="admin_orders.php">
                    <input type="text" name="search" placeholder="Поиск по модели, ФИО или телефону" value="<?= htmlspecialchars($search) ?>">
                    <button type="submit"> ПОИСК </button>
                    <?php if ($search): ?>
                        <a href="admin_orders.php"> Сбросить поиск </a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

         <div class="base">
            <table>
                <thead>
                    <tr>
                        <th> Номер закза </th>
                        <th> Дата </th>
                        <th> Закзчик </th>
                        <th> Номер телефона </th>
                        <th> Марка т/с </th>
                        <th> Гос. номер </th>
                        <th> Сумма заказа </th>
                        <th> Из них работы </th>
                        <th> Из них запчасти </th>
                        <!-- <th>Услуги</th> -->
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($order = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($order['id']) ?></td>
                                <td><?= htmlspecialchars(date('d.m.Y', strtotime($order['created_at']))) ?></td>
                                <td><?= htmlspecialchars($order['surname'] . ' ' . $order['name'] . ' ' . $order['patronymic']) ?></td>
                                <td><?= htmlspecialchars($order['phone']) ?></td>
                                <td><?= htmlspecialchars($order['car_model']) ?></td>
                                <td><?= htmlspecialchars($order['car_number']) ?></td>
                                <td><?= htmlspecialchars($order['services_total']) ?></td>
                                <td><?= htmlspecialchars(floor($order['total_work_price'] ?? 0)) ?></td>
                                <td><?= htmlspecialchars(floor($order['total_parts_price'] ?? 0)) ?></td>
                                <!--<td><?= nl2br(htmlspecialchars($order['services'])) ?></td>-->
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
        </div>

    
    <!-- <style>
        form.search-form { margin-bottom: 20px; }
        .action-links a { margin-right: 10px; }
    </style> -->

    



    <script>
        // Функция подтверждения удаления заказа
        function confirmDeletion(orderId) {
            if (confirm("Вы уверены, что хотите удалить этот заказ?")) {
                window.location.href = "delete_order.php?id=" + orderId;
            }
        }
    </script>

</body>
</html>

<?php
// Закрываем подключение к БД
$conn->close();
?>