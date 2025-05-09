<?php
// NOTE: Проверка авторизации пользователя
require_once('auth_check.php');
?>

<!DOCTYPE HTML>
<html lang="ru">
    <head>
        <meta charset="utf-8">
        <meta name="keywords" content="key words">
        <meta name="description" content="description of the page SEO">
        <title> STANDOX </title>
        <link rel="icon" href="files/favicon.ico" type="image/x-icon">
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
                    <li><a href="logout.php" class="menu_link"> выйти </a></li>
                </ul>
            </nav>
        </header>

        <div class="title">
            <h2> Создание нового заказ-наряда </h2>
            <h3> 1. Данные о заказчике: </h3>
        </div>

        <div class="form">
            <form id="orderForm" action="submit_order.php" method="POST">

                <div class="customer">
                    <label for="surname" class="sr-only"> Фамилия </label>
                    <input class="user_input" id="surname" type="text" name="surname" placeholder="Фамилия *" pattern="^[а-яА-ЯёЁ\-]+$" title="Укажите Фамилию" required>

                    <label for="name" class="sr-only"> Имя </label>
                    <input class="user_input" id="name" type="text" name="name" placeholder="Имя *" pattern="^[а-яА-ЯёЁ\-]+$" title="Укажите Имя" required>

                    <div class="error-message" data-for="surname"> Допустимые символы: буквы кириллицы, знак тире </div>
                    <div class="error-message" data-for="name"> Допустимые символы: буквы кириллицы, знак тире </div>

                    <label for="patronymic" class="sr-only"> Отчество </label>
                    <input class="user_input" id="patronymic" type="text" name="patronymic" placeholder="Отчество" pattern="^[а-яА-ЯёЁ\s\-]+$" title="Укажите Отчествo">

                    <label for="phone" class="sr-only"> Контактный телефон </label>
                    <input class="user_input" id="phone" type="tel" name="phone" placeholder="Номер телефона *" pattern="^[0-9\+\-\s]+$" title="Укажите Номер телефона" required>

                    <div class="error-message" data-for="patronymic"> Допустимые символы: буквы кириллицы, знак тире, пробел </div>
                    <div class="error-message" data-for="phone"> Допустимые символы: цифры, знак "+", тире, пробел </div>

                    <label for="car_model" class="sr-only"> Марка автомобиля </label>
                    <input class="user_input" id="car_model" type="text" name="car_model" placeholder="Марка автомобиля *" pattern="^[а-яА-ЯёЁa-zA-Z0-9\-\s]+$" title="Укажите Марку автомобиля" required>

                    <label for="car_number" class="sr-only"> Регистрационный знак </label>
                    <input class="user_input" id="car_number" type="text" name="car_number" placeholder="Регистрационный знак *" pattern="^[а-яА-ЯёЁa-zA-Z0-9\-\s]+$" title="Укажите Гос. номер" required>

                    <div class="error-message" data-for="car_model"> Допустимые символы: буквы кириллицы, латиницы, знак тире, пробел </div>
                    <div class="error-message" data-for="car_number"> Допустимые символы: буквы кириллицы, латиницы, знак тире, пробел </div>
                </div>
                <p class="asterisk"> * поля обязательные для заполнения </p>

                <div class="title">
                    <h4> 2. Наименование выполняемых работ: </h4>
                </div>

                <?php
                $services = require_once 'works.php';

                foreach ($services as $section) {
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
                        echo '
                                <div class="service-item">
                                    <input type="checkbox" class="service-checkbox" id="'.$serviceId.'" 
                                        data-service-name="'.$item['name'].'">
                                    <label for="'.$serviceId.'" class="checkbox-btn">'.$item['label'].'</label>
                                    <input type="number" class="service-cost" id="'.$serviceId.'-cost" placeholder="0.00" disabled>
                                    <input type="hidden" name="'.$serviceId.'_name" id="'.$serviceId.'-name" value="'.$item['name'].'">
                                    <input type="hidden" name="'.$serviceId.'_section" value="'.$section['section'].'">
                                    <input type="hidden" name="'.$serviceId.'_service_id" value="'.$item['id'].'">
                                    <input type="hidden" name="'.$serviceId.'_price" id="'.$serviceId.'-price-hidden" value="0">
                                </div>';
                    }
                    
                    echo '
                            </div>
                        </div>
                    </div>';
                }
                ?>

                <!-- Итоговая сумма, отображаемая на экране -->
                <!-- Отображаем сумму для пользователя -->
                <div class="title">
                    <h4> Итого: <span id="totalPrice">0</span> руб. </h4>
                </div>
                <!-- Поле для отправки суммы. Сейчас оно текстовое, чтобы видеть значение -->
                <input type="hidden" id="total_price_hidden" name="total_price" value="0" readonly>

                <button type="submit" class="btn btn-success"> ОФОРМИТЬ ЗАКАЗ </button>
            </form>
        </div>

        <?php include 'footer.php'; ?>

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
