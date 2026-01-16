<?php
session_start();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../controllers/AuthController.php';

// Ensure user is logged in
if (!AuthController::isUser()) {
    header("Location: login.php");
    exit();
}

$userModel = new User($conn);
$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';

    if (empty($password)) {
        $_SESSION['error'] = "Password is required to deactivate your account.";
    } else {
        $user = $userModel->getUserById($userId);

        if (!$user || !password_verify($password, $user['password'])) {
            $_SESSION['error'] = "Incorrect password.";
        } else {
            // Soft deactivate account
            $stmt = $conn->prepare("UPDATE users SET active = 0 WHERE id = ?");
            $stmt->bind_param("i", $userId);

            if ($stmt->execute()) {
                session_destroy();
                header("Location: login.php?deactivated=1");
                exit();
            } else {
                $_SESSION['error'] = "Failed to deactivate account.";
            }
        }
    }

    header("Location: deactivate_account.php");
    exit();
}

$theme = $_SESSION['theme'] ?? 'light';
?>

<!DOCTYPE html>
<html lang="en" data-theme="<?= $theme ?>">
<head>
    <meta charset="UTF-8">
    <title>Deactivate Account | TaskFlow</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">

    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #e6e9ff;
            --primary-dark: #3a56d4;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --warning: #f8961e;
            --danger: #f72585;
            --text: #2b2d42;
            --text-light: #8d99ae;
            --bg: #f8f9fa;
            --card-bg: #ffffff;
            --sidebar-bg: #2b2d42;
            --sidebar-text: #f8f9fa;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: var(--bg);
            color: var(--text);
            display: flex;
            font-size:13px;
            min-height: 100vh;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 2rem;
            transition: var(--transition);
        }

        /* Top Bar */
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

    
        /* Responsive */
        @media (max-width: 1024px) {
            
            .main-content {
                margin-left: 240px;
            }
        }

        @media (max-width: 768px) {
            
            .main-content {
                margin-left: 0;
                padding: 1.5rem;
            }
            
        }
        
        .danger-card {
            max-width: 520px;
            background: #fff;
            padding: 2.5rem;
            padding-left:30px;
            margin-top:20px;
            border-radius: 14px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
            border-left: 5px solid #f72585;
        }
        .danger-card h3 {
            color: #f72585;
            margin-bottom: 0.75rem;
        }
        .danger-card p {
            color: #555;
            margin-bottom: 1.5rem;
        }
        .danger-btn {
            width: 100%;
            background: #f72585;
            color: white;
            padding: 0.75rem;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            cursor: pointer;
        }
        .danger-btn:hover {
            background: #d61c6f;
        }
        .form-group {
            margin-bottom: 1.25rem;
        }
        input {
            width: 100%;
            padding: 0.75rem;
            margin-top:5px;
            border-radius: 8px;
            border: 1px solid #ccc;
        }
    </style>
</head>

<body>
    <!-- Main Content -->
    <main class="main-content">
        <div class="top-bar">
            <div class="user-info">
                <div class="user-name">Hi, <?= htmlspecialchars($_SESSION['name']) ?></div>
            </div>
            <a href="logout.php" class="logout btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>

        <center>
        <h2 style="color:#f72585;">Deactivate Account</h2>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert error">
        <i class="fas fa-exclamation-circle"></i>
        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>

<div class="danger-card">
    <h3><i class="fas fa-exclamation-triangle"></i> This action is permanent</h3>
    <p>
        Deactivating your account will disable access immediately.
        You can only restore it by contacting support.
    </p>

    <form method="POST">
        <div class="form-group">
            <label>Confirm your password</label>
            <input type="password" name="password" required>
        </div>

        <button class="danger-btn" type="submit">
            <i class="fas fa-user-slash"></i> Deactivate My Account
        </button>
    </form>
</div>
        </center>
    </main>
</body>
</html>
