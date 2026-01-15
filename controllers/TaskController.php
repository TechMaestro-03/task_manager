<?php
require_once __DIR__ . '/../models/Task.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/TaskController.php';
require_once __DIR__ . '/AuthController.php';


class TaskController {
    private $taskModel;
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->taskModel = new Task($conn);
    }

    // Create a new task
    public function createTask($title, $description, $userId, $deadline, $createdBy, $status = 'pending') {
        return $this->taskModel->createTask($title, $description, $userId, $deadline, $createdBy, $status);
    }

    // Get tasks assigned to a user with optional filter
    public function getTasksByUser($userId, $filter = 'all') {
        $query = "SELECT t.*, u.name as created_by FROM tasks t 
                  JOIN users u ON t.created_by = u.id 
                  WHERE t.user_id = ?";
        
        switch ($filter) {
            case 'pending':
                $query .= " AND t.status = 'pending'";
                break;
            case 'in-progress':
                $query .= " AND t.status = 'in-progress'";
                break;
            case 'completed':
                $query .= " AND t.status = 'completed'";
                break;
        }
        
        $query .= " ORDER BY t.deadline ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result();
    }

    // Get a task by ID (admin version - no user check)
    public function getTaskById($taskId) {
        $stmt = $this->conn->prepare("SELECT * FROM tasks WHERE id = ?");
        $stmt->bind_param("i", $taskId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Get a task by ID and user (for user security)
    public function getUserTaskById($taskId, $userId) {
        $stmt = $this->conn->prepare("SELECT t.*, u.name as created_by FROM tasks t 
                                     JOIN users u ON t.created_by = u.id 
                                     WHERE t.id = ? AND t.user_id = ?");
        $stmt->bind_param("ii", $taskId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Update a task's status (for user access)
    public function updateStatus($taskId, $userId, $status) {
        // Verify the task belongs to the user
        $task = $this->getUserTaskById($taskId, $userId);
        if (!$task) {
            return false;
        }
        
        return $this->taskModel->updateStatus($taskId, $userId, $status);
    }

    // Update task status with user verification
    public function updateTaskStatus($taskId, $newStatus, $userId = null) {
        if ($userId) {
            // Verify the task belongs to the user
            $task = $this->getUserTaskById($taskId, $userId);
            if (!$task) {
                return false;
            }
        } else {
            // Admin version - verify task exists
            $task = $this->getTaskById($taskId);
            if (!$task) {
                return false;
            }
        }
        
        $stmt = $this->conn->prepare("UPDATE tasks SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $newStatus, $taskId);
        return $stmt->execute();
    }

    // Delete a task (admin version)
    public function deleteTask($taskId) {
        $stmt = $this->conn->prepare("DELETE FROM tasks WHERE id = ?");
        $stmt->bind_param("i", $taskId);
        return $stmt->execute();
    }

    // Delete a task with user verification (user version)
    public function deleteUserTask($taskId, $userId) {
        // Verify the task belongs to the user
        $task = $this->getUserTaskById($taskId, $userId);
        if (!$task) {
            return false;
        }
        
        $stmt = $this->conn->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $taskId, $userId);
        return $stmt->execute();
    }

    // Get all tasks (admin view)
    public function getAllTasks($filter = 'all') {
        $query = "SELECT t.*, u.name as user_name, u2.name as created_by_name 
                  FROM tasks t
                  JOIN users u ON t.user_id = u.id
                  JOIN users u2 ON t.created_by = u2.id";
        
        switch ($filter) {
            case 'pending':
                $query .= " WHERE t.status = 'pending'";
                break;
            case 'in-progress':
                $query .= " WHERE t.status = 'in-progress'";
                break;
            case 'completed':
                $query .= " WHERE t.status = 'completed'";
                break;
        }
        
        $query .= " ORDER BY t.deadline ASC";
        
        $result = $this->conn->query($query);
        return $result;
    }
}
?>