<?php
session_start();
require_once '../config/db.php';
require_once '../scripts/send_mail.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$success = '';
$error = '';

// Get admin details
$admin_id = $_SESSION['user_id'];
$admin_name = $_SESSION['name'] ?? 'Admin';

// Fetch users
$users = $conn->query("SELECT id, name, email FROM users WHERE role = 'user'");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $user_id = intval($_POST['user_id']);
    $deadline = $_POST['deadline'];

    if ($title && $description && $user_id && $deadline) {
        $stmt = $conn->prepare("INSERT INTO tasks (title, description, user_id, deadline, created_by) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssisi", $title, $description, $user_id, $deadline, $admin_id);
        
        if ($stmt->execute()) {
            // Get user details
            $result = $conn->query("SELECT email, name FROM users WHERE id = $user_id");
            $user = $result->fetch_assoc();
            
            // Prepare email
            $to = $user['email'];
            $subject = "New Task Assigned: $title";
            $message = "Hello {$user['name']},\n\n"
                     . "You have been assigned a new task by $admin_name:\n\n"
                     . "Task: \"$title\"\n"
                     . "Description: $description\n"
                     . "Deadline: $deadline\n\n"
                     . "Please log in to your dashboard to view details.\n\n"
                     . "Regards,\nTask Manager";
            
            // Send email
            if (sendTaskEmail($to, $subject, $message)) {
                $success = "Task assigned and notification sent successfully.";
            } else {
                $success = "Task assigned but email notification failed to send.";
            }
        } else {
            $error = "Error assigning task: " . $conn->error;
        }
        $stmt->close();
    } else {
        $error = "All fields are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assign Task - Admin</title>
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
        .success {
            color: green;
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
        .admin-info {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Assign New Task</h2>
    
    <div class="admin-info">
        Assigning as: <strong><?= htmlspecialchars($admin_name) ?></strong>
    </div>

    <?php if ($success): ?>
        <div class="message success"><?= htmlspecialchars($success) ?></div>
    <?php elseif ($error): ?>
        <div class="message error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <label for="user_id">Assign To</label>
        <select name="user_id" required>
            <option value="">-- Select User --</option>
            <?php while ($user = $users->fetch_assoc()): ?>
                <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['name']) ?> (<?= htmlspecialchars($user['email']) ?>)</option>
            <?php endwhile; ?>
        </select>

        <label for="title">Task Title</label>
        <input type="text" name="title" required>

        <label for="description">Task Description</label>
        <textarea name="description" rows="4" required></textarea>

        <label for="deadline">Deadline</label>
        <input type="date" name="deadline" required>

        <button type="submit" class="btn">Assign Task</button>
    </form>

    <div class="back">
        <a href="admin-dashboard.php">← Back to Dashboard</a>
    </div>
</div>
</body>
</html>
<?php
session_start();
require_once '../config/db.php';
require_once '../scripts/send_mail.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$success = '';
$error = '';

// Get admin details
$admin_id = $_SESSION['user_id'];
$admin_name = $_SESSION['name'] ?? 'Admin';

// Fetch users
$users = $conn->query("SELECT id, name, email FROM users WHERE role = 'user'");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $user_id = intval($_POST['user_id']);
    $deadline = $_POST['deadline'];

    if ($title && $description && $user_id && $deadline) {
        $stmt = $conn->prepare("INSERT INTO tasks (title, description, user_id, deadline, created_by) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssisi", $title, $description, $user_id, $deadline, $admin_id);
        
        if ($stmt->execute()) {
            // Get user details
            $result = $conn->query("SELECT email, name FROM users WHERE id = $user_id");
            $user = $result->fetch_assoc();
            
            // Prepare email
            $to = $user['email'];
            $subject = "New Task Assigned: $title";
            $message = "Hello {$user['name']},\n\n"
                     . "You have been assigned a new task by $admin_name:\n\n"
                     . "Task: \"$title\"\n"
                     . "Description: $description\n"
                     . "Deadline: $deadline\n\n"
                     . "Please log in to your dashboard to view details.\n\n"
                     . "Regards,\nTask Manager";
            
            // Send email
            if (sendTaskEmail($to, $subject, $message)) {
                $success = "Task assigned and notification sent successfully.";
            } else {
                $success = "Task assigned but email notification failed to send.";
            }
        } else {
            $error = "Error assigning task: " . $conn->error;
        }
        $stmt->close();
    } else {
        $error = "All fields are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assign Task - Admin</title>
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
        .success {
            color: green;
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
        .admin-info {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Assign New Task</h2>
    
    <div class="admin-info">
        Assigning as: <strong><?= htmlspecialchars($admin_name) ?></strong>
    </div>

    <?php if ($success): ?>
        <div class="message success"><?= htmlspecialchars($success) ?></div>
    <?php elseif ($error): ?>
        <div class="message error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <label for="user_id">Assign To</label>
        <select name="user_id" required>
            <option value="">-- Select User --</option>
            <?php while ($user = $users->fetch_assoc()): ?>
                <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['name']) ?> (<?= htmlspecialchars($user['email']) ?>)</option>
            <?php endwhile; ?>
        </select>

        <label for="title">Task Title</label>
        <input type="text" name="title" required>

        <label for="description">Task Description</label>
        <textarea name="description" rows="4" required></textarea>

        <label for="deadline">Deadline</label>
        <input type="date" name="deadline" required>

        <button type="submit" class="btn">Assign Task</button>
    </form>

    <div class="back">
        <a href="admin-dashboard.php">← Back to Dashboard</a>
    </div>
</div>
</body>
</html>