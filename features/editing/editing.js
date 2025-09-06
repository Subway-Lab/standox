// editing.js - Объединенный функционал для страницы редактирования заказов
// Объединяет: index_1.js, editing_1.js, editing_2.js, editing_3.js, index_services.js

(function () {
    'use strict';

    // ============================================================================
    // НАСТРОЙКИ И ПЕРЕМЕННЫЕ
    // ============================================================================
    
    const base = (window.basePath) || (window.location.pathname.split('/')[1] ? '/' + window.location.pathname.split('/')[1] : '');
    const api = (type) => `${base}/api/services.php?type=${type}`;

    const targets = {
        works: document.getElementById('section-works'),
        painting: document.getElementById('section-painting'),
        parts: document.getElementById('section-parts'),
    };

    // ============================================================================
    // ФУНКЦИИ ЗАГРУЗКИ И ОТОБРАЖЕНИЯ УСЛУГ (из index_services.js)
    // ============================================================================

    // Простой скелет для отображения во время загрузки
    const skeleton = () => '<div class="collapsible-container"><div class="collapsible-header"><label class="form-label">Загрузка...</label></div></div>';

    // Инициализация скелетов для всех секций
    Object.values(targets).forEach(t => { if (t) t.innerHTML = skeleton(); });

    // Функция пересчета общей суммы (объединенная из index_1.js и index_services.js)
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

    // Делегированные обработчики событий для динамически созданных элементов
    function attachDelegates(container) {
        if (!container) return;

        // Делегирование: раскрытие/сворачивание блоков
        container.addEventListener('click', (e) => {
            const header = e.target.closest('.collapsible-header');
            if (header && container.contains(header)) {
                const root = header.closest('.collapsible-container');
                if (root) root.classList.toggle('open');
            }
        });

        // Делегирование: изменение состояния чекбоксов
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

        // Делегирование: ввод стоимости с валидацией
        container.addEventListener('input', (e) => {
            const inp = e.target.closest('.service-cost');
            if (!inp) return;
            
            // Нормализация запятой в точку
            if (typeof inp.value === 'string' && inp.value.includes(',')) {
                inp.value = inp.value.replace(',', '.');
            }
            
            // Разрешаем только цифры и одну точку
            inp.value = inp.value.replace(/[^0-9.]/g, '');
            const firstDot = inp.value.indexOf('.');
            if (firstDot !== -1) {
                inp.value = inp.value.substring(0, firstDot + 1) + inp.value.substring(firstDot + 1).replace(/\./g, '');
            }
            
            recalcTotal();
        });

        // Блокировка изменения значения при прокрутке колесиком мыши
        container.addEventListener('wheel', (e) => {
            if (e.target.closest('.service-cost')) {
                e.preventDefault();
            }
        });
    }

    // Рендеринг секции услуг
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
        
        // Инициализация состояний чекбоксов/инпутов
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

    // Загрузка данных секции из API
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

    // ============================================================================
    // ФУНКЦИИ ВАЛИДАЦИИ ПОЛЕЙ (из editing_2.js)
    // ============================================================================

    // Обработка валидации полей ввода
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

    // ============================================================================
    // ФУНКЦИИ ВАЛИДАЦИИ СТОИМОСТИ (из index_1.js)
    // ============================================================================

    // Функция проверки заполненности поля стоимости
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

    // Функция проверки всех полей перед отправкой формы
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

    // ============================================================================
    // ФУНКЦИИ ОБРАБОТКИ ФОРМЫ (из index_1.js)
    // ============================================================================

    // Подготовка данных формы для отправки
    function prepareFormData() {
        const orderForm = document.getElementById("orderForm");
        
        // Удаляем старые скрытые поля
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

                // Создаем скрытые поля для отправки
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

        // Добавляем счетчик услуг
        const existingCount = document.querySelector("input[name='service_count']");
        if (existingCount) existingCount.remove();
        
        const serviceCountInput = document.createElement("input");
        serviceCountInput.type = "hidden";
        serviceCountInput.name = "service_count";
        serviceCountInput.value = serviceCount;
        orderForm.appendChild(serviceCountInput);
    }

    // ============================================================================
    // ФУНКЦИИ ПОВЕДЕНИЯ КНОПОК (из editing_3.js)
    // ============================================================================

    // Инициализация эффектов кнопок
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

    // ============================================================================
    // ФУНКЦИИ АВТОМАТИЧЕСКОГО РАСКРЫТИЯ БЛОКОВ (из editing_1.js)
    // ============================================================================

    // Автоматическое раскрытие блоков с выбранными услугами
    function initializeCollapsibles() {
        document.querySelectorAll('.collapsible-container').forEach(container => {
            const hasCheckedServices = container.querySelector('.service-checkbox:checked');
            if (hasCheckedServices) {
                container.classList.add('open');
            }
        });
    }

    // ============================================================================
    // ФУНКЦИИ ПРЕДЗАПОЛНЕНИЯ ДАННЫХ
    // ============================================================================

    // Предзаполнение данных о выбранных услугах
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

    // ============================================================================
    // ИНИЦИАЛИЗАЦИЯ ВАЛИДАЦИИ ПОЛЕЙ ВВОДА (из editing_2.js)
    // ============================================================================

    function initializeInputValidation() {
        const inputs = document.querySelectorAll('.user_input');
        inputs.forEach(input => {
            input.dataset.defaultPlaceholder = input.placeholder;
            input.addEventListener('blur', handleInputValidation);
        });
    }

    // ============================================================================
    // ОСНОВНАЯ ИНИЦИАЛИЗАЦИЯ
    // ============================================================================

    // Главная функция инициализации
    function initializeAll() {
        // 1. Загружаем все секции услуг
        Promise.all([
            load('works', targets.works),
            load('painting', targets.painting),
            load('parts', targets.parts)
        ]).then(() => {
            // 2. Предзаполняем данные о выбранных услугах
            populateExistingServices();
            
            // 3. Инициализируем автоматическое раскрытие блоков
            setTimeout(() => {
                initializeCollapsibles();
            }, 100);
        });

        // 4. Инициализируем валидацию полей ввода
        initializeInputValidation();

        // 5. Инициализируем поведение кнопок
        initializeButtons();

        // 6. Обработка отправки формы
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

    // Запускаем инициализацию после загрузки DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeAll);
    } else {
        initializeAll();
    }

})();
