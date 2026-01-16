<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../controllers/TaskController.php';
require_once __DIR__ . '/../controllers/AuthController.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Check if task ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "No task ID provided";
    header("Location: ../admin-dashboard.php");
    exit();
}

$taskId = intval($_GET['id']);
$userId = $_SESSION['user_id'];
$isAdmin = ($_SESSION['role'] === 'admin');

$taskController = new TaskController($conn);

if ($isAdmin) {
    // Admin can delete any task
    $success = $taskController->deleteTask($taskId);
} else {
    // Regular users can only delete their own tasks
    $success = $taskController->deleteUserTask($taskId, $userId);
}

if ($success) {
    $_SESSION['success'] = "Task deleted successfully";
} else {
    $_SESSION['error'] = "Failed to delete task or you don't have permission";
}

// Redirect back to the appropriate page
$redirect = $isAdmin ? '../admin-dashboard.php' : '../dashboard.php';
header("Location: $redirect");
exit();
?>
