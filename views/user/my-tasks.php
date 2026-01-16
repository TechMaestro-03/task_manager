<?php
require_once 'config/db.php';
require_once 'controllers/AuthController.php';
require_once 'controllers/TaskController.php';

session_start();

// Ensure only logged-in users (not admin)
if (!AuthController::isUser()) {
    header("Location: auth/login.php");
    exit();
}

$taskController = new TaskController($conn);
$tasks = $taskController->getTasksByUser($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Tasks</title>
  <link rel="stylesheet" href="assets/css/styles.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap">
</head>
<body>

<?php include 'components/header.php'; ?>

<div class="container">
  <h1>My Tasks</h1>

  <?php if (isset($_SESSION['success'])): ?>
    <div class="feedback success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
  <?php endif; ?>

  <?php if (isset($_SESSION['error'])): ?>
    <div class="feedback error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
  <?php endif; ?>

  <?php if ($tasks && $tasks->num_rows > 0): ?>
    <?php while ($task = $tasks->fetch_assoc()): ?>
      <?php include 'components/taskcard.php'; ?>
    <?php endwhile; ?>
  <?php else: ?>
    <p>You currently have no assigned tasks.</p>
  <?php endif; ?>
</div>

<?php include 'components/footer.php'; ?>
<script src="assets/js/main.js"></script>
</body>
</html>
