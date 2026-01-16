<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../controllers/AuthController.php';

if (!AuthController::isAdmin()) {
    header("Location: login.php");
    exit();
}

// Handle user deletion
if (isset($_GET['delete'])) {
    $userId = intval($_GET['delete']);
    if ($userId > 0 && $userId != $_SESSION['user_id']) { // Prevent self-deletion
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->close();
        $_SESSION['message'] = "User deleted successfully!";
        header("Location: manage-users.php");
        exit();
    } else {
        $_SESSION['error'] = "Cannot delete your own account or invalid user!";
    }
}

// Handle user addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    
    // Basic validation
    if (empty($name) || empty($email) || empty($password)) {
        $_SESSION['error'] = "All fields are required!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format!";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $_SESSION['error'] = "Email already exists!";
        } else {
            // Hash the password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $hashedPassword, $role);
            
            if ($stmt->execute()) {
                $_SESSION['message'] = "User added successfully!";
                header("Location: manage-users.php");
                exit();
            } else {
                $_SESSION['error'] = "Error adding user: " . $conn->error;
            }
        }
        $stmt->close();
    }
}

$users = $conn->query("SELECT id, name, email, role FROM users ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Users - Admin Panel</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <style>
    * {
      box-sizing: border-box;
      font-family: 'Inter', sans-serif;
      font-size:13px;
    }

    body {
      margin: 0;
      background: #f8f9fa;
      color: #333;
    }

    .navbar {
      background-color: #343a40;
      color: white;
      padding: 15px 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .navbar a {
      color: white;
      margin-left: 20px;
      text-decoration: none;
      font-weight: 500;
    }

    .navbar a:hover {
      text-decoration: underline;
    }
          .nav-links {
        display: flex;
        align-items: center;
        gap: 20px;
      }

      .nav-links a {
        display: flex;
        align-items: center;
        gap: 8px;
        color: white;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.3s;
      }

      .nav-links a i {
        font-size: 14px;
      }

      .nav-links a:hover {
        color: #0d6efd;
      }


    .container {
      max-width: 1000px;
      margin: 40px auto;
      background: white;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 10px 20px rgba(0,0,0,0.05);
    }

    h1 {
      margin-bottom: 25px;
      color: #007bff;
      font-size: 20px;
      font-weight:300;
      text-align: center;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 30px;
    }

    thead {
      background-color: #007bff;
      color: white;
    }

    th, td {
      padding: 14px 12px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }

    tr:nth-child(even) {
      background-color: #f2f2f2;
    }

    tr:hover {
      background-color: #eef6ff;
    }

    .btn {
      padding: 8px 10px;
      border: none;
      border-radius: 4px;
      text-align:center;
      cursor: pointer;
      font-weight: bold;
      text-decoration:none;
      transition: all 0.3s;
    }

    .btn-danger {
      background-color: #dc3545;
      color: white;
    }

    .btn-danger:hover {
      background-color: #c82333;
    }

    .btn-primary {
      background-color: #007bff;
      color: white;
    }

    .btn-primary:hover {
      background-color: #0069d9;
    }

    .add-user-form {
      background: #f8f9fa;
      padding: 20px;
      border-radius: 8px;
      margin-bottom: 30px;
    }

    .form-group {
      margin-bottom: 15px;
    }

    .form-group label {
      display: block;
      margin-bottom: 5px;
      font-weight: 500;
    }

    .form-control {
      width: 100%;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 16px;
    }

    .alert {
      padding: 15px;
      margin-bottom: 20px;
      border-radius: 4px;
    }

    .alert-success {
      background-color: #d4edda;
      color: #155724;
    }

    .alert-danger {
      background-color: #f8d7da;
      color: #721c24;
    }

    @media (max-width: 768px) {
      table, thead, tbody, th, td, tr {
        display: block;
      }

      thead {
        display: none;
      }

      tr {
        margin-bottom: 20px;
        background: #f9f9f9;
        padding: 15px;
        border-radius: 8px;
      }

      td {
        padding: 10px;
        border: none;
        display: flex;
        justify-content: space-between;
      }

      td::before {
        content: attr(data-label);
        font-weight: bold;
      }
    }
     @media (max-width: 768px) {
      .nav-links a span {
        display: none;
      }

      .nav-links a i {
        font-size: 18px;
      }
    }

  </style>
</head>
<body>

  <div class="navbar">
    <div><strong>Task Manager - Admin</strong></div>
    <div class="nav-links">
  <a href="admin-dashboard.php">
    <i class="fa-solid fa-chart-line"></i>
    <span>Dashboard</span>
  </a>

  <a href="assign-task.php">
    <i class="fas fa-user-plus"></i>
    <span>Assign Task</span>
  </a>

  <a href="logout.php">
    <i class="fa-solid fa-right-from-bracket"></i>
    <span>Logout</span>
  </a>
</div>

  </div>

  <div class="container">
    <h1>Manage Users</h1>

    <?php if (isset($_SESSION['message'])): ?>
      <div class="alert alert-success">
        <?= $_SESSION['message']; unset($_SESSION['message']); ?>
      </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
      <div class="alert alert-danger">
        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
      </div>
    <?php endif; ?>

    

    <?php if ($users->num_rows > 0): ?>
      <table>
        <thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($user = $users->fetch_assoc()): ?>
            <tr>
              <td data-label="Name"><?= htmlspecialchars($user['name']) ?></td>
              <td data-label="Email"><?= htmlspecialchars($user['email']) ?></td>
              <td data-label="Role"><?= htmlspecialchars($user['role']) ?></td>
              <td data-label="Actions">
                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                  <a href="?delete=<?= $user['id'] ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                <?php else: ?>
                  <span style="color: #6c757d;">Current User</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p style="text-align:center; color: #555;">No users found.</p>
    <?php endif; ?>
    <div class="add-user-form">
      <h2 style="margin-top: 0; color: #495057;">Add New User</h2>
      <form method="POST" action="">
        <div class="form-group">
          <label for="name">Full Name</label>
          <input type="text" id="name" name="name" class="form-control" required>
        </div>
        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" class="form-control" required>
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" class="form-control" required>
        </div>
        <div class="form-group">
          <label for="role">Role</label>
          <select id="role" name="role" class="form-control" required>
            <option value="user">User</option>
            <option value="admin">Admin</option>
          </select>
        </div>
        <button type="submit" name="add_user" class="btn btn-primary">Add User</button>
      </form>
    </div>
  </div>

  
</body>
</html>
