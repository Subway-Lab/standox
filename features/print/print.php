<?php
    require_once __DIR__ . '/../auth/check.php';  // NOTE: Проверка авторизации пользователя
    require_once __DIR__ . '/../../shared/path.php';  // NOTE: Подключение базового пути
?>

<?php
    // NOTE: Подключаемся к базе данных
    $servername = "g8r9w9tmspbwmsyo.cbetxkdyhwsb.us-east-1.rds.amazonaws.com"; // NOTE: Хост базы данных на Heroku
    $username   = "q1i28z5zzuyro11l"; // NOTE: Имя пользователя базы данных
    $password   = "kwdvun8ff1f8m6fs"; // NOTE: Пароль к базе данных
    $dbname     = "vtjb3fkssehwjx62"; // NOTE: Имя базы данных


    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Ошибка подключения: " . $conn->connect_error);
    }

    // NOTE: Получение данных order_id из GET-параметра
    $order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($order_id <= 0) {
        die("Неверный номер заказа.");
    }

    // NOTE: Запрос данных заказа
    $sql_order = "SELECT id, full_name, phone, created_at, car_model, car_number, services_total, total_work_price, total_parts_price FROM orders WHERE id = ?";
    $stmt = $conn->prepare($sql_order);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        die("Заказ не найден.");
    }
    $order = $result->fetch_assoc();
    $stmt->close();

    // NOTE: Запрос данных работ, только для section = 'work'
    $sql_works = "SELECT service_id, name_work, price FROM list_of_work WHERE order_id = ? AND section = 'work'";
    $stmt_works = $conn->prepare($sql_works);
    $stmt_works->bind_param("i", $order_id);
    $stmt_works->execute();
    $result_works = $stmt_works->get_result();
    $works = [];
    while ($row = $result_works->fetch_assoc()) {
        $works[] = $row;
    }
    $stmt_works->close();

    // NOTE: Запрос данных запчастей, только для section = 'parts'
    $sql_parts = "SELECT service_id, name_work, price FROM list_of_work WHERE order_id = ? AND section = 'parts'";
    $stmt_parts = $conn->prepare($sql_parts);
    $stmt_parts->bind_param("i", $order_id);
    $stmt_parts->execute();
    $result_parts = $stmt_parts->get_result();
    $parts = [];
    while ($row = $result_parts->fetch_assoc()) {
        $parts[] = $row;
    }
    $stmt_parts->close();

    $conn->close();
?>

