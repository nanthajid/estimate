<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(['error' => 'Method not allowed'], 405);
}

if (!isset($_FILES['photo'])) {
    sendResponse(['error' => 'No photo uploaded'], 400);
}

$file = $_FILES['photo'];
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$allowed = ['jpg', 'jpeg', 'png', 'webp'];

if (!in_array(strtolower($ext), $allowed)) {
    sendResponse(['error' => 'Invalid file type'], 400);
}

$uploadDir = '../../uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$fileName = uniqid('staff_') . '.' . $ext;
$targetPath = $uploadDir . $fileName;

if (move_uploaded_file($file['tmp_name'], $targetPath)) {
    // Return the path relative to the project root for database storage
    sendResponse([
        'message' => 'Upload successful',
        'url' => 'uploads/' . $fileName
    ]);
} else {
    sendResponse(['error' => 'Failed to move uploaded file'], 500);
}
?>
