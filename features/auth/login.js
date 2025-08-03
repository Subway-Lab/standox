// NOTE: Login and password verification file

window.onload = function() {
    if (errorMessage) {
        // NOTE: Изменяем placeholder
        document.getElementById('login').placeholder = 'Неверный логин или пароль';
        document.getElementById('login').classList.add('error');
        
        // NOTE: Добавляем класс ошибки
        document.getElementById('password').classList.add('error');

        // NOTE: Выводим сообщение об ошибке
        alert(errorMessage); // NOTE: Здесь можно использовать любое уведомление, например, вывод в span или div
    }
};