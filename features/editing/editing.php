<?php
    require_once __DIR__ . '/../auth/check.php';  // NOTE: Проверка авторизации пользователя

    $ebitingCss = 'editing.css';
    include __DIR__ . '/../../shared/head.php';
?>

<?php
// FIXME: Включение отладки (Убрать в продакшине)
error_reporting(E_ALL);
ini_set('display_errors', 1);


// NOTE: Подключение к базе данных
$servername = "g8r9w9tmspbwmsyo.cbetxkdyhwsb.us-east-1.rds.amazonaws.com"; // NOTE: Хост базы данных на Heroku
$username   = "q1i28z5zzuyro11l"; // NOTE: Имя пользователя базы данных
$password   = "kwdvun8ff1f8m6fs"; // NOTE: Пароль базы данных
$dbname     = "vtjb3fkssehwjx62"; // NOTE: Имя базы данных

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $order_id = intval($_POST['order_id']);
    
    // NOTE: Обновление основной информации
    $stmt = $conn->prepare("UPDATE orders SET 
        surname = ?,
        name = ?,
        patronymic = ?,
        phone = ?,
        car_model = ?,
        car_number = ?,
        services_total = ?
        WHERE id = ?");
    
    $stmt->bind_param("ssssssii",
        $_POST['surname'],
        $_POST['name'],
        $_POST['patronymic'],
        $_POST['phone'],
        $_POST['car_model'],
        $_POST['car_number'],
        $_POST['total_price'],
        $order_id
    );
    $stmt->execute();
    
    $conn->query("DELETE FROM list_of_work WHERE order_id = $order_id");
    
    $services = [];
    for ($i = 1; $i <= 10000; $i++) {
        $price_key = 'service' . $i . '_price';
        $name_key = 'service' . $i . '_name';
        $section_key = 'service' . $i . '_section';

        // NOTE: Проверка наличия и стоимость услуги
        if (isset($_POST[$price_key]) && floatval($_POST[$price_key]) > 0) {
            $services[] = [
                'service_id' => $i,
                'price'      => (float)$_POST[$price_key],
                'name'       => $_POST[$name_key] ?? '',
                'section'    => $_POST[$section_key] ?? 'work'
            ];
        }
    }

    // NOTE: Добавление новых услуг
    foreach ($services as $service) {
        $sql_services = "INSERT INTO list_of_work 
            (order_id, service_id, name_work, price, section, full_work) 
            VALUES (?, ?, ?, ?, ?, ?)";
            
        $stmt_services = $conn->prepare($sql_services);
        $full_work = "{$service['name']} {$service['price']} руб."; 
        
        $stmt_services->bind_param("iissss",
            $order_id,
            $service['service_id'],
            $service['name'],
            $service['price'],
            $service['section'],
            $full_work
        );
        
        $stmt_services->execute();
    }
        
    header('Location: ' . $basePath . '/features/database/database.php?id=' . urlencode($order_id));
    exit();
}

// NOTE: Загрузка данных заказа
$order_data = [];
$services_data = [];

if ($order_id > 0) {
    // NOTE:Основные данные заказа
    $result = $conn->query("SELECT * FROM orders WHERE id = $order_id");
    $order_data = $result->fetch_assoc();
    
    // NOTE: Данные об услугах
    $result = $conn->query("SELECT * FROM list_of_work WHERE order_id = $order_id");
    while ($row = $result->fetch_assoc()) {
        $services_data[$row['service_id']] = $row;
    }
}
?>

