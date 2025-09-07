<?php
function getBasePath() {
    $script = trim($_SERVER['SCRIPT_NAME'] ?? '', '/');
    $firstSegment = explode('/', $script)[0] ?? '';
    if ($firstSegment === '' || $firstSegment === 'index.php') {
        return '';
    }
    return '/' . $firstSegment;
}