// NOTE: Error handling file

function handleInputValidation(event) {
    const input = event.target;
    const errorMessage = input.closest('.customer').querySelector(`.error-message[data-for="${input.id}"]`);

    if (!input.validity.valid) { // NOTE: Проверяем поле на валидность
        input.placeholder = input.dataset.defaultPlaceholder; // NOTE: Меняем текст placeholder на ошибку
        input.classList.add("error-placeholder"); // NOTE: Изменяем цвет placeholder
        input.classList.add("error"); // NOTE: Добавляем класс ошибки
        errorMessage.style.visibility = 'visible'; // NOTE: Показываем текст ошибки
    }
else {
        input.placeholder = input.dataset.defaultPlaceholder; // NOTE: Возвращаем дефолтный placeholder
        input.classList.remove("error"); // NOTE: Убираем класс ошибки
        input.classList.remove("error-placeholder");
        errorMessage.style.visibility = 'hidden'; // NOTE: Скрываем текст ошибки
    }
}

const inputs = document.querySelectorAll('.user_input'); // NOTE: Получаем все поля ввода

inputs.forEach(input => { // NOTE: Устанавливаем дефолтный placeholder в data-атрибут
    input.dataset.defaultPlaceholder = input.placeholder;
    input.addEventListener('blur', handleInputValidation);
});