<?php
    $servername = "127.0.0.1";
    $username = "standox_user";
    $password = "ZiNH7N987CR2";
    $dbname = "standox_db";

    $db_connection = new mysqli($servername, $username, $password, $dbname);

    if ($db_connection->connect_error) {
        die("Ошибка подключения: " . $db_connection->connect_error);
    }

    $db_connection->set_charset("utf8mb4");
?>
