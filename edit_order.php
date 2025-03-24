<?php
// NOTE: Проверка авторизации пользователя
require_once('auth_check.php');
?>

<?php
// Включение отладки (уберите в продакшене)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Подключение к БД
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sto_orders";

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
    
    // Добавляем новые услуги
    $services = [];
    // В обработчике POST-запроса (submit_order.php)
    foreach ($_POST as $key => $value) {
        if (preg_match('/service(\d+)_price/', $key, $matches)) {
            $service_id = $matches[1];
            if ($value > 0) {
                $services[] = [
                    'service_id' => $service_id,
                    'price'      => (float)$value,
                    'name'       => $_POST["service{$service_id}_name"] ?? '',
                    'section'    => $_POST["service{$service_id}_section"] ?? 'work'
                ];
            }
        }
    }

    foreach ($services as $service) {
        $sql_services = "INSERT INTO list_of_work 
            (order_id, service_id, name_work, price, section, full_work) 
            VALUES (?, ?, ?, ?, ?, ?)";
            
        $stmt_services = $conn->prepare($sql_services);
        
        // Формируем полное описание
        $full_work = "{$service['name']} {$service['price']} руб."; 
        
        $stmt_services->bind_param("iissss",
            $order_id,
            $service['service_id'],
            $service['name'],
            $service['price'],
            $service['section'],
            $full_work // Добавляем в запрос
        );
        
        $stmt_services->execute();
    }
        
        header("Location: admin_orders.php?id=$order_id");
        exit;
    }

// ПОЛУЧЕНИЕ ДАННЫХ ДЛЯ РЕДАКТИРОВАНИЯ
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
?>

