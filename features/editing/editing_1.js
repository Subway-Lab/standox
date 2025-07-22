// NOTE: Файл раскрытия блоков с выбраными услугами

document.addEventListener('DOMContentLoaded', function() { // NOTE: Поиск всех заголовков раскрывающихся блоков

    const collapsibles = document.querySelectorAll('.collapsible-header');
    
    collapsibles.forEach(collapsible => { // NOTE: Добавление обработчика событий для каждого заголовка
        collapsible.addEventListener('click', () => {
            const container = collapsible.parentElement;
            container.classList.toggle('open');
        });
    });
    
    document.querySelectorAll('.collapsible-container').forEach(container => { //NOTE: Раскрытие блоков с выбраными услугами при загрузке страницы
        const hasCheckedServices = container.querySelector('.service-checkbox:checked'); // NOTE: Проверка контейнера на наличие выбранных услуг
        
        if (hasCheckedServices) {
            container.classList.add('open');
        }
    });
});