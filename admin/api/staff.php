<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Get all staff or single staff if ID provided
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("SELECT * FROM staff WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            sendResponse($stmt->fetch());
        } elseif (isset($_GET['staff_id'])) {
            $stmt = $pdo->prepare("SELECT * FROM staff WHERE staff_id = ?");
            $stmt->execute([$_GET['staff_id']]);
            sendResponse($stmt->fetch());
        } else {
            $stmt = $pdo->query("SELECT * FROM staff ORDER BY id DESC");
            sendResponse($stmt->fetchAll());
        }
        break;

    case 'POST':
        // Create new staff
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data || !isset($data['staff_id']) || !isset($data['name'])) {
                sendResponse(['error' => 'Missing required fields'], 400);
            }

            $stmt = $pdo->prepare("INSERT INTO staff (staff_id, name, position, department, photo_url) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $data['staff_id'],
                $data['name'],
                $data['position'] ?? '',
                $data['department'] ?? '',
                $data['photo_url'] ?? ''
            ]);
            sendResponse(['message' => 'Staff created', 'id' => $pdo->lastInsertId()]);
        } catch (\PDOException $e) {
            sendResponse(['error' => 'Database error: ' . $e->getMessage()], 500);
        }
        break;

    case 'PUT':
        // Update staff
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data || !isset($data['id'])) {
                sendResponse(['error' => 'Missing ID'], 400);
            }

            $stmt = $pdo->prepare("UPDATE staff SET staff_id = ?, name = ?, position = ?, department = ?, photo_url = ? WHERE id = ?");
            $stmt->execute([
                $data['staff_id'],
                $data['name'],
                $data['position'],
                $data['department'],
                $data['photo_url'],
                $data['id']
            ]);
            sendResponse(['message' => 'Staff updated']);
        } catch (\PDOException $e) {
            sendResponse(['error' => 'Database error: ' . $e->getMessage()], 500);
        }
        break;

    case 'DELETE':
        // Delete staff
        if (!isset($_GET['id'])) {
            sendResponse(['error' => 'Missing ID'], 400);
        }
        $stmt = $pdo->prepare("DELETE FROM staff WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        sendResponse(['message' => 'Staff deleted']);
        break;

    default:
        sendResponse(['error' => 'Method not allowed'], 405);
        break;
}
?>
