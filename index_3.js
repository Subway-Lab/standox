// NOTE: Файл обработки ошибок формы приема заказ-наряда

function handleInputValidation(event) {
    const input = event.target;
    const errorMessage = input.closest('.customer').querySelector(`.error-message[data-for="${input.id}"]`);

    if (!input.validity.valid) { //NOTE: Проверяем поле на валидность
        // NOTE: Меняем текст placeholder на ошибку
        input.placeholder = input.dataset.defaultPlaceholder;  // NOTE:Оставляем текст как был
        // NOTE:Изменяем цвет placeholder
        input.classList.add("error-placeholder");  // NOTE: Добавляем класс для изменения цвета placeholder 
        input.classList.add("error"); // NOTE:Добавляем класс для отображения ошибки
        errorMessage.style.visibility = 'visible'; // NOTE: Показываем текст ошибки
    }
else {
        input.style.backgroundColor = ""; // NOTE:Восстанавливаем стандартный фон
        input.placeholder = input.dataset.defaultPlaceholder; // NOTE: Восстанавливаем исходный placeholder
        input.classList.remove("error");  // NOTE: Убираем класс ошибки
        errorMessage.style.visibility = 'hidden'; // NOTE: Скрываем текст ошибки
    }
}

const inputs = document.querySelectorAll('.user_input'); // NOTE:Получаем все поля ввода

inputs.forEach(input => { // NOTE:Устанавливаем дефолтный placeholder в data-атрибут
    input.dataset.defaultPlaceholder = input.placeholder;
    input.addEventListener('blur', handleInputValidation);
});