<?php
    require_once __DIR__ . '/../../shared/path.php';  // NOTE: Подключение базового пути
    require_once __DIR__ . '/../auth/check.php';  // NOTE: Проверка авторизации пользователя
?>

<?php
// NOTE: Включение отладки (Убрать в продакшине)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// NOTE: Подключение к базе данных
$servername = "g8r9w9tmspbwmsyo.cbetxkdyhwsb.us-east-1.rds.amazonaws.com"; // NOTE: Хост базы данных на Heroku
$username   = "q1i28z5zzuyro11l"; // NOTE: Имя пользователя базы данных
$password   = "kwdvun8ff1f8m6fs"; // NOTE: Пароль базы данных
$dbname     = "vtjb3fkssehwjx62"; // NOTE: Имя базы данных

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

// NOTE: Обработка поискового запроса (по модели, ФИО или телефону)
$search = "";
$searchQuery = "";
$searchParams = [];
$searchTypes = "";

if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search = trim($_GET['search']);
    $searchQuery = " WHERE car_model LIKE ? OR car_number LIKE ? OR name LIKE ? OR surname LIKE ? OR patronymic LIKE ? OR phone LIKE ?";
    $likeSearch = "%" . $search . "%";
    $searchParams = array_fill(0, 6, $likeSearch);
    $searchTypes = "ssssss"; 
}

// NOTE: Запрос для получения заказов
if ($searchQuery) {
    $stmt = $conn->prepare("SELECT * FROM orders $searchQuery ORDER BY created_at DESC");
    // NOTE: Привязываем параметры поиска
    $stmt->bind_param($searchTypes, ...$searchParams);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM orders ORDER BY created_at DESC");
}
?>

<!DOCTYPE HTML>
<html lang="ru">
    
    <?php
        $databaseCss = 'database.css';
        include __DIR__ . '/../../shared/head.php';
    ?>

        <body>

            <header>
                <h1> STANDOX </h1>
                <nav class="menu">
                    <ul>
                        <li><a href="<?= $basePath ?>/index.php" class="menu_link"> новый заказ-наряд </a></li>
                        <li><a href="<?= $basePath ?>/features/auth/logout.php" class="menu_link"> выйти </a></li>
                    </ul>
                </nav> 
            </header>

            <div class="sticky_wrapper">
                <div class="search_block">
                    <h2> База данных заказ-нарядов </h2>
                    <img class="loupe" src="<?= $basePath ?>/files/gray_search.svg" loading="lazy" alt="icon search">
                </div>

                <div class="search_form">
                    <form method="get" action="<?= $basePath ?>/features/database/database.php">
                        <div class="input-container">
                            <input class="search_input" type="text" name="search" placeholder="ПОИСК" 
                                value="<?= htmlspecialchars($search) ?>">
                            <span class="close_icon"></span>
                        </div>
                        <?php if ($search): ?>
                            <p> Результаты поиска для: "<?= htmlspecialchars($search) ?>"</p>
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
                            <th> Действия </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($order = $result->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <a class="link_move_1" href="<?= $basePath ?>/features/print/print.php?id=<?= htmlspecialchars($order['id']) ?>">
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
                                        <a class="link_move_2" href="<?= $basePath ?>/features/print/print.php?id=<?= $order['id'] ?>" target="_blank"> Распечатать </a>
                                        <a class="link_move_2" href="<?= $basePath ?>/features/editing/editing.php?id=<?= $order['id'] ?>"> Редактировать </a>
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
            
            <?php include __DIR__ . '/../../shared/footer.php'; ?>

            <script src="<?= $basePath ?>/features/database/database.js?v=<?php echo $version; ?>" defer></script>
            <script src="<?= $basePath ?>/features/database/searchBar.js?v=<?php echo $version; ?>" defer></script>

        </body>
    </html>

    <?php

    // NOTE: Закрываем подключение к БД
    $conn->close();
    ?>