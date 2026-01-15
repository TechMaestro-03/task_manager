<?php
require_once __DIR__ . '/../config/db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'admin')");
    $stmt->bind_param("sss", $name, $email, $password);

    if ($stmt->execute()) {
        $message = "✅ Admin registered successfully. You may now login.";
    } else {
        $message = "❌ Failed to register admin: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    * {
      font-family: 'Poppins', sans-serif;
      box-sizing: border-box;
    }

    body {
      background: #f4f7fa;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .register-box {
      background: white;
      padding: 40px;
      border-radius: 10px;
      box-shadow: 0 10px 30px rgba(0, 123, 255, 0.1);
      width: 100%;
      max-width: 400px;
    }

    h2 {
      color: #007BFF;
      text-align: center;
      margin-bottom: 25px;
    }

    input[type="text"],
    input[type="email"],
    input[type="password"] {
      width: 100%;
      padding: 12px;
      margin-bottom: 20px;
      border: 1px solid #ccc;
      border-radius: 6px;
    }

    .btn {
      background: #007BFF;
      color: white;
      padding: 12px;
      width: 100%;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-weight: bold;
    }

    .btn:hover {
      background: #0056b3;
    }

    .message {
      text-align: center;
      margin-top: 15px;
      color: green;
    }

    .error {
      color: red;
    }
  </style>
</head>
<body>

<div class="register-box">
  <h2>Register Admin</h2>
  <?php if ($message): ?>
    <div class="message"><?= htmlspecialchars($message); ?></div>
  <?php endif; ?>
  <form method="POST" action="">
    <input type="text" name="name" placeholder="Full Name" required>
    <input type="email" name="email" placeholder="Email Address" required>
    <input type="password" name="password" placeholder="Password" required>
    <button class="btn" type="submit">Register Admin</button>
  </form>
</div>

</body>
</html>
