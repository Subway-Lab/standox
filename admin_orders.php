<?php
// NOTE: Проверка авторизации пользователя
require_once('auth_check.php');
?>

<?php
// NOTE: Включаем отображение ошибок (для отладки)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// NOTE: Подключаемся к базе данных
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "sto_orders";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

/// NOTE: Обработка поискового запроса (по модели, ФИО или телефону)
$search = "";
$searchQuery = "";
$searchParams = [];
$searchTypes = "";

if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search = trim($_GET['search']);
    // Исправлено количество параметров и пробел после LIKE
    $searchQuery = " WHERE car_model LIKE ? OR car_number LIKE ? OR name LIKE ? OR surname LIKE ? OR patronymic LIKE ? OR phone LIKE ?";
    $likeSearch = "%" . $search . "%";
    $searchParams = array_fill(0, 6, $likeSearch);
    $searchTypes = "ssssss"; 
}

// NOTE: Запрос для получения заказов
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
                        <li><a href="index.php" class="menu_link"> новый заказ-наряд </a></li>
                        <li><a href="registration.php" class="menu_link"> выйти </a></li>
                    </ul>
                </nav> 
            </header>

            <div class="sticky_wrapper">
                <div class="search_block">
                    <h2> База данных заказ-нарядов </h2>
                    <label class="icon_search" for="toggle">
                        <img class="loupe" src="filles/gray_search.svg" alt="icon search">
                    </label>
                    <input type="checkbox" id="toggle">
                    <aside id="info-panel">
                        <form method="get" action="admin_orders.php">
                            <input class="search_input" type="text" name="search" placeholder="Поиск по ФИО, модели, номеру или сумме заказа" 
                                value="<?= htmlspecialchars($search) ?>">
                            <?php if ($search): ?>
                            <?php endif; ?>
                        </form>
                    </aside>
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
                            <th> Действия </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($order = $result->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <a class="link_move_1" href="order_confirmation.php?id=<?= htmlspecialchars($order['id']) ?>">
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
                                        <a class="link_move_2" href="order_confirmation.php?id=<?= $order['id'] ?>" target="_blank"> Распечатать </a>
                                        <a class="link_move_2" href="edit_order.php?id=<?= $order['id'] ?>"> Редактировать </a>
                                        <a class="link_move_2" href="javascript:void(0)" onclick="confirmDeletion(<?= $order['id'] ?>)"> Удалить </a>
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