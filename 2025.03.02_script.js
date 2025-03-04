document.addEventListener("DOMContentLoaded", function () {
    const totalPriceElement = document.getElementById("totalPrice");
    const totalPriceHidden = document.getElementById("total_price_hidden");
    const orderForm = document.getElementById("orderForm");

    // Функция активации поля ввода стоимости
    function toggleCostInput(checkbox, costInput) {
        if (checkbox.checked) {
            costInput.disabled = false;
            costInput.focus();
        } else {
            costInput.disabled = true;
            costInput.value = "";
            costInput.style.borderColor = ""; // Сброс цвета границы при отключении
        }
    }

    // Функция проверки заполненности поля стоимости
    function validateCostInput(costInput) {
        if (costInput.disabled) return true; // Поле отключено, проверка не требуется
        const value = parseFloat(costInput.value);
        if (isNaN(value) || value < 1) {
            costInput.style.borderColor = "red"; // Устанавливаем красную границу
            return false;
        } else {
            costInput.style.borderColor = ""; // Сбрасываем цвет границы
            return true;
        }
    }

    // Обработка изменения состояния чекбоксов
    const serviceCheckboxes = document.querySelectorAll(".service-checkbox");
    serviceCheckboxes.forEach(checkbox => {
        const costInput = checkbox.parentElement.querySelector(".service-cost");
        checkbox.addEventListener("change", function () {
            toggleCostInput(this, costInput);
            updateTotal();
        });
    });

    // Обработка ввода стоимости
    const serviceCostInputs = document.querySelectorAll(".service-cost");
    serviceCostInputs.forEach(input => {
        input.addEventListener("input", function () {
            updateTotal();
        });

        // Отключаем изменение значения при прокрутке колесиком мыши или тачпадом
        input.addEventListener("wheel", function (event) {
            event.preventDefault(); // Блокируем стандартное поведение
        });
    });

    // Функция пересчёта итоговой суммы
    function updateTotal() {
        let total = 0;
        document.querySelectorAll(".service-item").forEach(item => {
            const checkbox = item.querySelector(".service-checkbox");
            const costInput = item.querySelector(".service-cost");
            if (checkbox.checked && costInput.value) {
                total += parseFloat(costInput.value) || 0;
            }
        });
        const totalRounded = Math.floor(total);
        totalPriceElement.textContent = total.toLocaleString();
        totalPriceHidden.value = totalRounded;
    }

    // Функция проверки всех полей перед отправкой формы
    function validateForm() {
        let isValid = true;
        let hasCheckedServices = false; // Флаг для проверки наличия хотя бы одного выбранного чекбокса

        document.querySelectorAll(".service-item").forEach(item => {
            const checkbox = item.querySelector(".service-checkbox");
            const costInput = item.querySelector(".service-cost");

            if (checkbox.checked) {
                hasCheckedServices = true; // Есть хотя бы один выбранный чекбокс
                if (!validateCostInput(costInput)) {
                    isValid = false; // Если поле стоимости не заполнено, форма невалидна
                }
            }
        });

        if (!hasCheckedServices) {
            alert("Пожалуйста, выберите хотя бы одну услугу.");
            isValid = false;
        } else if (!isValid) {
            alert("Пожалуйста, укажите стоимость выбранных Вами работ.");
        }

        return isValid;
    }

    // Обработка отправки формы
    orderForm.addEventListener("submit", function (event) {
        if (!validateForm()) {
            event.preventDefault(); // Отмена отправки формы при наличии ошибок
            return;
        }

        // Пересчитываем итоговую сумму перед отправкой
        updateTotal();

        // Удаляем старые скрытые поля с услугами
        document.querySelectorAll("input[name='services_details[]']").forEach(input => input.remove());

        // Собираем данные по услугам
        let serviceCount = 0;
        document.querySelectorAll(".service-item").forEach(item => {
            const checkbox = item.querySelector(".service-checkbox");
            const costInput = item.querySelector(".service-cost");
            if (checkbox.checked && costInput.value) {
                const serviceName = checkbox.getAttribute("data-service-name");
                const cost = costInput.value;
                const detail = serviceName + " " + cost + " руб.";

                // Добавляем скрытое поле с данными для каждой услуги
                const hiddenInput = document.createElement("input");
                hiddenInput.type = "hidden";
                hiddenInput.name = "services_details[]";
                hiddenInput.value = detail;
                orderForm.appendChild(hiddenInput);

                // Обновляем соответствующее скрытое поле для цены
                const serviceId = checkbox.id;
                const priceHiddenInput = document.getElementById(serviceId + "-price-hidden");
                priceHiddenInput.value = cost; // Обновляем скрытое поле с ценой

                serviceCount++;
            }
        });

        // Добавляем скрытое поле с количеством услуг
        const serviceCountInput = document.createElement("input");
        serviceCountInput.type = "hidden";
        serviceCountInput.name = "service_count";
        serviceCountInput.value = serviceCount; // Записываем количество выбранных услуг
        orderForm.appendChild(serviceCountInput);
    });
});