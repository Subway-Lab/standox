<?php
function getBasePath() {
    if (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'standox.pro') !== false) {
        $isHTTPS = (
            (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ||
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
            (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
        );
        
        $protocol = $isHTTPS ? 'https' : 'http';
        return $protocol . '://www.standox.pro';
    }
    
    return '/standox';
}

$basePath = getBasePath();
?>