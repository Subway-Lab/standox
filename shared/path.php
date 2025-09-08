<?php
function getBasePath() {
    if (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'standox.pro') !== false) {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        return $protocol . '://www.standox.pro';
    }
    
    return '/sto-site';
}

$basePath = getBasePath();
?>