<!DOCTYPE HTML>
<html lang="ru">
    
    <?php
        $noStyle = true;
        $printCss = 'print.css';
        include __DIR__ . '/../../shared/head.php';
    ?>

    <body class="sheet">
        <header>
            <div class="work_order">
                <p> ИП Фарафонов </p>
                <p> Владимир Владимирович </p>
                <p> ОГРНИП: 306753636100113 </p>
                <p> ИНН: 753610458920 </p>
                <h1> ЗАКАЗ-НАРЯД № <?= htmlspecialchars($order['id']) ?> </h1>
                <p> ул. Верхоленская 51 </p>
                <p> тел. 8 914 472-10-10 </p>
                <p> 8 924 472-30-30 </p>
                <p> www.standox.chita.ru </p>
                <p> email: lider00@list.ru </p>
                <img src="<?= $basePath ?>/files/standox_logo.svg" loading="lazy" alt="Standox logo">
            </div>

            <div class="customer_data">
                <p> Заказчик: <?php echo htmlspecialchars($order['full_name']); ?></p>
                <p> тел. <?= htmlspecialchars($order['phone']) ?></p>
                <p> Марка т/с: <?= htmlspecialchars($order['car_model']) ?> </p>
                <p> Гос. номер: <?= htmlspecialchars($order['car_number']) ?> </p>
                <p> Дата заезда: <?php echo date("d.m.Y", strtotime($order['created_at'])); ?> г.</p>
                <p> Итого за работы: <?php echo number_format($order['total_work_price'], 0, '', ' '); ?> руб.</p>
                <p> Итого за запчасти: <?php echo number_format($order['total_parts_price'], 0, '', ' '); ?> руб.</p>
                <p> Всего: <?php echo number_format($order['services_total'], 0, '', ' '); ?> руб.</p>
            </div>
        </header>

        <table>
            <thead>
                <tr>
                    <th> № </th>
                    <th> Наименование выполняемых работ </th>
                    <th> Стоимость </th>
                </tr>
            </thead>
            <tbody>
                <?php
                $counter = 1;
                foreach ($works as $work) {
                    echo "<tr>";
                    echo "<td>" . $counter . "</td>";
                    echo "<td>" . htmlspecialchars($work['name_work']) . "</td>";
                    echo "<td>" . number_format($work['price'], 0, '', ' ') . " руб.</td>";
                    echo "</tr>";
                    $counter++;
                }
                ?>
                <tr>
                    <td colspan="2"><strong> Итого за работы: </strong></td>
                    <td><strong><?php echo number_format($order['total_work_price'], 0, '', ' '); ?> руб.</strong></td>
                </tr>
            </tbody>
        </table>
    
        <table>
            <thead>
                <tr>
                    <th> № </th>
                    <th> Запасные части и расходные материалы </th>
                    <th> Стоимость </th>
                </tr>
            </thead>
            <tbody>
                <?php
                $counter = 1;
                foreach ($parts as $part) {
                    echo "<tr>";
                    echo "<td>" . $counter . "</td>";
                    echo "<td>" . htmlspecialchars($part['name_work']) . "</td>";
                    echo "<td>" . number_format($part['price'], 0, '', ' ') . " руб.</td>";
                    echo "</tr>";
                    $counter++;
                }
                ?>
                <tr>
                    <td colspan="2"><strong> Итого за запчасти: </strong></td>
                    <td><strong><?php echo number_format($order['total_parts_price'], 0, '', ' '); ?> руб.</strong></td>
                </tr>
            </tbody>
        </table>

        <div class="acceptance">
            <p> Претензий к качеству производственных работ не имею </p>
            <p> Дата "_____"________________________ <?= date("Y") ?> г. </p>
            <p> Подпись закзчика____________________________ </p>
        </div>

        <section>
            <div>
                <h2> ОБЯЗАТЕЛЬНО К ОЗНАКОМЛЕНИЮ </h2>
            </div>
            <div>
                <p>
                    1. При отключении аккумуляторной батареи в процессе ремонта автомобиля (по правилам техники безопасности), могут происходить сбои электронных систем. Автосервис за собой всех электросистем и сигнализации ответственности не несет.,<br>
                    2. При оформлении автомобиля в ремонт в грязном виде Автосервис за скрытые повреждения (царапины, сколы, вмятины) ответственности не несет!<br>
                    3. При выполнении кузовных работ, если используются запасные части, не оригинальные, то возможны не корректные установки заменяемых запасных частей (зазоры могут быть большие или меньше от заводских норм).<br>
                    4. За личные вещи при оформлении в ремонт Автосервис ответственности не несет.<br>
                    5. Гарантия на выполненные кузовные и покрасочные работы 6 месяцев с момента оплаты за ремонт.<br>
                    6. На пластиковые, пластмассовые, карбоновые детали гарантия не распространяется.
                </p>
            </div>
            <div>
                <h2>
                    За простой автомобиля по вине заказчика 250 руб. – сутки
                </h2>
            </div>
            <div>
                <p> Претензии по качеству <br>
                    принимаются только <br>
                    согласно выполненных <br>
                    работ заказ-наряда
                </p>
                <p>
                    Итого по заказ-наряду <br>
                    <?php echo number_format($order['services_total'], 0, '', ' '); ?> руб.
                </p>
                <p>
                    Сдал: ___________________________ <br>
                    <sup> подпись заказчика </sup> <br>
                    Принял: ________________________ <br>
                    <sup> подпись исполнителя </sup>
                </p>
            </div>
        </section>

        <div>
            <button class="print-btn" onclick="window.print();"> ПЕЧАТЬ ЗАКАЗА </button>
        </div>
   </body>
</html>