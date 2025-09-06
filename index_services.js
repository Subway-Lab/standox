// index_services.js
(function () {
    const base = (window.basePath) || (window.location.pathname.split('/')[1] ? '/' + window.location.pathname.split('/')[1] : '');
    const api = (type) => `${base}/api/services.php?type=${type}`;

    const targets = {
        works: document.getElementById('section-works'),
        painting: document.getElementById('section-painting'),
        parts: document.getElementById('section-parts'),
    };

    // Простой скелет
    const skeleton = () => '<div class="collapsible-container"><div class="collapsible-header"><label class="form-label">Загрузка...</label></div></div>';

    Object.values(targets).forEach(t => { if (t) t.innerHTML = skeleton(); });

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

    function attachDelegates(container) {
        if (!container) return;

        // Делегирование: раскрытие/сворачивание
        container.addEventListener('click', (e) => {
            const header = e.target.closest('.collapsible-header');
            if (header && container.contains(header)) {
                const root = header.closest('.collapsible-container');
                if (root) root.classList.toggle('open');
            }
        });

        // Делегирование: изменение чекбокса
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

        // Делегирование: ввод стоимости
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

        // Не блокируем колесо мыши, чтобы сохранить прокрутку страницы
    }

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

    // Независимая загрузка секций
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

    load('works', targets.works);
    load('painting', targets.painting);
    load('parts', targets.parts);
})();