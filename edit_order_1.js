// NOTE: Файл раскрытия блоков с выбраными услугами

document.addEventListener('DOMContentLoaded', function() { // NOTE:Поиск всех заголовков раскрывающихся блоков

    const collapsibles = document.querySelectorAll('.collapsible-header');
    
    collapsibles.forEach(collapsible => { // NOTE: Добавляем обработчик событий для каждого заголовка
        collapsible.addEventListener('click', () => {
            const container = collapsible.parentElement;
            container.classList.toggle('open');
        });
    });
    
    document.querySelectorAll('.collapsible-container').forEach(container => { //NOTE: Раскрываем блоки с выбраными услугами при загрузке страницы
        const hasCheckedServices = container.querySelector('.service-checkbox:checked'); // NOTE: Проверяем, есть ли внутри контейнера выбранные услуги
        
        if (hasCheckedServices) {
            container.classList.add('open');
        }
    });
});