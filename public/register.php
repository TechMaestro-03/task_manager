<?php
session_start();
require_once __DIR__ . '/../config/db.php';
;

$name = $email = $password = '';
$error = $success = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Check if user already exists
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $error = "User already exists with that email.";
    } else {
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')");
        $stmt->bind_param("sss", $name, $email, $hashed);

        if ($stmt->execute()) {
            $success = "Registration successful. You can now login.";
            header("Location: login.php"); // Optional immediate redirect
            exit();
        } else {
            $error = "Something went wrong. Please try again.";
        }
    }

    $check->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Task Manager</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Poppins', sans-serif;
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f4f7fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .register-container {
            background: #ffffff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 123, 255, 0.2);
            width: 100%;
            max-width: 450px;
        }

        .register-container h2 {
            margin-bottom: 20px;
            color: #007BFF;
            text-align: center;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            font-weight: 600;
            margin-bottom: 5px;
            display: block;
            color: #333;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            outline: none;
            transition: 0.3s;
        }

        input:focus {
            border-color: #007BFF;
        }

        .btn {
            background-color: #007BFF;
            color: white;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        .error, .success {
            text-align: center;
            margin-bottom: 15px;
            font-size: 14px;
        }

        .error { color: red; }
        .success { color: green; }

        .footer-note {
            text-align: center;
            margin-top: 15px;
            font-size: 13px;
            color: #555;
        }

    </style>
</head>
<body>

<div class="register-container">
    <h2>Create Account</h2>
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <form method="POST" action="">
        <div class="form-group">
            <label for="name">Full name</label>
            <input type="text" id="name" name="name" required autocomplete="name">
        </div>
        <div class="form-group">
            <label for="email">Email address</label>
            <input type="email" id="email" name="email" required autocomplete="email">
        </div>
        <div class="form-group">
            <label for="password">Create password</label>
            <input type="password" id="password" name="password" required autocomplete="new-password">
        </div>
        <button type="submit" class="btn">Register</button>
    </form>
    <div class="footer-note">
        Already have an account? <a href="login.php">Login</a>
    </div>
</div>

</body>
</html>
