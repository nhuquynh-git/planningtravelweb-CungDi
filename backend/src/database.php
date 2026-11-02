
<?php

function getDatabaseConnection(): PDO
{
    $host = "db";
    $port = "3306";
    $database = getenv("MYSQL_DATABASE");
    $username = getenv("MYSQL_USER");
    $password = getenv("MYSQL_PASSWORD");

    $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";

    return new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
}