<!DOCTYPE HTML>
<html lang="ru">
    <body>

        <header>
            <h1> STANDOX </h1>
            <nav class="menu">
                <ul>
                    <li><a href="<?= $basePath ?>/features/database/database.php" class="menu_link"> база данных </a></li>
                    <li><a href="<?= $basePath ?>/features/auth/logout.php" class="menu_link"> выйти </a></li>
                </ul>
            </nav>
        </header>

        <div class="form">
            <form id="orderForm" action="editing.php" method="POST">
                <input type="hidden" name="order_id" value="<?= $order_id ?>">

            <div class="title">
                <h2> Редактирование заказ-наряда № <?= $order_id ?> </h2>
            </div>
            <div class="title">
                <h3> 1. Данные о заказчике: </h3>
            </div>

                <div class="customer">
                    <label for="surname" class="sr-only"> Фамилия </label>
                    <input class="user_input" id="surname" type="text" name="surname"
                        value="<?= htmlspecialchars($order_data['surname'] ?? '') ?>" 
                        placeholder="Фамилия *" pattern="^[а-яА-ЯёЁ\-]+$" title="Укажите Фамилию" required>

                    <label for="name" class="sr-only"> Имя </label>
                    <input class="user_input" id="name" type="text" name="name" 
                        value="<?= htmlspecialchars($order_data['name'] ?? '') ?>" 
                        placeholder="Имя *" pattern="^[а-яА-ЯёЁ\-]+$" title="Укажите Имя" required>
                    
                    <div class="error-message" data-for="surname"> Допустимые символы: буквы кириллицы, знак тире </div>
                    <div class="error-message" data-for="name"> Допустимые символы: буквы кириллицы, знак тире </div>

                    <label for="patronymic" class="sr-only"> Отчество </label>
                    <input class="user_input" id="patronymic" type="text" name="patronymic" 
                        value="<?= htmlspecialchars($order_data['patronymic'] ?? '') ?>" 
                        placeholder="Отчество" pattern="^[а-яА-ЯёЁ\s\-]+$" title="Укажите Отчествo">
                    
                    <label for="phone" class="sr-only"> Контактный телефон </label>
                    <input class="user_input" id="phone" type="text" name="phone"
                        value="<?= htmlspecialchars($order_data['phone'] ?? '') ?>" 
                        placeholder="Номер телефона *" pattern="^[0-9\+\-\s]+$" title="Укажите Номер телефона" required>

                    <div class="error-message" data-for="patronymic"> Допустимые символы: буквы кириллицы, знак тире, пробел </div>
                    <div class="error-message" data-for="phone"> Допустимые символы: цифры, знак "+", тире, пробел </div>

                    <label for="car_model" class="sr-only"> Марка автомобиля </label>
                    <input class="user_input" id="car_model" type="text" name="car_model"
                        value="<?= htmlspecialchars($order_data['car_model'] ?? '') ?>" 
                        placeholder="Марка автомобиля *" pattern="^[а-яА-ЯёЁa-zA-Z0-9\-\s]+$" title="Укажите Марку автомобиля" required>

                    <label for="car_number" class="sr-only"> Регистрационный знак </label>
                    <input class="user_input" id="car_number" type="text" name="car_number"
                        value="<?= htmlspecialchars($order_data['car_number'] ?? '') ?>" 
                        placeholder="Регистрационный знак *" pattern="^[а-яА-ЯёЁa-zA-Z0-9\-\s]+$" title="Укажите Гос. номер" required>

                    <div class="error-message" data-for="car_model"> Допустимые символы: буквы кириллицы, латиницы, знак тире, пробел </div>
                    <div class="error-message" data-for="car_number"> Допустимые символы: буквы кириллицы, латиницы, знак тире, пробел </div>
                </div>
                <p class="asterisk"> * поля обязательные для заполнения </p>

                <div class="title">
                    <h3> 2. Кузовные работы: </h3>
                </div>
                <div id="section-works" class="heavy"></div>

                <div class="title">
                    <h3> 3. Покрасочные работы: </h3>
                </div>
                <div id="section-painting" class="heavy"></div>

                <div class="title">
                    <h3> 4. Запасные части и расходные материалы: </h3>
                </div>
                <div id="section-parts" class="heavy"></div>

                <div class="title">
                    <h4> Итого: 
                        <span id="totalPrice">
                            <?= number_format(($order_data['services_total'] ?? 0), 0, ',', ' ') ?>
                        </span> руб. 
                    </h4>
                    <input type="hidden" id="total_price_hidden" name="total_price" 
                        value="<?= $order_data['services_total'] ?? 0 ?>">
                </div>

                <div class="button_block">
                    <button type="submit" class="btn btn-save left_button"> СОХРАНИТЬ ИЗМЕНЕНИЯ </button>
                    <button type="reset" class="btn btn-reset right_button"> СБРОСИТЬ ИЗМЕНЕНИЯ </button>
                </div>
            </form>
        </div>

        <?php include __DIR__ . '/../../shared/footer.php'; ?>

        <script src="<?= $basePath ?>/index_services.js?v=<?php echo $version; ?>" defer></script>
        <script src="<?= $basePath ?>/index_1.js?v=<?php echo $version; ?>" defer></script>
        <script src="editing_1.js?v=<?php echo $version; ?>" defer></script>
        <script src="editing_2.js?v=<?php echo $version; ?>" defer></script>
        <script src="editing_3.js?v=<?php echo $version; ?>" defer></script>
    </body>
</html>
<?php $conn->close(); ?>