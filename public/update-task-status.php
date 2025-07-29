<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';
$task = null;

// Validate task ID from query param
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $error = "Invalid task ID.";
} else {
    $task_id = intval($_GET['id']);
    $result = $conn->query("SELECT * FROM tasks WHERE id = $task_id AND user_id = $user_id");

    if ($result && $result->num_rows === 1) {
        $task = $result->fetch_assoc();
    } else {
        $error = "Task not found or access denied.";
    }
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $task) {
    $new_status = $conn->real_escape_string($_POST['status']);
    $allowed = ['Pending', 'In Progress', 'Completed'];

    if (in_array($new_status, $allowed)) {
        $update = $conn->query("UPDATE tasks SET status = '$new_status' WHERE id = $task_id AND user_id = $user_id");
        if ($update) {
            $success = "Task status updated!";
            $task['status'] = $new_status; // reflect in view
        } else {
            $error = "Failed to update task.";
        }
    } else {
        $error = "Invalid status selected.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Task Status</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Poppins', sans-serif;
            box-sizing: border-box;
        }
        body {
            margin: 0;
            background: #f0f4f8;
            padding: 40px;
        }
        .container {
            max-width: 600px;
            background: white;
            margin: auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 123, 255, 0.1);
        }
        h2 {
            color: #007BFF;
            margin-bottom: 20px;
            text-align: center;
        }
        label {
            display: block;
            margin: 10px 0 5px;
            font-weight: 600;
        }
        select, button {
            width: 100%;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
            margin-bottom: 20px;
        }
        button {
            background-color: #007BFF;
            color: white;
            border: none;
            font-weight: bold;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .msg {
            text-align: center;
            margin-bottom: 15px;
        }
        .success {
            color: green;
        }
        .error {
            color: red;
        }
        .back-link {
            text-align: center;
            margin-top: 10px;
        }
        .back-link a {
            color: #007BFF;
            text-decoration: none;
            font-weight: 500;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Update Task Status</h2>

    <?php if ($error): ?>
        <div class="msg error"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
        <div class="msg success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if ($task): ?>
        <p><strong>Task:</strong> <?= htmlspecialchars($task['title']) ?></p>
        <p><strong>Deadline:</strong> <?= htmlspecialchars($task['deadline']) ?></p>

        <form method="POST">
            <label for="status">Status</label>
            <select name="status" id="status">
                <option value="Pending" <?= $task['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                <option value="In Progress" <?= $task['status'] === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                <option value="Completed" <?= $task['status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
            </select>
            <button type="submit">Update Status</button>
        </form>
    <?php endif; ?>

    <div class="back-link">
        <a href="user-dashboard.php">← Back to Dashboard</a>
    </div>
</div>

</body>
</html>
