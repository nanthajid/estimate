<?php
// Database Configuration
$host = 'localhost';
$db   = 'doearcom_score';
$user = 'doearcom_nantha440664';
$pass = 'Nantha@440664';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// Simple .env loader
function loadEnv($path) {
    if (!file_exists($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}
loadEnv(__DIR__ . '/../../.env');

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
     session_start();
} catch (\PDOException $e) {
     header('Content-Type: application/json');
     echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
     exit;
}

// Helper to send JSON response
function sendResponse($data, $statusCode = 200) {
    header('Content-Type: application/json');
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}
?>
