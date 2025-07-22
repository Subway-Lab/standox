<?php
// NOTE: Проверка авторизации пользователя
require_once('../../auth_check.php');

$base_url = '../../';
?>

<?php
// NOTE: Включение отладки убрать в продакшине
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Подключаемся к базе данных
$servername = "g8r9w9tmspbwmsyo.cbetxkdyhwsb.us-east-1.rds.amazonaws.com"; // Хост базы данных на Heroku
$username   = "q1i28z5zzuyro11l"; // Имя пользователя базы данных
$password   = "kwdvun8ff1f8m6fs"; // Пароль к базе данных
$dbname     = "vtjb3fkssehwjx62"; // Имя базы данных

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Обработка GET/POST запросов
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // ОБРАБОТКА СОХРАНЕНИЯ ФОРМЫ
    $order_id = intval($_POST['order_id']);
    
    // Обновление основной информации
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
    
    // Удаляем старые услуги
    $conn->query("DELETE FROM list_of_work WHERE order_id = $order_id");
    
    // ИСПРАВЛЕНИЕ #1: Новый способ сбора данных об услугах
    $services = [];
    // Собираем все возможные индексы услуг
    for ($i = 1; $i <= 10000; $i++) { // 300 - максимальный ожидаемый ID услуги
        $price_key = 'service' . $i . '_price';
        $name_key = 'service' . $i . '_name';
        $section_key = 'service' . $i . '_section';
        
        // Проверяем наличие и стоимость услуги
        if (isset($_POST[$price_key]) && floatval($_POST[$price_key]) > 0) {
            $services[] = [
                'service_id' => $i,
                'price'      => (float)$_POST[$price_key],
                'name'       => $_POST[$name_key] ?? '',
                'section'    => $_POST[$section_key] ?? 'work'
            ];
        }
    }

    // Добавляем новые услуги
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
        
    header("Location: ../../admin_orders.php?id=$order_id");
    exit;
}

// Загрузка данных заказа
$order_data = [];
$services_data = [];

if ($order_id > 0) {
    // Основные данные заказа
    $result = $conn->query("SELECT * FROM orders WHERE id = $order_id");
    $order_data = $result->fetch_assoc();
    
    // Данные об услугах
    $result = $conn->query("SELECT * FROM list_of_work WHERE order_id = $order_id");
    while ($row = $result->fetch_assoc()) {
        $services_data[$row['service_id']] = $row;
    }
}

// NOTE: Подключаем файлы с услугами
$works_services = require 'https://www.standox.pro/shared/works.php';
$painting_services = require 'https://www.standox.pro/shared/painting.php';
$parts_services = require 'https://www.standox.pro/shared/parts.php';
?>

