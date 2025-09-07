// NOTE: Объединенный файл функционала формы заказ-наряда
// Объединяет функционал из index_1.js, index_2.js, index_3.js, index_services.js

(function () {
    // NOTE: Инициализация API и базовых переменных
    const base = (window.basePath) || (window.location.pathname.split('/')[1] ? '/' + window.location.pathname.split('/')[1] : '');
    const api = (type) => `${base}/api/services.php?type=${type}`;

    const targets = {
        works: document.getElementById('section-works'),
        painting: document.getElementById('section-painting'),
        parts: document.getElementById('section-parts'),
    };

    const totalPriceElement = document.getElementById("totalPrice");
    const totalPriceHidden = document.getElementById("total_price_hidden");
    const orderForm = document.getElementById("orderForm");

    // NOTE: Простой скелет для загрузки
    const skeleton = () => '<div class="collapsible-container"><div class="collapsible-header"><label class="form-label">Загрузка...</label></div></div>';

    // Инициализация скелетов
    Object.values(targets).forEach(t => { if (t) t.innerHTML = skeleton(); });

    // NOTE: Универсальная функция расчета итоговой суммы
    function updateTotal() {
        if (!totalPriceElement || !totalPriceHidden) return;
        
        let total = 0;
        document.querySelectorAll('.service-item').forEach(item => {
            const checkbox = item.querySelector('.service-checkbox');
            const costInput = item.querySelector('.service-cost');
            if (checkbox && costInput && checkbox.checked && costInput.value) {
                total += parseFloat(costInput.value) || 0;
            }
        });
        
        const totalRounded = Math.floor(total);
        totalPriceElement.textContent = total.toLocaleString();
        totalPriceHidden.value = totalRounded;
    }

    // NOTE: Функция активации поля ввода стоимости
    function toggleCostInput(checkbox, costInput) {
        if (checkbox.checked) {
            costInput.disabled = false;
            costInput.focus();
        } else {
            costInput.disabled = true;
            costInput.value = "";
            costInput.style.borderColor = "";
        }
    }

    // NOTE: Функция проверки заполненности поля стоимости
    function validateCostInput(costInput) {
        if (costInput.disabled) return true;
        const value = parseFloat(costInput.value);
        if (isNaN(value) || value < 1) {
            costInput.style.borderColor = "red";
            return false;
        } else {
            costInput.style.borderColor = "";
            return true;
        }
    }

    // NOTE: Функция проверки всех полей перед отправкой формы
    function validateForm() {
        let isValid = true;
        let hasCheckedServices = false;

        document.querySelectorAll(".service-item").forEach(item => {
            const checkbox = item.querySelector(".service-checkbox");
            const costInput = item.querySelector(".service-cost");

            if (checkbox && checkbox.checked) {
                hasCheckedServices = true;
                if (!validateCostInput(costInput)) {
                    isValid = false;
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

    // NOTE: Обработка ошибок валидации полей ввода клиентских данных
    function handleInputValidation(event) {
        const input = event.target;
        const errorMessage = input.closest('.customer').querySelector(`.error-message[data-for="${input.id}"]`);

        if (!input.validity.valid) {
            input.placeholder = input.dataset.defaultPlaceholder;
            input.classList.add("error-placeholder");
            input.classList.add("error");
            if (errorMessage) errorMessage.style.visibility = 'visible';
        } else {
            input.style.backgroundColor = "";
            input.placeholder = input.dataset.defaultPlaceholder;
            input.classList.remove("error");
            if (errorMessage) errorMessage.style.visibility = 'hidden';
        }
    }

    // NOTE: Делегирование событий для динамически загруженного контента
    function attachDelegates(container) {
        if (!container) return;

        // NOTE: Делегирование: раскрытие/сворачивание блоков
        container.addEventListener('click', (e) => {
            const header = e.target.closest('.collapsible-header');
            if (header && container.contains(header)) {
                const root = header.closest('.collapsible-container');
                if (root) root.classList.toggle('open');
            }
        });

        // NOTE: Делегирование: изменение чекбокса услуг
        container.addEventListener('change', (e) => {
            const cb = e.target.closest('.service-checkbox');
            if (!cb) return;
            const item = cb.closest('.service-item');
            const input = item ? item.querySelector('.service-cost') : null;
            if (!input) return;
            
            toggleCostInput(cb, input);
            updateTotal();
        });

        // NOTE: Делегирование: ввод стоимости услуг
        container.addEventListener('input', (e) => {
            const inp = e.target.closest('.service-cost');
            if (!inp) return;
            
            // NOTE: Нормализация запятой в точку
            if (typeof inp.value === 'string' && inp.value.includes(',')) {
                inp.value = inp.value.replace(',', '.');
            }
            
            // NOTE: Разрешаем только цифры и одну точку
            inp.value = inp.value.replace(/[^0-9.]/g, '');
            const firstDot = inp.value.indexOf('.');
            if (firstDot !== -1) {
                inp.value = inp.value.substring(0, firstDot + 1) + inp.value.substring(firstDot + 1).replace(/\./g, '');
            }
            
            updateTotal();
        });

        // NOTE: Разрешение прокрутки страницы при попадании курсора на поля ввода стоимости
        container.addEventListener('wheel', (e) => {
            const inp = e.target.closest('.service-cost');
            if (inp) {
                // НЕ блокируем прокрутку страницы - позволяем ей работать
                return;
            }
        });
    }

    // NOTE: Рендеринг секции услуг
    function renderSection(container, sections) {
        if (!container) return;
        
        const html = sections.map(section => {
            const itemsHtml = section.items.map((item, index) => {
                const serviceNumber = section.base_id + (index * section.id_step);
                const serviceId = `service${serviceNumber}`;
                return `
                    <div class="service-item">
                        <input type="checkbox" class="service-checkbox" id="${serviceId}"
                            data-service-name="${item.name}" data-section="${section.section}" data-service-id="${serviceNumber}">
                        <label for="${serviceId}" class="checkbox-btn">${item.label}</label>
                        <input type="number" class="service-cost" id="${serviceId}-cost" placeholder="0.00" disabled min="0" step="0.01" inputmode="decimal">
                    </div>
                `;
            }).join('');
            
            return `
                <div class="collapsible-container">
                    <div class="collapsible-header">
                        <label class="form-label">${section.title}</label>
                        <span class="collapsible-arrow">↓</span>
                    </div>
                    <div class="collapsible-content">
                        <div class="wrapper">${itemsHtml}</div>
                    </div>
                </div>
            `;
        }).join('');

        container.innerHTML = html;
        
        // NOTE: Инициализация состояний чекбоксов/инпутов
        container.querySelectorAll('.service-item').forEach(item => {
            const cb = item.querySelector('.service-checkbox');
            const input = item.querySelector('.service-cost');
            if (!cb || !input) return;
            input.disabled = !cb.checked;
            if (!cb.checked) input.value = '';
        });
        
        attachDelegates(container);
        updateTotal();
    }

    // NOTE: Независимая загрузка секций услуг
    async function load(type, target) {
        if (!target) return;
        target.innerHTML = skeleton();
        
        try {
            const res = await fetch(api(type));
            if (!res.ok) throw new Error('Network error');
            const data = await res.json();
            renderSection(target, data);
        } catch (e) {
            target.innerHTML = '<div class="collapsible-container"><div class="collapsible-header"><label class="form-label">Не удалось загрузить данные</label></div></div>';
        }
    }

    // NOTE: Инициализация обработчиков для статических элементов
    function initializeStaticHandlers() {
        // NOTE: Обработка раскрывающихся блоков для статического контента
        const collapsibles = document.querySelectorAll('.collapsible-header');
        collapsibles.forEach(collapsible => {
            collapsible.addEventListener('click', () => {
                const container = collapsible.parentElement;
                container.classList.toggle('open');
            });
        });

        // NOTE: Инициализация валидации полей ввода клиентских данных
        const inputs = document.querySelectorAll('.user_input');
        inputs.forEach(input => {
            input.dataset.defaultPlaceholder = input.placeholder;
            input.addEventListener('blur', handleInputValidation);
        });

        // NOTE: Обработка отправки формы
        if (orderForm) {
            orderForm.addEventListener("submit", function (event) {
                if (!validateForm()) {
                    event.preventDefault();
                    return;
                }
                updateTotal();

                // NOTE: Удаление старых скрытых полей
                document.querySelectorAll("input[name^='services']").forEach(input => input.remove());

                let serviceCount = 0;
                document.querySelectorAll(".service-item").forEach(item => {
                    const checkbox = item.querySelector(".service-checkbox");
                    const costInput = item.querySelector(".service-cost");
                    if (checkbox && costInput && checkbox.checked && costInput.value) {
                        const serviceName = checkbox.getAttribute("data-service-name");
                        const cost = costInput.value;
                        const section = checkbox.getAttribute("data-section");
                        const serviceId = checkbox.getAttribute("data-service-id");

                        // NOTE: Создание скрытых полей для отправки данных услуг
                        const fields = [
                            { name: `services[${serviceCount}][service_id]`, value: serviceId },
                            { name: `services[${serviceCount}][name]`, value: serviceName },
                            { name: `services[${serviceCount}][section]`, value: section },
                            { name: `services[${serviceCount}][price]`, value: cost },
                            { name: `services[${serviceCount}][detail]`, value: `${serviceName} ${cost} руб.` }
                        ];

                        fields.forEach(field => {
                            const hidden = document.createElement("input");
                            hidden.type = "hidden";
                            hidden.name = field.name;
                            hidden.value = field.value;
                            orderForm.appendChild(hidden);
                        });

                        serviceCount++;
                    }
                });

                // NOTE: Добавление счетчика услуг
                const existingCount = document.querySelector("input[name='service_count']");
                if (existingCount) existingCount.remove();
                const serviceCountInput = document.createElement("input");
                serviceCountInput.type = "hidden";
                serviceCountInput.name = "service_count";
                serviceCountInput.value = serviceCount;
                orderForm.appendChild(serviceCountInput);
            });
        }
    }

    // NOTE: Основная инициализация при загрузке DOM
    document.addEventListener("DOMContentLoaded", function () {
        initializeStaticHandlers();
        
        // NOTE: Загрузка всех секций услуг
        load('works', targets.works);
        load('painting', targets.painting);
        load('parts', targets.parts);
    });

})();