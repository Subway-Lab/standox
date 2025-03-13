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


        <style>
            label[for="toggle"] {
            font-size: 3rem;
            position: absolute;
            top: 4px;
            right: 5px;
            z-index: 1;
            cursor: pointer;
            }

            input[type="checkbox"] {
            position: absolute;
            top: -100px;
            }
        </style>
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

        <div class="experement">
            <label for="toggle" aria-expanded="false" aria-controls="info-panel">❔</label>
            <input type="checkbox" id="toggle">
            <aside id="info-panel">
            <form class="search-form" method="get" action="admin_orders.php">
                    <input type="text" name="search" placeholder="Поиск по модели, ФИО или телефону" value="<?= htmlspecialchars($search) ?>">
                    <button type="submit"> ПОИСК </button>
                    <?php if ($search): ?>
                        <a href="admin_orders.php"> Сбросить поиск </a>
                    <?php endif; ?>
                </form>
                <!-- <h2>Information</h2>
                <p>Some very important information about your app:</p>
                <ol>
                    <li>It has a really cool slide-out information panel.</li>
                    <li>This information panel uses a combination of fixed positioning and a CSS transition for the smooth sliding.</li>
                    <li>Using JavaScript this information panel is brought in and out of the view.</li>
                </ol> -->
            </aside>
        </div>



        <!--<div class="search_block">
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
        </div> -->


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
                        <th> Действия </th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($order = $result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <a class="link_move_2
                                    "  href="order_confirmation.php?id=<?= htmlspecialchars($order['id']) ?>">
                                        <?= htmlspecialchars($order['id']) ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars(date('d.m.Y', strtotime($order['created_at']))) ?></td>
                                <td><?= htmlspecialchars($order['surname'] . ' ' . $order['name'] . ' ' . $order['patronymic']) ?></td>
                                <td><?= htmlspecialchars($order['phone']) ?></td>
                                <td><?= htmlspecialchars($order['car_model']) ?></td>
                                <td><?= htmlspecialchars($order['car_number']) ?></td>
                                <td><?= htmlspecialchars(number_format($order['services_total'], 0, '.', ' ')) ?></td>
                                <td><?= htmlspecialchars(number_format(floor($order['total_work_price'] ?? 0), 0, '.', ' ')) ?></td>
                                <td><?= htmlspecialchars(number_format(floor($order['total_parts_price'] ?? 0), 0, '.', ' ')) ?></td>
                                <td>
                                    <a class="link_move_1" href="order_confirmation.php?id=<?= $order['id'] ?>" target="_blank"> Распечатать </a>
                                    <a class="link_move_1" href="edit_order.php?id=<?= $order['id'] ?>"> Редактировать </a>
                                    <a class="link_move_1" href="javascript:void(0)" onclick="confirmDeletion(<?= $order['id'] ?>)"> Удалить </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7">Заказов не найдено</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <footer>
            <div class="all_footer_block">
                <div class="footer_column_1">
                    <img class="bottom_logo" src="filles/black_logo.svg" alt="STANDOX logo">
                    <div class="contacts">
                        <p>
                            СТО "STANDOX" <br>
                            672039, г. Чита, ул. Верхоленская 51 <br>
                            телефон: 8 914 472-10-10, 8 924 472-30-30 <br>
                            email: lider00@list.ru <br>
                            web-site: www.standox.chita.ru
                        </p>
                    </div>
                </div>

                <div class="property">
                    <p>
                        Данное программное обеспечение является интеллектуальной собственностью <br>
                        Индивидуального предпринимателя Фарафонова Владимира Владимировича <br>
                        ОГРНИП: 306753636100113, ИНН: 753610458920 <br>
                        Все права защищены
                    </p>
                </div>

                <div class="sbwlab">
                    <p>
                        © 2025 SUBWAY LAB COMPANY <br>
                        <span class="other_font_size"> программа разработана в рамках проекта «STANDOX» </span>
                    </p>
                </div>
            </div>
        </footer>

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