<!DOCTYPE HTML>
<html lang="ru">
    <head>
        <meta charset="utf-8">
        <meta name="keywords" content="key words">
        <meta name="description" content="description of the page SEO">
        <title> STANDOX </title>
        <link rel="icon" href="filles/favicon.ico" type="image/x-icon">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.11.5/gsap.min.js"></script>
        <link rel="stylesheet" type="text/css" href="style.css">
        <link rel="stylesheet" type="text/css" href="edit_order.css">
    </head>
    <body>

        <header>
            <h1> STANDOX </h1>
            <nav class="menu">
                <ul>
                    <li><a href="admin_orders.php" class="menu_link"> база данных </a></li>
                    <li><a href="registration.php" class="menu_link"> выйти </a></li>
                </ul>
            </nav>
        </header>

        <div class="title">
            <h2> Редактирование заказ-наряда № <?= $order_id ?> </h2>
            <h3> 1. Данные о заказчике: </h3>
        </div>

        <div class="form">
            <form id="orderForm" action="edit_order.php" method="POST">
                <input type="hidden" name="order_id" value="<?= $order_id ?>">
                
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

                    <?php
                    function render_service($service_id, $service_name, $label_text, $section, $services_data) {
                        $is_checked = isset($services_data[$service_id]);
                        $price_value = $is_checked ? $services_data[$service_id]['price'] : 0;
                        $checked_attr = $is_checked ? 'checked' : '';
                        $disabled_attr = $is_checked ? '' : 'disabled';
                        
                        echo <<<HTML
                        <div class="service-item">
                            <input type="checkbox" class="service-checkbox" id="service{$service_id}" 
                                data-service-name="{$service_name}" {$checked_attr}>
                            <label for="service{$service_id}" class="checkbox-btn">{$label_text}</label>
                            <input type="number" class="service-cost" id="service{$service_id}-cost" 
                                name="service{$service_id}_price" placeholder="0.00" 
                                value="{$price_value}" {$disabled_attr}>
                            
                            <input type="hidden" name="service{$service_id}_name" 
                                id="service{$service_id}-name" value="{$service_name}">
                            <input type="hidden" name="service{$service_id}_section" value="{$section}">
                            <input type="hidden" name="service{$service_id}_service_id" value="{$service_id}">
                        </div>
                        HTML;
                    } 
                    ?>

                    <div class="title">
                            <h4> 2. Наименование выполняемых работ: </h4>
                    </div>     

                    <div class="collapsible-container">
                        <div class="collapsible-header">
                            <label class="form-label"> Бампер передний </label>
                            <span class="collapsible-arrow">↓</span>
                        </div>
                        <div class="collapsible-content">
                            <div class="wrapper">

                            <?php
                            render_service(303, "Снятие, установка переднего бампера", 
                                "Снятие, установка", "work", $services_data);
                            render_service(306, "Мелкий ремонт переднего бампера", 
                                "Мелкий ремонт", "work", $services_data);
                            render_service(309, "Ремонт бампера переднего без удаления лакокрасочного покрытия", 
                                "Ремонт без удаления лакокрасочного покрытия", "work", $services_data);
                            render_service(312, "Ремонт бампера переднего с удалением лакокрасочного покрытия", 
                                "Ремонт с удалением лакокрасочного покрытия", "work", $services_data);
                            render_service(315, "Изготовление отверстий в переднем бампере под сонары или омыватели фар", 
                                "Изготовление отверстий под сонары или омыватели фар", "work", $services_data);
                            ?>

                            </div>
                        </div>
                    </div>

                    <div class="collapsible-container">
                        <div class="collapsible-header">
                            <label class="form-label">Решетка радиатора</label>
                            <span class="collapsible-arrow">↓</span>
                        </div>
                        <div class="collapsible-content">
                            <div class="wrapper">

                            <?php
                            render_service(603, "Замена решетки радиатора", 
                                "Замена", "work", $services_data);
                            render_service(606, "Ремонт решетки радиатора", 
                                "Ремонт", "work", $services_data);
                            ?>

                            </div>
                        </div>
                    </div>

                    <div class="collapsible-container">
                        <div class="collapsible-header">
                            <label class="form-label"> Капот </label>
                            <span class="collapsible-arrow">↓</span>
                        </div>
                        <div class="collapsible-content">
                            <div class="wrapper">

                            <?php
                            render_service(903, "Снятие, установка капота", 
                                "Снятие, установка капота", "work", $services_data);
                            render_service(906, "Замена капота", 
                                "Замена капота", "work", $services_data);
                            render_service(909, "Ремонт капота без удаления лакокрасочного покрытия", 
                                "Ремонт капота без удаления лакокрасочного покрытия", "work", $services_data);
                            render_service(912, "Ремонт капота с удалением лакокрасочного покрытия", 
                                "Ремонт капота с удалением лакокрасочного покрытия", "work", $services_data);
                            render_service(915, "Замена шарнира капота левого", 
                                "Замена шарнира капота левого", "work", $services_data);
                            render_service(918, "Замена шарнира капота правого", 
                                "Замена шарнира капота правого", "work", $services_data);
                            render_service(921, "Замена обоих шарниров капота", 
                                "Замена обоих шарниров капота", "work", $services_data);
                            ?>

                            </div>
                        </div>
                    </div>

                    <div class="collapsible-container">
                        <div class="collapsible-header">
                            <label class="form-label"> Моторный отсек </label>
                            <span class="collapsible-arrow">↓</span>
                        </div>
                        <div class="collapsible-content">
                            <div class="wrapper">

                            <?php
                            render_service(1203, "Замена передней панели радиатора", 
                                "Замена передней панели радиатора", "work", $services_data);
                            render_service(1206, "Замена передней панели радиатора с частями лонжеронов", 
                                "Замена передней панели радиатора с частями лонжеронов", "work", $services_data);
                            render_service(1209, "Замена лонжерона переднего левого", 
                                "Замена лонжерона переднего левого", "work", $services_data);
                            render_service(1212, "Замена лонжерона переднего правого", 
                                "Замена лонжерона переднего правого", "work", $services_data);
                            render_service(1215, "Ремонт лонжерона переднего левого", 
                                "Ремонт лонжерона переднего левого", "work", $services_data);
                            render_service(1218, "Ремонт лонжерона переднего правого", 
                                "Ремонт лонжерона переднего правого", "work", $services_data);
                            render_service(1221, "Замена моторного щита", 
                                "Замена моторного щита", "work", $services_data);
                            render_service(1224, "Ремонт моторного щита", 
                                "Ремонт моторного щита", "work", $services_data);
                            render_service(1227, "Замена нижнего бруса", 
                                "Замена нижнего бруса", "work", $services_data);
                            ?>

                            </div>
                        </div>
                    </div>

                    <div class="collapsible-container">
                        <div class="collapsible-header">
                            <label class="form-label"> Элементы кузова </label>
                            <span class="collapsible-arrow">↓</span>
                        </div>
                        <div class="collapsible-content">
                            <div class="wrapper">

                            <?php
                            render_service(1503, "Ремонт порога двери левого", 
                                "Ремонт порога двери левого", "work", $services_data);
                            render_service(1506, "Ремонт порога двери правого", 
                                "Ремонт порога двери правого", "work", $services_data);
                            render_service(1509, "Замена порога двери левого", 
                                "Замена порога двери левого", "work", $services_data);
                            render_service(1512, "Замена порога двери правого", 
                                "Замена порога двери правого", "work", $services_data);
                            render_service(1515, "Замена усилителя порога левого", 
                                "Замена усилителя порога левого", "work", $services_data);
                            render_service(1518, "Замена усилителя порога правого", 
                                "Замена усилителя порога правого", "work", $services_data);
                            render_service(1521, "Замена средней стойки кузова левой", 
                                "Замена средней стойки кузова левой", "work", $services_data);
                            render_service(1524, "Замена средней стойки кузова правой", 
                                "Замена средней стойки кузова правой", "work", $services_data);
                            render_service(1527, "Ремонт средней стойки кузова левой", 
                                "Ремонт средней стойки кузова левой", "work", $services_data);
                            render_service(1530, "Ремонт средней стойки кузова правой", 
                                "Ремонт средней стойки кузова правой", "work", $services_data);
                            render_service(1533, "Замена средней стойки кузова с порогом (левой)", 
                                "Замена средней стойки кузова с порогом (левой)", "work", $services_data);
                            render_service(1536, "Замена средней стойки кузова с порогом (правой)", 
                                "Замена средней стойки кузова с порогом (правой)", "work", $services_data);
                            ?>

                            </div>
                        </div>
                    </div>

                    <div class="collapsible-container">
                        <div class="collapsible-header">
                            <label class="form-label"> Крылья передние </label>
                            <span class="collapsible-arrow">↓</span>
                        </div>
                        <div class="collapsible-content">
                            <div class="wrapper">

                            <?php
                            render_service(1803, "Замена крыла переднего левого", 
                                "Замена крыла переднего левого", "work", $services_data);
                            render_service(1806, "Замена крыла переднего правого", 
                                "Замена крыла переднего правого", "work", $services_data);
                            render_service(1809, "Ремонт крыла переднего левого", 
                                "Ремонт крыла переднего левого", "work", $services_data);
                            render_service(1812, "Ремонт крыла переднего правого", 
                                "Ремонт крыла переднего правого", "work", $services_data);
                            render_service(1815, "Ремонт крыла переднего левого без удаления лакокрасочного покрытия", 
                                "Ремонт крыла переднего левого без удаления лакокрасочного покрытия", "work", $services_data);
                            render_service(1818, "Ремонт крыла переднего правого без удаления лакокрасочного покрытия", 
                                "Ремонт крыла переднего правого без удаления лакокрасочного покрытия", "work", $services_data);
                            render_service(1821, "Ремонт крыла переднего левого с удалением лакокрасочного покрытия", 
                                "Ремонт крыла переднего левого с удалением лакокрасочного покрытия", "work", $services_data);
                            render_service(1824, "Ремонт крыла переднего правого с удалением лакокрасочного покрытия", 
                                "Ремонт крыла переднего правого с удалением лакокрасочного покрытия", "work", $services_data);
                            render_service(1827, "Замена брызговика переднего левого (метал)", 
                                "Замена брызговика переднего левого (метал)", "work", $services_data);
                            render_service(1830, "Замена брызговика переднего правого (метал)", 
                                "Замена брызговика переднего правого (метал)", "work", $services_data);
                            render_service(1833, "Замена усилителя крыла переднего левого", 
                                "Замена усилителя крыла переднего левого", "work", $services_data);
                            render_service(1836, "Замена усилителя крыла переднего правого", 
                                "Замена усилителя крыла переднего правого", "work", $services_data);
                            render_service(1839, "Ремонт усилителя крыла переднего левого", 
                                "Ремонт усилителя крыла переднего левого", "work", $services_data);
                            render_service(1842, "Ремонт усилителя крыла переднего правого", 
                                "Ремонт усилителя крыла переднего правого", "work", $services_data);
                            ?>

                            </div>
                        </div>
                    </div>

                    <div class="collapsible-container">
                        <div class="collapsible-header">
                            <label class="form-label"> Двери передние </label>
                            <span class="collapsible-arrow">↓</span>
                        </div>
                        <div class="collapsible-content">
                            <div class="wrapper">

                            <?php
                            render_service(2103, "Снятие установка двери передней левой", 
                                "Снятие установка двери передней левой", "work", $services_data);
                            render_service(2106, "Снятие установка двери передней правой", 
                                "Снятие установка двери передней правой", "work", $services_data);
                            render_service(2109, "Замена двери передней левой", 
                                "Замена двери передней левой", "work", $services_data);
                            render_service(2112, "Замена двери передней правой", 
                                "Замена двери передней правой", "work", $services_data);
                            render_service(2115, "Ремонт двери передней левой", 
                                "Ремонт двери передней левой", "work", $services_data);
                            render_service(2118, "Ремонт двери передней правой", 
                                "Ремонт двери передней правой", "work", $services_data);
                            render_service(2121, "Ремонт двери передней левой без удаления лакокрасочного покрытия", 
                                "Ремонт двери передней левой без удаления лакокрасочного покрытия", "work", $services_data);
                            render_service(2124, "Ремонт двери передней правой без удаления лакокрасочного покрытия", 
                                "Ремонт двери передней правой без удаления лакокрасочного покрытия", "work", $services_data);
                            render_service(2127, "Ремонт двери передней левой с удалением лакокрасочного покрытия", 
                                "Ремонт двери передней левой с удалением лакокрасочного покрытия", "work", $services_data);
                            render_service(2130, "Ремонт двери передней правой с удалением лакокрасочного покрытия", 
                                "Ремонт двери передней правой с удалением лакокрасочного покрытия", "work", $services_data);
                            render_service(2133, "Замена шарниров двери передней левой", 
                                "Замена шарниров двери передней левой", "work", $services_data);
                            render_service(2136, "Замена шарниров двери передней правой", 
                                "Замена шарниров двери передней правой", "work", $services_data);
                            ?>

                            </div>
                        </div>
                    </div>

                    <div class="collapsible-container">
                        <div class="collapsible-header">
                            <label class="form-label"> Двери задние </label>
                            <span class="collapsible-arrow">↓</span>
                        </div>
                        <div class="collapsible-content">
                            <div class="wrapper">

                            <?php
                            render_service(2403, "Снятие установка двери задней левой", 
                                "Снятие установка двери задней левой", "work", $services_data);
                            render_service(2406, "Снятие установка двери задней правой", 
                                "Снятие установка двери задней правой", "work", $services_data);
                            render_service(2409, "Замена двери задней левой", 
                                "Замена двери задней левой", "work", $services_data);
                            render_service(2412, "Замена двери задней правой", 
                                "Замена двери задней правой", "work", $services_data);
                            render_service(2415, "Ремонт двери задней левой", 
                                "Ремонт двери задней левой", "work", $services_data);
                            render_service(2418, "Ремонт двери задней правой", 
                                "Ремонт двери задней правой", "work", $services_data);
                            render_service(2421, "Ремонт двери задней левой без удаления лакокрасочного покрытия", 
                                "Ремонт двери задней левой без удаления лакокрасочного покрытия", "work", $services_data);
                            render_service(2424, "Ремонт двери задней правой без удаления лакокрасочного покрытия", 
                                "Ремонт двери задней правой без удаления лакокрасочного покрытия", "work", $services_data);
                            render_service(2427, "Ремонт двери задней левой с удалением лакокрасочного покрытия", 
                                "Ремонт двери задней левой с удалением лакокрасочного покрытия", "work", $services_data);
                            render_service(2430, "Ремонт двери задней правой с удалением лакокрасочного покрытия", 
                                "Ремонт двери задней правой с удалением лакокрасочного покрытия", "work", $services_data);
                            render_service(2433, "Замена шарниров двери задней левой", 
                                "Замена шарниров двери задней левой", "work", $services_data);
                            render_service(2436, "Замена шарниров двери задней правой", 
                                "Замена шарниров двери задней правой", "work", $services_data);
                            ?>

                            </div>
                        </div>
                    </div>

                    <div class="collapsible-container">
                        <div class="collapsible-header">
                            <label class="form-label"> Крылья задние </label>
                            <span class="collapsible-arrow">↓</span>
                        </div>
                        <div class="collapsible-content">
                            <div class="wrapper">

                            <?php
                            render_service(2703, "Замена крыла заднего левого", 
                                "Замена крыла заднего левого", "work", $services_data);
                            render_service(2706, "Замена крыла заднего правого", 
                                "Замена крыла заднего правого", "work", $services_data);
                            render_service(2709, "Ремонт крыла заднего левого", 
                                "Ремонт крыла заднего левого", "work", $services_data);
                            render_service(2712, "Ремонт крыла заднего правого", 
                                "Ремонт крыла заднего правого", "work", $services_data);
                            render_service(2715, "Ремонт крыла заднего левого без удаления лакокрасочного покрытия", 
                                "Ремонт крыла заднего левого без удаления лакокрасочного покрытия", "work", $services_data);
                            render_service(2718, "Ремонт крыла заднего правого без удаления лакокрасочного покрытия", 
                                "Ремонт крыла заднего правого без удаления лакокрасочного покрытия", "work", $services_data);
                            render_service(2721, "Ремонт крыла заднего левого с удалением лакокрасочного покрытия", 
                                "Ремонт крыла заднего левого с удалением лакокрасочного покрытия", "work", $services_data);
                            render_service(2724, "Ремонт крыла заднего правого с удалением лакокрасочного покрытия", 
                                "Ремонт крыла заднего правого с удалением лакокрасочного покрытия", "work", $services_data);
                            render_service(2727, "Замена наружной арки крыла задней левой", 
                                "Замена наружной арки крыла задней левой", "work", $services_data);
                            render_service(2730, "Замена наружной арки крыла задней правой", 
                                "Замена наружной арки крыла задней правой", "work", $services_data);
                            render_service(2733, "Замена внутренней арки крыла задней левой", 
                                "Замена внутренней арки крыла задней левой", "work", $services_data);
                            render_service(2736, "Замена внутренней арки крыла задней правой", 
                                "Замена внутренней арки крыла задней правой", "work", $services_data);
                            render_service(2739, "Ремонт наружной арки крыла задней левой", 
                                "Ремонт наружной арки крыла задней левой", "work", $services_data);
                            render_service(2742, "Ремонт наружной арки крыла задней правой", 
                                "Ремонт наружной арки крыла задней правой", "work", $services_data);
                            render_service(2745, "Ремонт внутренней арки крыла задней левой", 
                                "Ремонт внутренней арки крыла задней левой", "work", $services_data);
                            render_service(2748, "Ремонт внутренней арки крыла задней правой", 
                                "Ремонт внутренней арки крыла задней правой", "work", $services_data);
                            ?>

                            </div>
                        </div>
                    </div>

                    <div class="collapsible-container">
                        <div class="collapsible-header">
                            <label class="form-label"> Крышка багажника (задняя дверь) </label>
                            <span class="collapsible-arrow">↓</span>
                        </div>
                        <div class="collapsible-content">
                            <div class="wrapper">

                            <?php
                            render_service(3003, "Снятие, установка крышки багажника", 
                                "Снятие, установка крышки багажника", "work", $services_data);
                            render_service(3006, "Снятие, установка задней двери", 
                                "Снятие, установка задней двери", "work", $services_data);
                            render_service(3009, "Замена крышки багажника", 
                                "Замена крышки багажника", "work", $services_data);
                            render_service(3012, "Замена задней двери", 
                                "Замена задней двери", "work", $services_data);
                            render_service(3015, "Ремонт крышки багажника без удаления лакокрасочного покрытия", 
                                "Ремонт крышки багажника без удаления лакокрасочного покрытия", "work", $services_data);
                            render_service(3018, "Ремонт задней двери без удаления лакокрасочного покрытия", 
                                "Ремонт задней двери без удаления лакокрасочного покрытия", "work", $services_data);
                            render_service(3021, "Ремонт крышки багажника с удалением лакокрасочного покрытия", 
                                "Ремонт крышки багажника с удалением лакокрасочного покрытия", "work", $services_data);
                            render_service(3024, "Ремонт задней двери с удалением лакокрасочного покрытия", 
                                "Ремонт задней двери с удалением лакокрасочного покрытия", "work", $services_data);
                            render_service(3027, "Замена задней панели", 
                                "Замена задней панели", "work", $services_data);
                            render_service(3030, "Ремонт задней панели", 
                                "Ремонт задней панели", "work", $services_data);
                            render_service(3033, "Замена обоих шарниров багажника", 
                                "Замена обоих шарниров багажника", "work", $services_data);
                            render_service(3036, "Замена обоих шарниров задней двери", 
                                "Замена обоих шарниров задней двери", "work", $services_data);
                            render_service(3039, "Замена шарнира багажника левого", 
                                "Замена шарнира багажника левого", "work", $services_data);
                            render_service(3042, "Замена шарнира багажника правого", 
                                "Замена шарнира багажника правого", "work", $services_data);
                            render_service(3045, "Замена шарнира задней двери левого", 
                                "Замена шарнира задней двери левого", "work", $services_data);
                            render_service(3048, "Замена шарнира задней двери правого", 
                                "Замена шарнира задней двери правого", "work", $services_data);
                            render_service(3051, "Замена внутренней части багажного отсека (без гарантии)", 
                                "Замена внутренней части багажного отсека (без гарантии)", "work", $services_data);
                            render_service(3054, "Замена задней панели с внутренней частью багажного отсека", 
                                "Замена задней панели с внутренней частью багажного отсека", "work", $services_data);
                            ?>

                            </div>
                        </div>
                    </div>

                    <div class="collapsible-container">
                        <div class="collapsible-header">
                            <label class="form-label"> Бампер задний </label>
                            <span class="collapsible-arrow">↓</span>
                        </div>
                        <div class="collapsible-content">
                            <div class="wrapper">

                            <?php
                            render_service(3303, "Снятие, установка заднего бампера", 
                                "Снятие, установка заднего бампера", "work", $services_data);
                            render_service(3306, "Мелкий ремонт заднего бампера", 
                                "Мелкий ремонт заднего бампера", "work", $services_data);
                            render_service(3309, "Ремонт бампера заднего без удаления лакокрасочного покрытия", 
                                "Ремонт бампера заднего без удаления лакокрасочного покрытия", "work", $services_data);
                            render_service(3312, "Ремонт бампера заднего с удалением лакокрасочного покрытия", 
                                "Ремонт бампера заднего с удалением лакокрасочного покрытия", "work", $services_data);
                            render_service(3315, "Изготовление отверстий в заднем бампере под сонары", 
                                "Изготовление отверстий в заднем бампере под сонары", "work", $services_data);
                            ?>

                            </div>
                        </div>
                    </div>

                    <div class="collapsible-container">
                        <div class="collapsible-header">
                            <label class="form-label"> Светотехника </label>
                            <span class="collapsible-arrow">↓</span>
                        </div>
                        <div class="collapsible-content">
                            <div class="wrapper">

                            <?php
                            render_service(3603, "Замена фар головного света", 
                                "Замена фар головного света", "work", $services_data);
                            render_service(3606, "Замена противотуманных фар", 
                                "Замена противотуманных фар", "work", $services_data);
                            render_service(3609, "Замена фары головного света передней левой", 
                                "Замена фары головного света передней левой", "work", $services_data);
                            render_service(3612, "Замена фары головного света передней правой", 
                                "Замена фары головного света передней правой", "work", $services_data);
                            render_service(3615, "Замена фары противотуманной левой", 
                                "Замена фары противотуманной левой", "work", $services_data);
                            render_service(3618, "Замена фары противотуманной правой", 
                                "Замена фары противотуманной правой", "work", $services_data);
                            render_service(3621, "Замена указателя поворота переднего левого", 
                                "Замена указателя поворота переднего левого", "work", $services_data);
                            render_service(3624, "Замена указателя поворота переднего правого", 
                                "Замена указателя поворота переднего правого", "work", $services_data);
                            render_service(3627, "Замена фары задней левой", 
                                "Замена фары задней левой", "work", $services_data);
                            render_service(3630, "Замена фары задней правой", 
                                "Замена фары задней правой", "work", $services_data);
                            ?>

                            </div>
                        </div>
                    </div>

                    <div class="collapsible-container">
                        <div class="collapsible-header">
                            <label class="form-label"> Стекла </label>
                            <span class="collapsible-arrow">↓</span>
                        </div>
                        <div class="collapsible-content">
                            <div class="wrapper">

                            <?php
                            render_service(3903, "Замена лобового стекла", 
                                "Замена лобового стекла", "work", $services_data);
                            render_service(3906, "Замена заднего стекла", 
                                "Замена заднего стекла", "work", $services_data);
                            render_service(3909, "Замена бокового стекла двери передней левой", 
                                "Замена бокового стекла двери передней левой", "work", $services_data);
                            render_service(3912, "Замена бокового стекла двери передней правой", 
                                "Замена бокового стекла двери передней правой", "work", $services_data);
                            render_service(3915, "Замена бокового стекла двери задней левой", 
                                "Замена бокового стекла двери задней левой", "work", $services_data);
                            render_service(3918, "Замена бокового стекла двери задней правой", 
                                "Замена бокового стекла двери задней правой", "work", $services_data);
                            render_service(3921, "Замена стекла форточки задней левой", 
                                "Замена стекла форточки задней левой", "work", $services_data);
                            render_service(3924, "Замена стекла форточки задней правой", 
                                "Замена стекла форточки задней правой", "work", $services_data);
                            render_service(3927, "Врезка форточки задней левой с последующей установкой", 
                                "Врезка форточки задней левой с последующей установкой", "work", $services_data);
                            render_service(3930, "Врезка форточки задней правой с последующей установкой", 
                                "Врезка форточки задней правой с последующей установкой", "work", $services_data);
                            ?>

                            </div>
                        </div>
                    </div>

                    <div class="title">
                        <h4> 3. Запасные части и расходные материалы: </h4>
                    </div>

                    <div class="collapsible-container">
                        <div class="collapsible-header">
                            <label class="form-label"> Бампера, решетка радиатора </label>
                            <span class="collapsible-arrow">↓</span>
                        </div>
                        <div class="collapsible-content">
                            <div class="wrapper">
                            
                            <?php
                            render_service(6003, "Бампер передний", 
                                "Бампер передний", "parts", $services_data);
                            render_service(6006, "Бампер задний", 
                                "Бампер задний", "parts", $services_data);
                            render_service(6009, "Решетка радиатора", 
                                "Решетка радиатора", "parts", $services_data);
                            ?>

                            </div>
                        </div>
                    </div>

                    <div class="collapsible-container">
                        <div class="collapsible-header">
                            <label class="form-label"> Капот </label>
                            <span class="collapsible-arrow">↓</span>
                        </div>
                        <div class="collapsible-content">
                            <div class="wrapper">
                            
                            <?php
                            render_service(6303, "Шарнир капота левый", 
                                "Шарнир капота левый", "parts", $services_data);
                            render_service(6306, "Шарнир капота правый", 
                                "Шарнир капота правый", "parts", $services_data);
                            render_service(6309, "Капот", 
                                "Капот", "parts", $services_data);
                            ?>

                            </div>
                        </div>
                    </div>

                    <div class="collapsible-container">
                        <div class="collapsible-header">
                            <label class="form-label"> Моторный отсек </label>
                            <span class="collapsible-arrow">↓</span>
                        </div>
                        <div class="collapsible-content">
                            <div class="wrapper">
                            
                            <?php
                            render_service(6603, "Нижний брус", 
                                "Нижний брус", "parts", $services_data);
                            render_service(6606, "Передняя панель радиатора", 
                                "Передняя панель радиатора", "parts", $services_data);
                            render_service(6609, "Лонжерон передний левый", 
                                "Лонжерон передний левый", "parts", $services_data);
                            render_service(6612, "Лонжерон передний правый", 
                                "Лонжерон передний правый","parts", $services_data);
                            render_service(6615, "Моторный щит", 
                                "Моторный щит", "parts", $services_data);
                            ?>

                            </div>
                        </div>
                    </div>

                    <div class="collapsible-container">
                        <div class="collapsible-header">
                            <label class="form-label"> Крылья </label>
                            <span class="collapsible-arrow">↓</span>
                        </div>
                        <div class="collapsible-content">
                            <div class="wrapper">
                            
                            <?php
                            render_service(6903, "Крыло переднее левое", 
                                "Крыло переднее левое", "parts", $services_data);
                            render_service(6906, "Крыло переднее правое", 
                                "Крыло переднее правое", "parts", $services_data);
                            render_service(6909, "Крыло заднее левое", 
                                "Крыло заднее левое", "parts", $services_data);
                            render_service(6912, "Крыло заднее правое", 
                                "Крыло заднее правое","parts", $services_data);
                            render_service(6915, "Брызговик передний левый (метал)", 
                                "Брызговик передний левый (метал)", "parts", $services_data);
                            render_service(6918, "Брызговик передний правый (метал)", 
                                "Брызговик передний правый (метал)", "parts", $services_data);
                            render_service(6921, "Усилитель крыла передний левый", 
                                "Усилитель крыла передний левый", "parts", $services_data);
                            render_service(6924, "Усилитель крыла передний правый", 
                                "Усилитель крыла передний правый","parts", $services_data);
                            render_service(6927, "Наружная арка крыла заднего левого", 
                                "Наружная арка крыла заднего левого", "parts", $services_data);
                            render_service(6930, "Наружная арка крыла заднего правого", 
                                "Наружная арка крыла заднего правого", "parts", $services_data);
                            render_service(6933, "Внутренняя арка крыла заднего левого", 
                                "Внутренняя арка крыла заднего левого", "parts", $services_data);
                            render_service(6936, "Внутренняя арка крыла заднего правого", 
                                "Внутренняя арка крыла заднего правого", "parts", $services_data);
                            ?>

                            </div>
                        </div>
                    </div>

                    <div class="collapsible-container">
                        <div class="collapsible-header">
                            <label class="form-label"> Двери </label>
                            <span class="collapsible-arrow">↓</span>
                        </div>
                        <div class="collapsible-content">
                            <div class="wrapper">
                            
                            <?php
                            render_service(7203, "Дверь передняя левая", 
                                "Дверь передняя левая", "parts", $services_data);
                            render_service(7206, "Дверь передняя правая", 
                                "Дверь передняя правая", "parts", $services_data);
                            render_service(7209, "Дверь задняя левая", 
                                "Дверь задняя левая", "parts", $services_data);
                            render_service(7212, "Дверь задняя правая", 
                                "Дверь задняя правая","parts", $services_data);
                            render_service(7215, "Средняя стойка кузова левая", 
                                "Средняя стойка кузова левая", "parts", $services_data);
                            render_service(7218, "Средняя стойка кузова правая", 
                                "Средняя стойка кузова правая", "parts", $services_data);
                            render_service(7221, "Порог двери левый", 
                                "Порог двери левый", "parts", $services_data);
                            render_service(7224, "Порог двери правый", 
                                "Порог двери правый","parts", $services_data);
                            render_service(7227, "Усилитель левого порога", 
                                "Усилитель левого порога", "parts", $services_data);
                            render_service(7230, "Усилитель правого порога", 
                                "Усилитель правого порога", "parts", $services_data);
                            render_service(7233, "Шарнир двери передней левой", 
                                "Шарнир двери передней левой", "parts", $services_data);
                            render_service(7236, "Шарнир двери передней правой", 
                                "Шарнир двери передней правой","parts", $services_data);
                            render_service(7239, "Шарнир двери задней левой", 
                                "Шарнир двери задней левой", "parts", $services_data);
                            render_service(7242, "Шарнир двери задней правой", 
                                "Шарнир двери задней правой", "parts", $services_data);
                            ?>

                            </div>
                        </div>
                    </div>

                    <div class="collapsible-container">
                        <div class="collapsible-header">
                            <label class="form-label"> Крышка багажника (задняя дверь) </label>
                            <span class="collapsible-arrow">↓</span>
                        </div>
                        <div class="collapsible-content">
                            <div class="wrapper">
                            
                            <?php
                            render_service(7503, "Крышка багажника", 
                                "Крышка багажника", "parts", $services_data);
                            render_service(7506, "Задняя дверь", 
                                "Задняя дверь", "parts", $services_data);
                            render_service(7509, "Шарнир багажника левый", 
                                "Шарнир багажника левый", "parts", $services_data);
                            render_service(7512, "Шарнир багажника правый", 
                                "Шарнир багажника правый","parts", $services_data);
                            render_service(7515, "Шарнир задней двери левый", 
                                "Шарнир задней двери левый", "parts", $services_data);
                            render_service(7518, "Шарнир задней двери правый", 
                                "Шарнир задней двери правый", "parts", $services_data);
                            render_service(7521, "Задняя панель", 
                                "Задняя панель", "parts", $services_data);
                            render_service(7524, "Внутренняя часть багажного отсека", 
                                "Внутренняя часть багажного отсека", "parts", $services_data);
                            ?>

                            </div>
                        </div>
                    </div>

                    <div class="collapsible-container">
                        <div class="collapsible-header">
                            <label class="form-label"> Светотехника </label>
                            <span class="collapsible-arrow">↓</span>
                        </div>
                        <div class="collapsible-content">
                            <div class="wrapper">
                            
                            <?php
                            render_service(7803, "Фара головного света передняя левая", 
                                "Фара головного света передняя левая", "parts", $services_data);
                            render_service(7806, "Фара головного света передняя правая", 
                                "Фара головного света передняя правая", "parts", $services_data);
                            render_service(7809, "Противотуманная фара левая", 
                                "Противотуманная фара левая", "parts", $services_data);
                            render_service(7812, "Противотуманная фара правая", 
                                "Противотуманная фара правая","parts", $services_data);
                            render_service(7815, "Указатель поворота левый", 
                                "Указатель поворота левый", "parts", $services_data);
                            render_service(7818, "Указатель поворота правый", 
                                "Указатель поворота правый", "parts", $services_data);
                            render_service(7821, "Повторитель указателя поворота левый", 
                                "Повторитель указателя поворота левый", "parts", $services_data);
                            render_service(7824, "Повторитель указателя поворота правый", 
                                "Повторитель указателя поворота правый", "parts", $services_data);
                            render_service(7827, "Фара задняя левая", 
                                "Фара задняя левая", "parts", $services_data);
                            render_service(7830, "Фара задняя правая", 
                                "Фара задняя правая", "parts", $services_data);
                            ?>

                            </div>
                        </div>
                    </div>
                         
                    <div class="collapsible-container">
                        <div class="collapsible-header">
                            <label class="form-label"> Стекла </label>
                            <span class="collapsible-arrow">↓</span>
                        </div>
                        <div class="collapsible-content">
                            <div class="wrapper">
                            
                            <?php
                            render_service(8103, "Стекло лобовое", 
                                "Стекло лобовое", "parts", $services_data);
                            render_service(8106, "Стекло заднее", 
                                "Стекло заднее", "parts", $services_data);
                            render_service(8109, "Стекло двери передней левой", 
                                "Стекло двери передней левой", "parts", $services_data);
                            render_service(8112, "Стекло двери передней правой", 
                                "Стекло двери передней правой","parts", $services_data);
                            render_service(8115, "Стекло двери задней левой", 
                                "Стекло двери задней левой", "parts", $services_data);
                            render_service(8118, "Стекло двери задней правой", 
                                "Стекло двери задней правой", "parts", $services_data);
                            render_service(8121, "Стекло форточки задней левой", 
                                "Стекло форточки задней левой", "parts", $services_data);
                            render_service(8124, "Стекло форточки задней правой", 
                                "Стекло форточки задней правой","parts", $services_data);
                            ?>

                            </div>
                        </div>
                    </div>
                        
                    <!-- Итоговая сумма, отображаемая на экране -->
                    <!-- Отображаем сумму для пользователя -->
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

        <script src="script.js"></script>
        
        <script>
            // Поиск всех заголовков раскрывающихся блоков
            const collapsibles = document.querySelectorAll('.collapsible-header');
        
            // Добавляем обработчик событий для каждого заголовка
            collapsibles.forEach(collapsible => {
                collapsible.addEventListener('click', () => {
                    const container = collapsible.parentElement;
                    container.classList.toggle('open');
                });
            });
        </script>

        <script>
            // Функция для обработки ошибки
            function handleInputValidation(event) {
                const input = event.target;
                const errorMessage = input.closest('.customer').querySelector(`.error-message[data-for="${input.id}"]`);
        
                // Проверяем поле на валидность
                if (!input.validity.valid) {
                    // Меняем текст placeholder на ошибку
                    input.placeholder = input.dataset.defaultPlaceholder;  // Оставляем текст как был
                    // Изменяем цвет placeholder
                    input.classList.add("error-placeholder");  // Добавляем класс для изменения цвета placeholder
                    // Добавляем класс для отображения ошибки
                    input.classList.add("error");
                    // Показываем текст ошибки
                    errorMessage.style.visibility = 'visible';
                }
            else {
                    // Восстанавливаем стандартный фон
                    input.style.backgroundColor = "";
                    // Восстанавливаем исходный placeholder
                    input.placeholder = input.dataset.defaultPlaceholder;
                    // Убираем класс ошибки
                    input.classList.remove("error");
                    // Скрываем текст ошибки
                    errorMessage.style.visibility = 'hidden';
                }
            }
        
            // Получаем все поля ввода
            const inputs = document.querySelectorAll('.user_input');
        
            // Устанавливаем дефолтный placeholder в data-атрибут
            inputs.forEach(input => {
                input.dataset.defaultPlaceholder = input.placeholder;
                input.addEventListener('blur', handleInputValidation);
            });
        </script>

        <script>
            document.querySelector('.btn-reset').addEventListener('mouseover', function() {
                document.querySelector('.btn-save').classList.add('hover-effect');
            });

            document.querySelector('.btn-reset').addEventListener('mouseout', function() {
                document.querySelector('.btn-save').classList.remove('hover-effect');
            });
        </script>
        
    </body>
</html>
<?php $conn->close(); ?>