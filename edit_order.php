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
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.11.5/gsap.min.js"></script>
        <link rel="stylesheet" type="text/css" href="style.css">
    </head>
    <body>

        <header>
            <h1> STANDOX </h1>
            <nav class="menu">
                <ul>
                    <li><a href="admin_orders.php" class="menu_link"> база данных </a></li>
                    <li><a href="registration.php" class="menu_link"> выйте </a></li>
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
                        <h4> Итого: <span id="totalPrice"><?= $order_data['services_total'] ?? 0 ?></span> руб. </h4>
                        <input type="hidden" id="total_price_hidden" name="total_price" 
                            value="<?= $order_data['services_total'] ?? 0 ?>">
                    </div>

                    <div class="form-controls">
                        <button type="submit" class="btn btn-save">Сохранить изменения</button>
                        <button type="reset" class="btn btn-reset">Сбросить изменения</button>
                    </div>
            </form>
        </div>

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
        
    </body>
</html>
<?php $conn->close(); ?>