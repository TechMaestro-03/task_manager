<?php
session_start();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Task.php';
require_once __DIR__ . '/../controllers/TaskController.php';
require_once __DIR__ . '/../controllers/AuthController.php';

if (!AuthController::isUser()) {
    header("Location: login.php");
    exit();
}

$taskController = new TaskController($conn);
$tasks = $taskController->getTasksByUser($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Task Manager</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
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
            font-size:13px;
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
            <a href="dashboard.php" class="nav-item active">
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
            <a href="settings.php" class="nav-item">
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

        <!-- Tasks Section -->
        <section class="tasks-section">
            <div class="section-header">
                <h2 class="section-title">Your Tasks</h2>
                <div class="task-filters">
                    <button class="filter-btn active">All</button>
                    <button class="filter-btn">Pending</button>
                    <button class="filter-btn">In Progress</button>
                    <button class="filter-btn">Completed</button>
                </div>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert success">
                    <i class="fas fa-check-circle"></i>
                    <span><?= $_SESSION['success']; unset($_SESSION['success']); ?></span>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?= $_SESSION['error']; unset($_SESSION['error']); ?></span>
                </div>
            <?php endif; ?>

            <div class="task-grid">
                <?php if ($tasks && $tasks->num_rows > 0): ?>
                    <?php while ($task = $tasks->fetch_assoc()): ?>
                        <div class="task-card <?= $task['status'] ?>">
                            <div class="task-header">
                                <div>
                                    <h3 class="task-title"><?= htmlspecialchars($task['title']) ?></h3>
                                    <span class="task-status status-<?= str_replace(' ', '-', strtolower($task['status'])) ?>">
                                        <?= $task['status'] ?>
                                    </span>
                                </div>
                                <div class="task-actions">
                                    <button class="action-btn btn-outline">
                                        <i class="fas fa-ellipsis-vertical"></i>
                                    </button>
                                </div>
                            </div>
                            <p class="task-description"><?= htmlspecialchars($task['description']) ?></p>
                            <div class="task-meta">
                                <div class="meta-item">
                                    <i class="fas fa-user"></i>
                                    <span>Assigned by: <?= htmlspecialchars($task['created_by']) ?></span>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-calendar-day"></i>
                                    <span class="task-deadline <?= (strtotime($task['deadline']) < time() && $task['status'] != 'completed') ? 'urgent' : '' ?>">
                                        Due: <?= date('M j, Y', strtotime($task['deadline'])) ?>
                                    </span>
                                </div>
                            </div>
                            <div class="task-actions">
                                <?php if ($task['status'] != 'completed'): ?>
                                    <button class="action-btn btn-primary">
                                        <i class="fas fa-check"></i>
                                        <span>Mark Complete</span>
                                    </button>
                                <?php endif; ?>
                                <button class="action-btn btn-outline">
                                    <i class="fas fa-pen"></i>
                                    <span>Edit</span>
                                </button>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-check-double"></i>
                        <h3>No tasks assigned</h3>
                        <p>You're all caught up! Enjoy your free time.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <script>
        // Simple filter functionality
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                
                const filter = btn.textContent.toLowerCase();
                const taskCards = document.querySelectorAll('.task-card');
                
                taskCards.forEach(card => {
                    if (filter === 'all') {
                        card.style.display = 'block';
                    } else {
                        const status = card.classList.contains(filter.replace(' ', '-')) ? 'block' : 'none';
                        card.style.display = status;
                    }
                });
            });
        });
    </script>
</body>
</html>