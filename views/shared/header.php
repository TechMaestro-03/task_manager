<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
?>

<header class="navbar">
  <div class="logo"><strong>Task Manager</strong></div>
  <nav>
    <?php if (isset($_SESSION['role'])): ?>
      <?php if ($_SESSION['role'] === 'admin'): ?>
        <a href="admin-dashboard.php">Dashboard</a>
        <a href="assign-task.php">Assign Task</a>
        <a href="register.php">Add User</a>
      <?php elseif ($_SESSION['role'] === 'user'): ?>
        <a href="user-dashboard.php">Dashboard</a>
      <?php endif; ?>
      <a href="logout.php">Logout</a>
    <?php else: ?>
      <a href="index.php">Home</a>
      <a href="auth/login.php">Login</a>
    <?php endif; ?>
  </nav>
</header>

<style>
.navbar {
  background-color: #007BFF;
  color: white;
  padding: 15px 30px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-family: 'Poppins', sans-serif;
}
.navbar a {
  color: white;
  margin-left: 15px;
  font-weight: 500;
  text-decoration: none;
}
.navbar a:hover {
  text-decoration: underline;
}
.logo {
  font-weight: 600;
  font-size: 18px;
}
@media (max-width: 600px) {
  .navbar {
    flex-direction: column;
    align-items: flex-start;
  }
  .navbar a {
    display: block;
    margin: 5px 0;
  }
}
</style>
