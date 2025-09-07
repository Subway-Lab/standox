(function () {
    'use strict';

    // NOTE: Настройки и переменные
    const base = (window.basePath) || (window.location.pathname.split('/')[1] ? '/' + window.location.pathname.split('/')[1] : '');
    const api = (type) => `${base}/api/services.php?type=${type}`;

    const targets = {
        works: document.getElementById('section-works'),
        painting: document.getElementById('section-painting'),
        parts: document.getElementById('section-parts'),
    };

    // NOTE: Функции загрузки и отображения услуг
    const skeleton = () => '<div class="collapsible-container"><div class="collapsible-header"><label class="form-label">Загрузка...</label></div></div>';

    Object.values(targets).forEach(t => { if (t) t.innerHTML = skeleton(); });

    // NOTE: Функция пересчета общей суммы
    function recalcTotal() {
        const totalEl = document.getElementById('totalPrice');
        const hiddenEl = document.getElementById('total_price_hidden');
        if (!totalEl || !hiddenEl) return;
        
        let total = 0;
        document.querySelectorAll('.service-item').forEach(item => {
            const cb = item.querySelector('.service-checkbox');
            const cost = item.querySelector('.service-cost');
            if (cb && cost && cb.checked && cost.value) {
                total += parseFloat(cost.value) || 0;
            }
        });
        
        const totalRounded = Math.floor(total);
        totalEl.textContent = total.toLocaleString();
        hiddenEl.value = totalRounded;
    }

    // NOTE: Делегированные обработчики событий для динамически созданных элементов
    function attachDelegates(container) {
        if (!container) return;

        // NOTE: Делегирование: раскрытие / сворачивание блоков
        container.addEventListener('click', (e) => {
            const header = e.target.closest('.collapsible-header');
            if (header && container.contains(header)) {
                const root = header.closest('.collapsible-container');
                if (root) root.classList.toggle('open');
            }
        });

        // NOTE: Делегирование: изменение состояния чекбоксов
        container.addEventListener('change', (e) => {
            const cb = e.target.closest('.service-checkbox');
            if (!cb) return;
            
            const item = cb.closest('.service-item');
            const input = item ? item.querySelector('.service-cost') : null;
            if (!input) return;
            
            if (cb.checked) {
                input.disabled = false;
                input.removeAttribute('disabled');
                input.readOnly = false;
                input.focus();
            } else {
                input.disabled = true;
                input.value = '';
            }
            recalcTotal();
        });

        // NOTE: Делегирование: ввод стоимости с валидацией
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
            
            recalcTotal();
        });

        // NOTE: Блокировка изменения стоимости через прокрутку колесика мыши
        container.addEventListener('wheel', (e) => {
            const inp = e.target.closest('.service-cost');
            if (inp) {
                // NOTE: Блокируем изменение значения поля, но разрешаем прокрутку страницы
                e.preventDefault();
                // NOTE: Прокручиваем страницу программно с сохранением скорости и направления
                const scrollAmount = e.deltaY * (e.deltaMode === 1 ? 40 : 1); // Учитываем deltaMode для правильной скорости
                window.scrollBy({
                    top: scrollAmount,
                    left: e.deltaX,
                    behavior: 'auto' // NOTE: Мгновенная прокрутка без анимации
                });
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
        recalcTotal();
    }

    // NOTE: Загрузка данных секции из API
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

    // NOTE: Функции валидации полей ввода
    // NOTE: Обработка валидации полей ввода
    function handleInputValidation(event) {
        const input = event.target;
        const errorMessage = input.closest('.customer').querySelector(`.error-message[data-for="${input.id}"]`);

        if (!input.validity.valid) {
            input.placeholder = input.dataset.defaultPlaceholder;
            input.classList.add("error-placeholder");
            input.classList.add("error");
            errorMessage.style.visibility = 'visible';
        } else {
            input.placeholder = input.dataset.defaultPlaceholder;
            input.classList.remove("error");
            input.classList.remove("error-placeholder");
            errorMessage.style.visibility = 'hidden';
        }
    }

    // NOTE: Функции валидации стоимости
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

            if (checkbox.checked) {
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

    // NOTE: Функция подготовки данных формы для отправки
    // NOTE: Подготовка данных формы для отправки
    function prepareFormData() {
        const orderForm = document.getElementById("orderForm");
        
        // NOTE: Удаляем старые скрытые поля
        document.querySelectorAll("input[name^='services']").forEach(input => input.remove());

        let serviceCount = 0;
        document.querySelectorAll(".service-item").forEach(item => {
            const checkbox = item.querySelector(".service-checkbox");
            const costInput = item.querySelector(".service-cost");
            
            if (checkbox.checked && costInput.value) {
                const serviceName = checkbox.getAttribute("data-service-name");
                const cost = costInput.value;
                const section = checkbox.getAttribute("data-section");
                const serviceId = checkbox.getAttribute("data-service-id");

                // NOTE: Создаем скрытые поля для отправки
                const fields = [
                    { name: `services[${serviceCount}][service_id]`, value: serviceId },
                    { name: `services[${serviceCount}][name]`, value: serviceName },
                    { name: `services[${serviceCount}][section]`, value: section },
                    { name: `services[${serviceCount}][price]`, value: cost },
                    { name: `services[${serviceCount}][detail]`, value: `${serviceName} ${cost} руб.` }
                ];

                fields.forEach(field => {
                    const hiddenInput = document.createElement("input");
                    hiddenInput.type = "hidden";
                    hiddenInput.name = field.name;
                    hiddenInput.value = field.value;
                    orderForm.appendChild(hiddenInput);
                });

                serviceCount++;
            }
        });

        // NOTE: Добавляем счетчик услуг
        const existingCount = document.querySelector("input[name='service_count']");
        if (existingCount) existingCount.remove();
        
        const serviceCountInput = document.createElement("input");
        serviceCountInput.type = "hidden";
        serviceCountInput.name = "service_count";
        serviceCountInput.value = serviceCount;
        orderForm.appendChild(serviceCountInput);
    }

    // NOTE: Фукционал кнопок
    // NOTE: Инициализация эффектов кнопок
    function initializeButtons() {
        const resetButton = document.querySelector('.btn-reset');
        const saveButton = document.querySelector('.btn-save');
        
        if (resetButton && saveButton) {
            resetButton.addEventListener('mouseover', function() {
                saveButton.classList.add('hover-effect');
            });

            resetButton.addEventListener('mouseout', function() {
                saveButton.classList.remove('hover-effect');
            });
        }
    }

    // NOTE: Функция автоматического раскрытия блоков с выбранными услугами
    // NOTE: Автоматическое раскрытие блоков с выбранными услугами
    function initializeCollapsibles() {
        document.querySelectorAll('.collapsible-container').forEach(container => {
            const hasCheckedServices = container.querySelector('.service-checkbox:checked');
            if (hasCheckedServices) {
                container.classList.add('open');
            }
        });
    }

    // NOTE: Функция предзаполнения данных о выбранных услугах
    // NOTE: Предзаполнение данных о выбранных услугах
    function populateExistingServices() {
        console.log('Попытка предзаполнения данных:', window.orderServicesData);
        
        if (!window.orderServicesData) {
            console.log('Нет данных для предзаполнения');
            return;
        }
        
        Object.values(window.orderServicesData).forEach(serviceData => {
            const serviceId = 'service' + serviceData.service_id;
            const checkbox = document.getElementById(serviceId);
            const costInput = document.getElementById(serviceId + '-cost');
            
            console.log(`Обрабатываем услугу ${serviceId}:`, serviceData);
            
            if (checkbox && costInput) {
                checkbox.checked = true;
                costInput.value = serviceData.price;
                costInput.disabled = false;
                costInput.removeAttribute('disabled');
                console.log(`Услуга ${serviceId} предзаполнена`);
            } else {
                console.log(`Элементы для услуги ${serviceId} не найдены`);
            }
        });
        
        recalcTotal();
    }

    // NOTE: Функция валидации полей ввода
    // NOTE: Инициализация валидации полей ввода
    function initializeInputValidation() {
        const inputs = document.querySelectorAll('.user_input');
        inputs.forEach(input => {
            input.dataset.defaultPlaceholder = input.placeholder;
            input.addEventListener('blur', handleInputValidation);
        });
    }

    // NOTE: Основная функция инициализации
    // NOTE: Главная функция инициализации
    function initializeAll() {
        // NOTE: 1. Загружаем все секции услуг
        Promise.all([
            load('works', targets.works),
            load('painting', targets.painting),
            load('parts', targets.parts)
        ]).then(() => {
            // NOTE: 2. Предзаполняем данные о выбранных услугах
            populateExistingServices();
            
            // NOTE: 3. Инициализируем автоматическое раскрытие блоков
            setTimeout(() => {
                initializeCollapsibles();
            }, 100);
        });

        // NOTE: 4. Инициализируем валидацию полей ввода
        initializeInputValidation();

        // NOTE: 5. Инициализируем поведение кнопок
        initializeButtons();

        // NOTE: 6. Обработка отправки формы
        const orderForm = document.getElementById("orderForm");
        if (orderForm) {
            orderForm.addEventListener("submit", function (event) {
                if (!validateForm()) {
                    event.preventDefault();
                    return;
                }
                
                recalcTotal();
                prepareFormData();
            });
        }
    }

    // NOTE: Запускаем инициализацию после загрузки DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeAll);
    } else {
        initializeAll();
    }

})();
