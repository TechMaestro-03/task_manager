<?php
class User {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all users (for admin)
    public function getAllUsers() {
        $stmt = $this->conn->prepare("SELECT id, name, email, role, created_at FROM users WHERE role = 'user'");
        $stmt->execute();
        return $stmt->get_result();
    }

    // Find user by email
    public function findByEmail($email) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Create new user
    public function create($name, $email, $password) {
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')");
        $stmt->bind_param("sss", $name, $email, $hashed);
        return $stmt->execute();
    }

    // Authenticate user
    public function authenticate($email, $password) {
        $user = $this->findByEmail($email);
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }

    // Delete user (admin)
    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // Get user by ID
    public function getUserById($userId) {
        $stmt = $this->conn->prepare("SELECT id, name, email, role, created_at FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Update user information
    public function updateUser($userId, $data) {
        $query = "UPDATE users SET ";
        $params = [];
        $types = '';
        
        foreach ($data as $key => $value) {
            $query .= "$key = ?, ";
            $params[] = $value;
            $types .= 's'; // All fields are strings
        }
        
        $query = rtrim($query, ', ');
        $query .= " WHERE id = ?";
        $params[] = $userId;
        $types .= 'i'; // ID is integer
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        return $stmt->execute();
    }

    // Check if email exists (for registration)
    public function emailExists($email) {
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }

    // Update user password
    public function updatePassword($userId, $newPassword) {
        $hashed = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $this->conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed, $userId);
        return $stmt->execute();
    }

    // Get user count (for admin dashboard)
    public function getUserCount() {
        $result = $this->conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'user'");
        return $result->fetch_assoc()['count'];
    }
}
?>