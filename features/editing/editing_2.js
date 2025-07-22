// NOTE: Error handling file

function handleInputValidation(event) {
    const input = event.target;
    const errorMessage = input.closest('.customer').querySelector(`.error-message[data-for="${input.id}"]`);

    if (!input.validity.valid) { // NOTE: Проверка полей на валидность
        input.placeholder = input.dataset.defaultPlaceholder; // NOTE: Замена текста placeholder на ошибку
        input.classList.add("error-placeholder"); // NOTE: Изменение цвета placeholder при ошибке
        input.classList.add("error"); // NOTE: Добавление класса ошибки
        errorMessage.style.visibility = 'visible'; // NOTE: Вывод текста об ошибке
    }
else {
        input.placeholder = input.dataset.defaultPlaceholder; // NOTE: Возврат первоначального состояния placeholder
        input.classList.remove("error"); // NOTE: Удаление класса ошибки
        input.classList.remove("error-placeholder");
        errorMessage.style.visibility = 'hidden'; // NOTE: Удаление текста об ошибке
    }
}

const inputs = document.querySelectorAll('.user_input'); // NOTE: Выбор всех полей ввода

inputs.forEach(input => { // NOTE: Устанавливаем первоначальный placeholder в data-атрибут
    input.dataset.defaultPlaceholder = input.placeholder;
    input.addEventListener('blur', handleInputValidation);
});