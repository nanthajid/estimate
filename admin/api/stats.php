<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendResponse(['error' => 'Method not allowed'], 405);
}

// 1. Total Feedbacks
$totalFeedbacks = $pdo->query("SELECT COUNT(*) FROM feedbacks")->fetchColumn();

// 2. Average Rating
$avgRating = $pdo->query("SELECT AVG(rating) FROM feedbacks")->fetchColumn();

// 3. Total Staff
$totalStaff = $pdo->query("SELECT COUNT(*) FROM staff")->fetchColumn();

// 4. Feedbacks by Rating (for chart)
$ratingDist = $pdo->query("SELECT rating, COUNT(*) as count FROM feedbacks GROUP BY rating ORDER BY rating DESC")->fetchAll();

// 5. Recent Feedbacks with Staff Name
$stmt = $pdo->query("
    SELECT f.*, s.name as staff_name 
    FROM feedbacks f 
    JOIN staff s ON f.staff_db_id = s.id 
    ORDER BY f.created_at DESC 
    LIMIT 10
");
$recentFeedbacks = $stmt->fetchAll();

// 6. Staff Performance (Average Rating per Staff)
$stmt = $pdo->query("
    SELECT s.name, AVG(f.rating) as avg_rating, COUNT(f.id) as feedback_count
    FROM staff s
    LEFT JOIN feedbacks f ON s.id = f.staff_db_id
    GROUP BY s.id, s.name
    ORDER BY avg_rating DESC
");
$staffPerformance = $stmt->fetchAll();

sendResponse([
    'total_feedbacks' => (int)$totalFeedbacks,
    'avg_rating' => round((float)$avgRating, 1),
    'total_staff' => (int)$totalStaff,
    'rating_distribution' => $ratingDist,
    'recent_feedbacks' => $recentFeedbacks,
    'staff_performance' => $staffPerformance
]);
?>
