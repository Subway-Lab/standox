const loupe = document.querySelector(".loupe");
const title = document.querySelector("h2");
const panel = document.querySelector(".search_form");
const input = document.querySelector(".search_input");
const cross = document.querySelector(".close_icon");

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