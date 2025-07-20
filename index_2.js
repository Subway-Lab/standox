// NOTE: Поиск всех заголовков раскрывающихся блоков

const collapsibles = document.querySelectorAll('.collapsible-header');

// Добавляем обработчик событий для каждого заголовка
collapsibles.forEach(collapsible => {
    collapsible.addEventListener('click', () => {
        const container = collapsible.parentElement;
        container.classList.toggle('open');
    });
});