const loupe = document.querySelector(".loupe");
const title = document.querySelector("h2");
const panel = document.querySelector(".search_form");
const input = document.querySelector(".search_input");
const cross = document.querySelector(".close_icon");

// NOTE: Функция оптимизации поиска
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// NOTE: Функция для выполнения поиска
function performSearch() {
    if (input.value.trim() !== '') {
        console.log('Выполняем поиск:', input.value);
        // NOTE: Отправка формы для выполнения поиска
        const form = document.querySelector('form');
        if (form) {
            form.submit();
        }
    }
}

// NOTE: Создание debounced версия поиска с задержкой 500мс
const debouncedSearch = debounce(performSearch, 500);

loupe.addEventListener('click', function() {
    loupe.classList.toggle("hidden");
    title.classList.toggle("hidden");
    panel.classList.toggle("visible");
    cross.classList.toggle("visible");
    input.focus();
    console.log('Активация панели поиска');
});

cross.addEventListener('click', function() {
    loupe.classList.remove("hidden");
    title.classList.remove("hidden");
    panel.classList.remove("visible");
    cross.classList.remove("visible");
    input.value = ''; // NOTE: Очищаем поле ввода
    input.blur(); // NOTE: Выводим поле ввода из фокуса
    console.log('Очистка поля ввода данных, закрытие панели');
});

// NOTE: Обработчик для автоматического поиска при вводе
input.addEventListener('input', function() {
    const searchValue = input.value.trim();
    
    if (searchValue.length >= 2) {
        // NOTE: Выполняем поиск с debounce
        debouncedSearch();
    } else if (searchValue.length === 0) {
        // NOTE: Если поле пустое, сразу очищаем результаты
        window.location.href = window.location.pathname;
    }
});