<?php
// NOTE: api/services.php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=600');

$type = $_GET['type'] ?? '';
$allowed = ['works', 'painting', 'parts'];
if (!in_array($type, $allowed, true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid type. Use: works | painting | parts'], JSON_UNESCAPED_UNICODE);
    exit();
}

// NOTE: Простой файловый кэш на 10 минут (в системной temp-папке)
$cacheKey = 'sto_services_' . $type . '.json';
$cacheFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $cacheKey;
$cacheTtl = 600;

if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTtl)) {
    readfile($cacheFile);
    exit();
}

// NOTE: Загружаем данные из shared/*.php
$map = [
    'works'    => __DIR__ . '/../shared/works.php',
    'painting' => __DIR__ . '/../shared/painting.php',
    'parts'    => __DIR__ . '/../shared/parts.php',
];

$services = require $map[$type];

// NOTE: Страхуемся: если файл не вернул массив, отдаём ошибку
if (!is_array($services)) {
    http_response_code(500);
    echo json_encode(['error' => 'Services data unavailable'], JSON_UNESCAPED_UNICODE);
    exit();
}

// NOTE: Кодируем и сохраняем в кэш
$json = json_encode($services, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
file_put_contents($cacheFile, $json);

echo $json;