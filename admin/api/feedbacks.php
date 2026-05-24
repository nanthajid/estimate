<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    if (!isset($_GET['id'])) {
        sendResponse(['error' => 'Missing ID'], 400);
    }
    $stmt = $pdo->prepare("DELETE FROM feedbacks WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    sendResponse(['message' => 'Feedback deleted']);
}

// Get all feedbacks with staff details
$stmt = $pdo->query("
    SELECT f.*, s.name as staff_name, s.staff_id, s.position, s.department 
    FROM feedbacks f 
    JOIN staff s ON f.staff_db_id = s.id 
    ORDER BY f.created_at DESC
");
sendResponse($stmt->fetchAll());
?>
