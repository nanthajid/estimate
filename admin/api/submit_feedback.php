<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(['error' => 'Method not allowed'], 405);
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['staff_id']) || !isset($data['rating'])) {
    sendResponse(['error' => 'Missing required fields'], 400);
}

// 1. Find staff internal DB ID by their public staff_id
$stmt = $pdo->prepare("SELECT id FROM staff WHERE staff_id = ?");
$stmt->execute([$data['staff_id']]);
$staff = $stmt->fetch();

if (!$staff) {
    sendResponse(['error' => 'Staff not found'], 404);
}

// 2. Insert feedback
$stmt = $pdo->prepare("INSERT INTO feedbacks (staff_db_id, rating, feedback_text) VALUES (?, ?, ?)");
$stmt->execute([
    $staff['id'],
    $data['rating'],
    $data['feedback_text'] ?? ''
]);

sendResponse(['message' => 'Feedback submitted successfully']);
?>