<!DOCTYPE HTML>
<html lang="ru">
    
    <?php
        $ebitingCss = 'editing.css';
        include '../../shared/head.php';
    ?>
    <body>

        <header>
            <h1> STANDOX </h1>
            <nav class="menu">
                <ul>
                    <li><a href="../../admin_orders.php" class="menu_link"> база данных </a></li>
                    <li><a href="logout.php" class="menu_link"> выйти </a></li>
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
                    <h4> 2. Кузовные работы: </h4>
                </div>

                <?php
                foreach ($works_services as $section) {
                    echo '
                    <div class="collapsible-container">
                        <div class="collapsible-header">
                            <label class="form-label">'.$section['title'].'</label>
                            <span class="collapsible-arrow">↓</span>
                        </div>
                        <div class="collapsible-content">
                            <div class="wrapper">';
                    foreach ($section['items'] as $index => $item) {
                        $serviceNumber = $section['base_id'] + ($index * $section['id_step']);
                        $serviceId = 'service'.$serviceNumber;
                        $is_checked = isset($services_data[$serviceNumber]);
                        $price_value = $is_checked ? $services_data[$serviceNumber]['price'] : 0;
                        
                        echo '
                                <div class="service-item">
                                    <input type="checkbox" class="service-checkbox" id="'.$serviceId.'" 
                                        data-service-name="'.$item['name'].'" 
                                        data-section="'.$section['section'].'" 
                                        data-service-id="'.$serviceNumber.'"
                                        '.($is_checked ? 'checked' : '').'>
                                    <label for="'.$serviceId.'" class="checkbox-btn">'.$item['label'].'</label>
                                    <!-- ИСПРАВЛЕНИЕ #2: Добавлен атрибут name для поля стоимости -->
                                    <input type="number" class="service-cost" id="'.$serviceId.'-cost" 
                                        name="service'.$serviceNumber.'_price"
                                        placeholder="0.00" 
                                        value="'.($is_checked ? $price_value : '').'" 
                                        '.($is_checked ? '' : 'disabled').'>
                                        
                                    <input type="hidden" name="service'.$serviceNumber.'_name" 
                                        id="service'.$serviceNumber.'-name" value="'.$item['name'].'">
                                    <input type="hidden" name="service'.$serviceNumber.'_section" value="'.$section['section'].'">
                                </div>';
                    }
                    echo '
                            </div>
                        </div>
                    </div>';
                }
                ?>

                <div class="title">
                    <h4> 3. Покрасочные работы: </h4>
                </div>

                <?php
                foreach ($painting_services as $section) {
                    echo '
                    <div class="collapsible-container">
                        <div class="collapsible-header">
                            <label class="form-label">'.$section['title'].'</label>
                            <span class="collapsible-arrow">↓</span>
                        </div>
                        <div class="collapsible-content">
                            <div class="wrapper">';
                    foreach ($section['items'] as $index => $item) {
                        $serviceNumber = $section['base_id'] + ($index * $section['id_step']);
                        $serviceId = 'service'.$serviceNumber;
                        $is_checked = isset($services_data[$serviceNumber]);
                        $price_value = $is_checked ? $services_data[$serviceNumber]['price'] : 0;
                        
                        echo '
                                <div class="service-item">
                                    <input type="checkbox" class="service-checkbox" id="'.$serviceId.'" 
                                        data-service-name="'.$item['name'].'" 
                                        data-section="'.$section['section'].'" 
                                        data-service-id="'.$serviceNumber.'"
                                        '.($is_checked ? 'checked' : '').'>
                                    <label for="'.$serviceId.'" class="checkbox-btn">'.$item['label'].'</label>
                                    <!-- ИСПРАВЛЕНИЕ #2: Добавлен атрибут name для поля стоимости -->
                                    <input type="number" class="service-cost" id="'.$serviceId.'-cost" 
                                        name="service'.$serviceNumber.'_price"
                                        placeholder="0.00" 
                                        value="'.($is_checked ? $price_value : '').'" 
                                        '.($is_checked ? '' : 'disabled').'>
                                        
                                    <input type="hidden" name="service'.$serviceNumber.'_name" 
                                        id="service'.$serviceNumber.'-name" value="'.$item['name'].'">
                                    <input type="hidden" name="service'.$serviceNumber.'_section" value="'.$section['section'].'">
                                </div>';
                    }
                    echo '
                            </div>
                        </div>
                    </div>';
                }
                ?>

                <div class="title">
                    <h4> 4. Запасные части и расходные материалы </h4>
                </div>

                <?php
                foreach ($parts_services as $section) {
                    echo '
                    <div class="collapsible-container">
                        <div class="collapsible-header">
                            <label class="form-label">'.$section['title'].'</label>
                            <span class="collapsible-arrow">↓</span>
                        </div>
                        <div class="collapsible-content">
                            <div class="wrapper">';
                    foreach ($section['items'] as $index => $item) {
                        $serviceNumber = $section['base_id'] + ($index * $section['id_step']);
                        $serviceId = 'service'.$serviceNumber;
                        $is_checked = isset($services_data[$serviceNumber]);
                        $price_value = $is_checked ? $services_data[$serviceNumber]['price'] : 0;
                        
                        echo '
                                <div class="service-item">
                                    <input type="checkbox" class="service-checkbox" id="'.$serviceId.'" 
                                        data-service-name="'.$item['name'].'" 
                                        data-section="'.$section['section'].'" 
                                        data-service-id="'.$serviceNumber.'"
                                        '.($is_checked ? 'checked' : '').'>
                                    <label for="'.$serviceId.'" class="checkbox-btn">'.$item['label'].'</label>
                                    <!-- ИСПРАВЛЕНИЕ #2: Добавлен атрибут name для поля стоимости -->
                                    <input type="number" class="service-cost" id="'.$serviceId.'-cost" 
                                        name="service'.$serviceNumber.'_price"
                                        placeholder="0.00" 
                                        value="'.($is_checked ? $price_value : '').'" 
                                        '.($is_checked ? '' : 'disabled').'>
                                        
                                    <input type="hidden" name="service'.$serviceNumber.'_name" 
                                        id="service'.$serviceNumber.'-name" value="'.$item['name'].'">
                                    <input type="hidden" name="service'.$serviceNumber.'_section" value="'.$section['section'].'">
                                </div>';
                    }
                    echo '
                            </div>
                        </div>
                    </div>';
                }
                ?>

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

        <?php include '../../shared/footer.php'; ?>

        <script src="../../index_1.js"></script>   
        <script src="editing_1.js"></script>
        <script src="editing_2.js"></script>
        <script src="editing_3.js"></script>
        
    </body>
</html>
<?php $conn->close(); ?>