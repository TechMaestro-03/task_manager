<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../controllers/TaskController.php';
require_once __DIR__ . '/../controllers/AuthController.php';

// Check if user is admin
if (!AuthController::isAdmin()) {
    header("Location: login.php");
    exit();
}

// Check if task ID is provided
if (!isset($_GET['id'])) {
    header("Location: admin-dashboard.php");
    exit();
}

$task_id = intval($_GET['id']);
$taskController = new TaskController($conn);

// Fetch task details
$task = $taskController->getTaskById($task_id);
if (!$task) {
    $_SESSION['error'] = "Task not found";
    header("Location: admin-dashboard.php");
    exit();
}

// Fetch all users for dropdown
$users = $conn->query("SELECT id, name FROM users WHERE role = 'user'");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $user_id = intval($_POST['user_id']);
    $deadline = $_POST['deadline'];
    $status = $_POST['status'];

    if ($title && $description && $user_id && $deadline && $status) {
        $stmt = $conn->prepare("UPDATE tasks SET 
                title = ?,
                description = ?,
                user_id = ?,
                deadline = ?,
                status = ?
                WHERE id = ?");
        $stmt->bind_param("ssissi", $title, $description, $user_id, $deadline, $status, $task_id);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Task updated successfully";
            header("Location: admin-dashboard.php");
            exit();
        } else {
            $error = "Error updating task: " . $conn->error;
        }
    } else {
        $error = "All fields are required";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Task - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Poppins', sans-serif;
            box-sizing: border-box;
        }

        body {
            background: #f0f4f8;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            background: #ffffff;
            margin: 60px auto;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 123, 255, 0.15);
        }

        h2 {
            color: #007BFF;
            text-align: center;
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
        }

        input, textarea, select {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 6px;
            border: 1px solid #ccc;
            transition: border 0.3s;
        }

        input:focus, textarea:focus, select:focus {
            border-color: #007BFF;
            outline: none;
        }

        .btn {
            background: #007BFF;
            color: white;
            padding: 12px;
            border: none;
            width: 100%;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
        }

        .btn:hover {
            background: #0056b3;
        }

        .message {
            text-align: center;
            margin-bottom: 15px;
            font-size: 14px;
        }

        .error {
            color: red;
        }

        .back {
            text-align: center;
            margin-top: 15px;
        }

        .back a {
            color: #007BFF;
            text-decoration: none;
        }

        .back a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Edit Task</h2>

    <?php if (isset($error)): ?>
        <div class="message error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form action="" method="POST">
        <label for="title">Task Title</label>
        <input type="text" name="title" value="<?= htmlspecialchars($task['title']) ?>" required>

        <label for="description">Task Description</label>
        <textarea name="description" rows="4" required><?= htmlspecialchars($task['description']) ?></textarea>

        <label for="user_id">Assign To</label>
        <select name="user_id" required>
            <option value="">-- Select User --</option>
            <?php while ($user = $users->fetch_assoc()): ?>
                <option value="<?= $user['id'] ?>" <?= $user['id'] == $task['user_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($user['name']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label for="deadline">Deadline</label>
        <input type="date" name="deadline" value="<?= htmlspecialchars($task['deadline']) ?>" required>

        <label for="status">Status</label>
        <select name="status" required>
            <option value="Pending" <?= $task['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
            <option value="In Progress" <?= $task['status'] == 'In Progress' ? 'selected' : '' ?>>In Progress</option>
            <option value="Completed" <?= $task['status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
        </select>

        <button type="submit" class="btn">Update Task</button>
    </form>

    <div class="back">
        <a href="admin-dashboard.php">← Back to Dashboard</a>
    </div>
</div>

</body>
</html>
<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../controllers/TaskController.php';
require_once __DIR__ . '/../controllers/AuthController.php';

// Check if user is admin
if (!AuthController::isAdmin()) {
    header("Location: login.php");
    exit();
}

// Check if task ID is provided
if (!isset($_GET['id'])) {
    header("Location: admin-dashboard.php");
    exit();
}

$task_id = intval($_GET['id']);
$taskController = new TaskController($conn);

// Fetch task details
$task = $taskController->getTaskById($task_id);
if (!$task) {
    $_SESSION['error'] = "Task not found";
    header("Location: admin-dashboard.php");
    exit();
}

// Fetch all users for dropdown
$users = $conn->query("SELECT id, name FROM users WHERE role = 'user'");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $user_id = intval($_POST['user_id']);
    $deadline = $_POST['deadline'];
    $status = $_POST['status'];

    if ($title && $description && $user_id && $deadline && $status) {
        $stmt = $conn->prepare("UPDATE tasks SET 
                title = ?,
                description = ?,
                user_id = ?,
                deadline = ?,
                status = ?
                WHERE id = ?");
        $stmt->bind_param("ssissi", $title, $description, $user_id, $deadline, $status, $task_id);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Task updated successfully";
            header("Location: admin-dashboard.php");
            exit();
        } else {
            $error = "Error updating task: " . $conn->error;
        }
    } else {
        $error = "All fields are required";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Task - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Poppins', sans-serif;
            box-sizing: border-box;
        }

        body {
            background: #f0f4f8;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            background: #ffffff;
            margin: 60px auto;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 123, 255, 0.15);
        }

        h2 {
            color: #007BFF;
            text-align: center;
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
        }

        input, textarea, select {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 6px;
            border: 1px solid #ccc;
            transition: border 0.3s;
        }

        input:focus, textarea:focus, select:focus {
            border-color: #007BFF;
            outline: none;
        }

        .btn {
            background: #007BFF;
            color: white;
            padding: 12px;
            border: none;
            width: 100%;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
        }

        .btn:hover {
            background: #0056b3;
        }

        .message {
            text-align: center;
            margin-bottom: 15px;
            font-size: 14px;
        }

        .error {
            color: red;
        }

        .back {
            text-align: center;
            margin-top: 15px;
        }

        .back a {
            color: #007BFF;
            text-decoration: none;
        }

        .back a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Edit Task</h2>

    <?php if (isset($error)): ?>
        <div class="message error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form action="" method="POST">
        <label for="title">Task Title</label>
        <input type="text" name="title" value="<?= htmlspecialchars($task['title']) ?>" required>

        <label for="description">Task Description</label>
        <textarea name="description" rows="4" required><?= htmlspecialchars($task['description']) ?></textarea>

        <label for="user_id">Assign To</label>
        <select name="user_id" required>
            <option value="">-- Select User --</option>
            <?php while ($user = $users->fetch_assoc()): ?>
                <option value="<?= $user['id'] ?>" <?= $user['id'] == $task['user_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($user['name']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label for="deadline">Deadline</label>
        <input type="date" name="deadline" value="<?= htmlspecialchars($task['deadline']) ?>" required>

        <label for="status">Status</label>
        <select name="status" required>
            <option value="Pending" <?= $task['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
            <option value="In Progress" <?= $task['status'] == 'In Progress' ? 'selected' : '' ?>>In Progress</option>
            <option value="Completed" <?= $task['status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
        </select>

        <button type="submit" class="btn">Update Task</button>
    </form>

    <div class="back">
        <a href="admin-dashboard.php">← Back to Dashboard</a>
    </div>
</div>

</body>
</html>