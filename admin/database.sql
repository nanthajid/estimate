-- Database Schema for Staff Feedback System

CREATE DATABASE IF NOT EXISTS staff_feedback CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE staff_feedback;

-- Table for Staff Data
C-- Database Schema for Staff Feedback System

CREATE DATABASE IF NOT EXISTS staff_feedback CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE staff_feedback;

-- Table for Staff Data
CREATE TABLE IF NOT EXISTS staff (
    id INT AUTO_INCREMENT PRIMARY KEY,
    staff_id VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    position VARCHAR(255),
    department VARCHAR(255),
    photo_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Table for Feedback/Ratings
CREATE TABLE IF NOT EXISTS feedbacks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    staff_db_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    feedback_text TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (staff_db_id) REFERENCES staff(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Initial Demo Data
INSERT INTO staff (staff_id, name, position, department, photo_url) VALUES 
('staff001', 'วิชชุตา แสงทอง', 'Customer Service', 'Front Office', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?q=80&w=256&h=256&auto=format&fit=crop'),
('staff002', 'สมชาย รักดี', 'Manager', 'Retail', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?q=80&w=256&h=256&auto=format&fit=crop');


