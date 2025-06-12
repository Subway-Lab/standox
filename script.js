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
        event.preventDefault();
        return;
    }
    updateTotal();

    // Удаляем старые скрытые поля, созданные ранее (имя 'services[...]' или 'services_details[]', в зависимости от схемы).
    // Например, если вы ранее создавали hidden с name="services[...]",
    // то удаляем их:
    document.querySelectorAll("input[name^='services']").forEach(input => input.remove());

    let serviceCount = 0;
    document.querySelectorAll(".service-item").forEach(item => {
        const checkbox = item.querySelector(".service-checkbox");
        const costInput = item.querySelector(".service-cost");
        if (checkbox.checked && costInput.value) {
            const serviceName = checkbox.getAttribute("data-service-name");
            const cost = costInput.value;
            const section = checkbox.getAttribute("data-section");
            const serviceId = checkbox.getAttribute("data-service-id"); // число или строка
            // Создаём hidden для service_id
            const hiddenId = document.createElement("input");
            hiddenId.type = "hidden";
            hiddenId.name = "services[" + serviceCount + "][service_id]";
            hiddenId.value = serviceId;
            orderForm.appendChild(hiddenId);
            // Создаём hidden для name
            const hiddenName = document.createElement("input");
            hiddenName.type = "hidden";
            hiddenName.name = "services[" + serviceCount + "][name]";
            hiddenName.value = serviceName;
            orderForm.appendChild(hiddenName);
            // Создаём hidden для section
            const hiddenSection = document.createElement("input");
            hiddenSection.type = "hidden";
            hiddenSection.name = "services[" + serviceCount + "][section]";
            hiddenSection.value = section;
            orderForm.appendChild(hiddenSection);
            // Создаём hidden для price
            const hiddenPrice = document.createElement("input");
            hiddenPrice.type = "hidden";
            hiddenPrice.name = "services[" + serviceCount + "][price]";
            hiddenPrice.value = cost;
            orderForm.appendChild(hiddenPrice);
            // (Опционально) Создаём hidden для detail, если нужен
            const hiddenDetail = document.createElement("input");
            hiddenDetail.type = "hidden";
            hiddenDetail.name = "services[" + serviceCount + "][detail]";
            hiddenDetail.value = serviceName + " " + cost + " руб.";
            orderForm.appendChild(hiddenDetail);

            serviceCount++;
        }
    });
        // Добавляем hidden service_count, если нужен
        const existingCount = document.querySelector("input[name='service_count']");
        if (existingCount) existingCount.remove();
        const serviceCountInput = document.createElement("input");
        serviceCountInput.type = "hidden";
        serviceCountInput.name = "service_count";
        serviceCountInput.value = serviceCount;
        orderForm.appendChild(serviceCountInput);
        // Теперь форма отправляется с полем services = array of selected services with fields
    });
});