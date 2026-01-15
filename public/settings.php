<?php
session_start();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../controllers/AuthController.php';

if (!AuthController::isUser()) {
    header("Location: login.php");
    exit();
}

$userModel = new User($conn);
$user = $userModel->getUserById($_SESSION['user_id']);

// Handle notification preferences update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_notifications'])) {
    $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
    $push_notifications = isset($_POST['push_notifications']) ? 1 : 0;
    $daily_summary = isset($_POST['daily_summary']) ? 1 : 0;

    $success = $userModel->updateNotificationPreferences(
        $_SESSION['user_id'],
        $email_notifications,
        $push_notifications,
        $daily_summary
    );

    if ($success) {
        $_SESSION['success'] = "Notification preferences updated successfully!";
    } else {
        $_SESSION['error'] = "Failed to update notification preferences";
    }
    header("Location: settings.php");
    exit();
}

// Handle theme preference update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_theme'])) {
    $theme = $_POST['theme'];
    
    if (in_array($theme, ['light', 'dark', 'system'])) {
        $success = $userModel->updateThemePreference($_SESSION['user_id'], $theme);
        
        if ($success) {
            $_SESSION['success'] = "Theme preference updated successfully!";
            $_SESSION['theme'] = $theme; // Update session
        } else {
            $_SESSION['error'] = "Failed to update theme preference";
        }
    } else {
        $_SESSION['error'] = "Invalid theme selection";
    }
    header("Location: settings.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="<?= $_SESSION['theme'] ?? 'light' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings | Task Manager</title>
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
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background: var(--sidebar-bg);
            color: var(--sidebar-text);
            padding: 2rem 1.5rem;
            position: fixed;
            height: 100vh;
            transition: var(--transition);
            z-index: 100;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 2.5rem;
        }

        .brand-icon {
            font-size: 1.75rem;
            color: var(--primary);
        }

        .brand-text {
            font-size: 1.25rem;
            font-weight: 700;
        }

        .nav-menu {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-top: 2rem;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            color: var(--sidebar-text);
        }

        .nav-item:hover, .nav-item.active {
            background: rgba(255, 255, 255, 0.1);
        }

        .nav-item i {
            font-size: 1.1rem;
            width: 24px;
            text-align: center;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
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

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            text-transform: uppercase;
        }

        .user-name {
            font-weight: 600;
        }

        .logout-btn {
            background: var(--primary-light);
            color: var(--primary);
            border: none;
            padding: 0.5rem 1.25rem;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logout-btn:hover {
            background: var(--primary);
            color: white;
        }

        /* Tasks Section */
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .task-filters {
            display: flex;
            gap: 0.75rem;
        }

        .filter-btn {
            padding: 0.5rem 1rem;
            border-radius: 6px;
            background: var(--primary-light);
            color: var(--primary);
            border: none;
            cursor: pointer;
            font-size: 0.85rem;
            transition: var(--transition);
        }

        .filter-btn:hover, .filter-btn.active {
            background: var(--primary);
            color: white;
        }

        .task-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        /* Task Card */
        .task-card {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
            transition: var(--transition);
            border-left: 4px solid var(--primary);
        }

        .task-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .task-card.pending {
            border-left-color: var(--warning);
        }

        .task-card.in-progress {
            border-left-color: var(--primary);
        }

        .task-card.completed {
            border-left-color: var(--success);
        }

        .task-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .task-title {
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 0.25rem;
        }

        .task-status {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending {
            background: rgba(248, 150, 30, 0.1);
            color: var(--warning);
        }

        .status-in-progress {
            background: rgba(67, 97, 238, 0.1);
            color: var(--primary);
        }

        .status-completed {
            background: rgba(76, 201, 240, 0.1);
            color: var(--success);
        }

        .task-description {
            color: var(--text-light);
            font-size: 0.9rem;
            margin-bottom: 1.25rem;
            line-height: 1.5;
        }

        .task-meta {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
        }

        .meta-item i {
            color: var(--text-light);
            width: 20px;
            text-align: center;
        }

        .task-deadline {
            color: var(--text-light);
        }

        .task-deadline.urgent {
            color: var(--danger);
            font-weight: 500;
        }

        .task-actions {
            display: flex;
            gap: 0.75rem;
        }

        .action-btn {
            padding: 0.5rem 1rem;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 0.85rem;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--text-light);
            color: var(--text-light);
        }

        .btn-outline:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        /* Alerts */
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert i {
            font-size: 1.1rem;
        }

        .alert.success {
            background: rgba(76, 201, 240, 0.1);
            color: var(--success);
            border-left: 4px solid var(--success);
        }

        .alert.error {
            background: rgba(247, 37, 133, 0.1);
            color: var(--danger);
            border-left: 4px solid var(--danger);
        }

        /* Empty State */
        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 3rem 0;
            color: var(--text-light);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--primary-light);
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .sidebar {
                width: 240px;
                padding: 1.5rem 1rem;
            }
            .main-content {
                margin-left: 240px;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                width: 280px;
            }
            .sidebar.active {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
                padding: 1.5rem;
            }
            .task-grid {
                grid-template-columns: 1fr;
            }
        }
        .settings-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .settings-card {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 2rem;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
        }

        .settings-header {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .settings-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .settings-description {
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .settings-form {
            display: grid;
            gap: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .checkbox-group input[type="checkbox"] {
            width: auto;
        }

        .radio-group {
            display: flex;
            gap: 1.5rem;
        }

        .radio-option {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-save {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            width: fit-content;
        }

        .btn-save:hover {
            background: var(--primary-dark);
        }

        /* Theme-specific variables */
        body[data-theme="light"] {
            --bg: #f8f9fa;
            --card-bg: #ffffff;
            --text: #2b2d42;
            --text-light: #8d99ae;
        }

        body[data-theme="dark"] {
            --bg: #1a1a1a;
            --card-bg: #2d2d2d;
            --text: #f8f9fa;
            --text-light: #b0b0b0;
            --sidebar-bg: #1a1a1a;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="brand">
            <div class="brand-icon">
                <i class="fas fa-tasks"></i>
            </div>
            <div class="brand-text">TaskFlow</div>
        </div>

        <nav class="nav-menu">
            <a href="user-dashboard.php" class="nav-item">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            <a href="calendar.php" class="nav-item">
                <i class="fas fa-calendar"></i>
                <span>Calendar</span>
            </a>
            <a href="profile.php" class="nav-item">
                <i class="fas fa-user"></i>
                <span>Profile</span>
            </a>
            <a href="settings.php" class="nav-item active">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <div class="user-info">
                <div class="user-avatar"><?= substr($_SESSION['name'], 0, 1) ?></div>
                <div class="user-name">Hi, <?= htmlspecialchars($_SESSION['name']) ?></div>
            </div>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>

        <!-- Settings Section -->
        <section class="settings-section">
            <div class="section-header">
                <h2 class="section-title">Settings</h2>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?= $_SESSION['error']; unset($_SESSION['error']); ?></span>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert success">
                    <i class="fas fa-check-circle"></i>
                    <span><?= $_SESSION['success']; unset($_SESSION['success']); ?></span>
                </div>
            <?php endif; ?>

            <div class="settings-container">
                <!-- Notification Settings -->
                <div class="settings-card">
                    <div class="settings-header">
                        <h3 class="settings-title">Notification Preferences</h3>
                        <p class="settings-description">Manage how you receive notifications from TaskFlow</p>
                    </div>
                    <form class="settings-form" method="POST" action="settings.php">
                        <div class="form-group">
                            <div class="checkbox-group">
                                <input type="checkbox" id="email_notifications" name="email_notifications" 
                                    <?= ($user['email_notifications'] ?? 1) ? 'checked' : '' ?>>
                                <label for="email_notifications">Email Notifications</label>
                            </div>
                            <div class="checkbox-group">
                                <input type="checkbox" id="push_notifications" name="push_notifications"
                                    <?= ($user['push_notifications'] ?? 1) ? 'checked' : '' ?>>
                                <label for="push_notifications">Push Notifications</label>
                            </div>
                            <div class="checkbox-group">
                                <input type="checkbox" id="daily_summary" name="daily_summary"
                                    <?= ($user['daily_summary'] ?? 1) ? 'checked' : '' ?>>
                                <label for="daily_summary">Daily Summary Email</label>
                            </div>
                        </div>
                        <button type="submit" name="update_notifications" class="btn-save">Save Preferences</button>
                    </form>
                </div>

                <!-- Theme Settings -->
                <div class="settings-card">
                    <div class="settings-header">
                        <h3 class="settings-title">Appearance</h3>
                        <p class="settings-description">Customize how TaskFlow looks</p>
                    </div>
                    <form class="settings-form" method="POST" action="settings.php">
                        <div class="form-group">
                            <div class="radio-group">
                                <div class="radio-option">
                                    <input type="radio" id="light-theme" name="theme" value="light"
                                        <?= ($_SESSION['theme'] ?? 'light') === 'light' ? 'checked' : '' ?>>
                                    <label for="light-theme">Light</label>
                                </div>
                                <div class="radio-option">
                                    <input type="radio" id="dark-theme" name="theme" value="dark"
                                        <?= ($_SESSION['theme'] ?? 'light') === 'dark' ? 'checked' : '' ?>>
                                    <label for="dark-theme">Dark</label>
                                </div>
                                <div class="radio-option">
                                    <input type="radio" id="system-theme" name="theme" value="system"
                                        <?= ($_SESSION['theme'] ?? 'light') === 'system' ? 'checked' : '' ?>>
                                    <label for="system-theme">System Default</label>
                                </div>
                            </div>
                        </div>
                        <button type="submit" name="update_theme" class="btn-save">Save Theme</button>
                    </form>
                </div>

                <!-- Account Actions -->
                <div class="settings-card">
                    <div class="settings-header">
                        <h3 class="settings-title">Account Actions</h3>
                        <p class="settings-description">Manage your account</p>
                    </div>
                    <div class="settings-form">
                        <div class="form-group">
                            <a href="change_password.php" class="action-btn btn-outline">
                                <i class="fas fa-key"></i>
                                <span>Change Password</span>
                            </a>
                            <a href="deactivate_account.php" class="action-btn btn-outline" style="color: var(--danger);">
                                <i class="fas fa-user-slash"></i>
                                <span>Deactivate Account</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script>
        // Apply theme immediately when changed
        document.querySelectorAll('input[name="theme"]').forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.checked) {
                    document.documentElement.setAttribute('data-theme', this.value);
                }
            });
        });
    </script>
</body>
</html>