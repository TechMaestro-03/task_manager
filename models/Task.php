<?php
class Task {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get tasks assigned to a user
    public function getTasksByUser($userId) {
        $stmt = $this->conn->prepare("SELECT * FROM tasks WHERE user_id = ? ORDER BY deadline ASC");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result();
    }

    // Get a specific task by ID (admin version - no user check)
    public function getTaskById($taskId) {
        $stmt = $this->conn->prepare("SELECT * FROM tasks WHERE id = ?");
        $stmt->bind_param("i", $taskId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Get a specific task by ID and user (for user access control)
    public function getUserTaskById($taskId, $userId) {
        $stmt = $this->conn->prepare("SELECT * FROM tasks WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $taskId, $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Admin-level status update (no user check)
    public function updateTaskStatus($taskId, $newStatus) {
        $allowedStatuses = ['Pending', 'In Progress', 'Completed'];
        if (!in_array($newStatus, $allowedStatuses)) {
            return false;
        }

        $stmt = $this->conn->prepare("UPDATE tasks SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $newStatus, $taskId);
        return $stmt->execute();
    }

    // User-level status update (requires user ID check)
    public function updateStatus($taskId, $userId, $status) {
        $allowedStatuses = ['Pending', 'In Progress', 'Completed'];
        if (!in_array($status, $allowedStatuses)) {
            return false;
        }

        $stmt = $this->conn->prepare("UPDATE tasks SET status = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("sii", $status, $taskId, $userId);
        return $stmt->execute();
    }

    // Create a new task
    public function createTask($title, $description, $userId, $deadline, $createdBy = null) {
        $sql = "INSERT INTO tasks (title, description, user_id, deadline, created_by) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssisi", $title, $description, $userId, $deadline, $createdBy);
        return $stmt->execute();
    }

   // Delete a task (admin version - no user check)
   public function deleteTask($taskId) {
    $stmt = $this->conn->prepare("DELETE FROM tasks WHERE id = ?");
    $stmt->bind_param("i", $taskId);
    return $stmt->execute();
}

// Delete a task with user verification (user version)
public function deleteUserTask($taskId, $userId) {
    $stmt = $this->conn->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $taskId, $userId);
    return $stmt->execute();
} 
}