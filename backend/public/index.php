<?php

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(204);
    exit;
}

require_once __DIR__ . "/../src/database.php";

try {
    $pdo = getDatabaseConnection();

    $stmt = $pdo->query("SELECT * FROM test_connection");
    $rows = $stmt->fetchAll();

    $response = [
        "success" => true,
        "message" => "PHP connected to MySQL successfully!",
        "data" => $rows
    ];

} catch (PDOException $e) {
    http_response_code(500);

    $response = [
        "success" => false,
        "message" => "Database connection failed"
    ];
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
