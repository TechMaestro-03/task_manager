<?php
session_start();
require_once __DIR__ . '/../config/db.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];

            // Redirect based on role
            header("Location: " . ($user['role'] === 'admin' ? 'admin-dashboard.php' : 'user-dashboard.php'));
            exit;
        } else {
            $error = "Invalid email or password.";
        }
    } else {
        $error = "No user found with that email.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Task Manager</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { margin: 0; background: #f4f7fa; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .login-container { background: #fff; padding: 40px; border-radius: 10px; box-shadow: 0 10px 25px rgba(0,123,255,0.2); width: 100%; max-width: 400px; }
        .login-container h2 { margin-bottom: 20px; color: #007BFF; text-align: center; }
        .form-group { margin-bottom: 20px; }
        label { font-weight: 600; margin-bottom: 5px; display: block; color: #333; }
        input[type="email"], input[type="password"] { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 6px; outline: none; transition: 0.3s; }
        input:focus { border-color: #007BFF; }
        .btn { background-color: #007BFF; color: #fff; border: none; padding: 12px; width: 100%; border-radius: 6px; cursor: pointer; font-weight: 600; }
        .btn:hover { background-color: #0056b3; }
        .error { color: red; text-align: center; margin-bottom: 15px; font-size: 14px; }
        .footer-note { text-align: center; margin-top: 15px; font-size: 13px; color: #555; }
        @media (max-width: 500px) { .login-container { padding: 30px 20px; } }
    </style>
</head>
<body>

<div class="login-container">
    <h2>Login</h2>
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST" action="">
        <div class="form-group">
            <label for="email">Email address</label>
            <input type="email" id="email" name="email" required autocomplete="email">
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required autocomplete="current-password">
        </div>
        <button type="submit" class="btn">Sign In</button>
    </form>
    <div class="footer-note">
        Don't have an account? Contact Admin.
    </div>
</div>

</body>
</html>
