<?php
function getBasePath() {
    if (isset($_SERVER['HTTP_HOST'])) {
        $host = $_SERVER['HTTP_HOST'];
        
        // Поддержка доменов standox.pro и standox-chita.ru
        if (strpos($host, 'standox.pro') !== false || strpos($host, 'standox-chita.ru') !== false) {
            $isHTTPS = (
                (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ||
                (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
                (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
            );
            
            $protocol = $isHTTPS ? 'https' : 'http';
            
            // Возвращаем URL с текущим доменом
            if (strpos($host, 'standox-chita.ru') !== false) {
                return $protocol . '://standox-chita.ru';
            } else {
                return $protocol . '://www.standox.pro';
            }
        }
    }
    
    return '/standox';
}

$basePath = getBasePath();
?>
