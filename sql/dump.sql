-- Create the database
CREATE DATABASE IF NOT EXISTS task_manager;
USE task_manager;

-- Drop tables if they exist (for safe reruns)
DROP TABLE IF EXISTS tasks;
DROP TABLE IF EXISTS users;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tasks table
CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    user_id INT NOT NULL,
    created_by INT NOT NULL,
    deadline DATE NOT NULL,
    status ENUM('Pending', 'In Progress', 'Completed') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Optional: insert default admin user
-- Password = admin123 (hashed with bcrypt)
INSERT INTO users (name, email, password, role) VALUES
('Admin', 'admin@cytonn.com', '$2y$10$CTgKUl1Xn47jzPTPb0cw6ey0xiGoWOKPVrsIDYaZ2DJ4EENnKJZbi', 'admin');
