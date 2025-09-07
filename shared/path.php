<?php
function getBasePath() {
    $script = trim($_SERVER['SCRIPT_NAME'] ?? '', '/');
    $firstSegment = explode('/', $script)[0] ?? '';

    // Known top-level paths of this app. If URL starts with one of these,
    // the app is at the domain root, so basePath should be empty.
    $knownTop = [
        '',
        'index.php',
        'features',
        'files',
        'public',
        'csp-report-endpoint.php',
        'submit_order.php'
    ];

    if ($firstSegment === '' || in_array($firstSegment, $knownTop, true) || strpos($firstSegment, '.') !== false) {
        return '';
    }

    // Otherwise assume first segment is the subdirectory the app is deployed under (e.g., /sto-site)
    return '/' . $firstSegment;
}