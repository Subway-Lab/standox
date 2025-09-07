<?php
    set_time_limit(600); // NOTE: Увиличение времени запроса до 10 минут

    require_once(__DIR__ . '/features/auth/check.php'); // NOTE: Проверка авторизации пользователя
?>

<!DOCTYPE HTML>
<html lang="ru">

    <?php include __DIR__ . '/shared/head.php'; ?>

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

        <div class="form heavy">
            <form id="orderForm" action="submit_order.php" method="POST">
                <div class="title">
                    <h2> Создание нового заказ-наряда </h2>
                </div>

                <div class="title">
                    <h3> 1. Данные о заказчике: </h3>
                </div>
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

                <!-- NOTE: Итоговая сумма, на экране -->
                <div class="title">
                    <h3> Итого: <span id="totalPrice">0</span> руб. </h3>
                </div>
                <!-- NOTE: Поле для отправки суммы. Сейчас оно текстовое, чтобы видеть значение -->
                <input type="hidden" id="total_price_hidden" name="total_price" value="0" readonly>

                <button type="submit" class="btn btn-success"> ОФОРМИТЬ ЗАКАЗ </button>
            </form>
        </div>

        <?php include 'shared/footer.php'; ?>

        <script src="index.js?v=<?php echo $version; ?>" defer></script>
    </body>
</html>