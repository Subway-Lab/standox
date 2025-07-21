document.querySelector('.btn-reset').addEventListener('mouseover', function() {
    document.querySelector('.btn-save').classList.add('hover-effect');
});

document.querySelector('.btn-reset').addEventListener('mouseout', function() {
    document.querySelector('.btn-save').classList.remove('hover-effect');
});