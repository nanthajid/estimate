<?php
require_once 'config.php';

$sql = "CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    line_id VARCHAR(100) NOT NULL UNIQUE,
    display_name VARCHAR(255),
    picture_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;";

try {
    $pdo->exec($sql);
    echo "Table 'admins' created successfully.";
} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
}
?>
