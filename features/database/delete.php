<?php
    require_once __DIR__ . '/../auth/check.php';  // NOTE: Проверка авторизации пользователя

    require_once __DIR__ . '/../../shared/path.php';
    $basePath = getBasePath();
?>

<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // NOTE: Подключаемся к базе данных
    require_once __DIR__ . '/../../shared/db_settings.php';

    if ($db_connection->connect_error) {
        die("Ошибка подключения: " . $db_connection->connect_error);
    }

    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $stmt = $db_connection->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            header('Location: '. $basePath .'/features/database/database.php');
            exit();
        } else {
            echo "Ошибка удаления заказа: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Не указан ID заказа.";
    }
    $db_connection->close();
?>