<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Task.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/TaskController.php';
require_once __DIR__ . '/../controllers/UserController.php';

// Check if user is admin
if (!AuthController::isAdmin()) {
    header("Location: login.php");
    exit();
}

$taskController = new TaskController($conn);

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_task_id'])) {
    $taskId = (int)$_POST['delete_task_id'];
    
    try {
        if ($taskController->deleteTask($taskId)) {
            $_SESSION['success'] = "Task deleted successfully!";
        } else {
            $_SESSION['error'] = "Failed to delete task.";
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error deleting task: " . $e->getMessage();
    }
    
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// Handle status change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_id']) && isset($_POST['status'])) {
    $taskId = (int)$_POST['task_id'];
    $newStatus = $_POST['status'];
    
    try {
        if ($taskController->updateTaskStatus($taskId, $newStatus)) {
            $_SESSION['success'] = "Task status updated successfully!";
        } else {
            $_SESSION['error'] = "Failed to update task status.";
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error updating task: " . $e->getMessage();
    }
    
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// Get all tasks
$tasks = $conn->query("SELECT tasks.*, 
                      assignees.name as assignee_name, 
                      creators.name as creator_name 
                      FROM tasks 
                      LEFT JOIN users assignees ON tasks.user_id = assignees.id
                      LEFT JOIN users creators ON tasks.created_by = creators.id
                      ORDER BY deadline ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Task Manager</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
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
        }

        .admin-header {
            background: var(--sidebar-bg);
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .admin-nav {
            display: flex;
            gap: 1.5rem;
        }

        .admin-nav a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            transition: var(--transition);
        }

        .admin-nav a:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .admin-container {
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .dashboard-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text);
        }

        .stats-container {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .stat-card {
            background: white;
            padding: 1.25rem;
            border-radius: 10px;
            box-shadow: var(--shadow);
            min-width: 200px;
            text-align: center;
        }

        .stat-value {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.25rem;
        }

        .stat-label {
            font-size: 0.9rem;
            color: var(--text-light);
        }

        .task-table-container {
            background: white;
            border-radius: 10px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .task-table {
            width: 100%;
            border-collapse: collapse;
        }

        .task-table th {
            background: var(--primary);
            color: white;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
        }

        .task-table td {
            padding: 1rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            vertical-align: middle;
        }

        .status-badge {
            display: inline-block;
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: capitalize;
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

        .action-btns {
            display: flex;
            gap: 0.5rem;
        }

        .btn {
            padding: 0.5rem 0.75rem;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-sm {
            padding: 0.35rem 0.65rem;
            font-size: 0.8rem;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        .btn-danger {
            background: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            background: #e01a6f;
        }

        .status-dropdown {
            position: relative;
            display: inline-block;
        }

        .status-dropdown-toggle {
            background: transparent;
            border: none;
            cursor: pointer;
            padding: 0;
            font-size: inherit;
            font-weight: inherit;
            color: inherit;
        }

        .status-dropdown-menu {
            position: absolute;
            top: 100%;
            left: 0;
            z-index: 1000;
            display: none;
            min-width: 160px;
            padding: 0.5rem 0;
            margin: 0.125rem 0 0;
            font-size: 0.85rem;
            color: var(--text);
            background-color: white;
            border: 1px solid rgba(0, 0, 0, 0.15);
            border-radius: 6px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .status-dropdown-menu.show {
            display: block;
        }

        .status-dropdown-item {
            display: block;
            width: 100%;
            padding: 0.5rem 1rem;
            font-weight: 400;
            color: var(--text);
            background-color: transparent;
            border: none;
            cursor: pointer;
        }

        .status-dropdown-item:hover {
            background-color: rgba(67, 97, 238, 0.05);
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-success {
            background: rgba(76, 201, 240, 0.1);
            color: var(--success);
            border-left: 4px solid var(--success);
        }

        .alert-error {
            background: rgba(247, 37, 133, 0.1);
            color: var(--danger);
            border-left: 4px solid var(--danger);
        }

        .empty-state {
            text-align: center;
            padding: 3rem 0;
            color: var(--text-light);
        }

        @media (max-width: 768px) {
            .admin-container {
                padding: 1.5rem;
            }
            
            .task-table {
                display: block;
                overflow-x: auto;
            }
            
            .admin-nav a span {
                display: none;
            }
        }
    </style>
</head>
<body>
    <header class="admin-header">
        <div class="brand">
            <i class="fas fa-tasks"></i>
            <span>Admin Dashboard</span>
        </div>
        <nav class="admin-nav">
            <a href="assign-task.php"><i class="fas fa-plus-circle"></i><span>Assign Task</span></a>
            <a href="register.php"><i class="fas fa-user-plus"></i><span>Add User</span></a>
            <a href="users.php"><i class="fas fa-users"></i><span>Manage Users</span></a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
        </nav>
    </header>

    <main class="admin-container">
        <div class="dashboard-header">
            <h1 class="dashboard-title">Task Management</h1>
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-value"><?= $conn->query("SELECT COUNT(*) FROM tasks")->fetch_row()[0] ?></div>
                    <div class="stat-label">Total Tasks</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= $conn->query("SELECT COUNT(*) FROM tasks WHERE status = 'Pending'")->fetch_row()[0] ?></div>
                    <div class="stat-label">Pending</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= $conn->query("SELECT COUNT(*) FROM tasks WHERE status = 'In Progress'")->fetch_row()[0] ?></div>
                    <div class="stat-label">In Progress</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= $conn->query("SELECT COUNT(*) FROM tasks WHERE status = 'Completed'")->fetch_row()[0] ?></div>
                    <div class="stat-label">Completed</div>
                </div>
            </div>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span><?= $_SESSION['success']; unset($_SESSION['success']); ?></span>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?= $_SESSION['error']; unset($_SESSION['error']); ?></span>
            </div>
        <?php endif; ?>

        <div class="task-table-container">
            <?php if ($tasks->num_rows > 0): ?>
                <table class="task-table">
                    <thead>
                        <tr>
                            <th>Task</th>
                            <th>Assigned To</th>
                            <th>Created By</th>
                            <th>Status</th>
                            <th>Deadline</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($task = $tasks->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($task['title']) ?></strong><br>
                                    <small style="color: var(--text-light)"><?= htmlspecialchars(substr($task['description'], 0, 50)) ?>...</small>
                                </td>
                                <td><?= htmlspecialchars($task['assignee_name'] ?? 'Unassigned') ?></td>
                                <td><?= htmlspecialchars($task['creator_name'] ?? 'System') ?></td>
                                <td>
                                    <div class="status-dropdown">
                                        <button class="status-dropdown-toggle status-badge status-<?= str_replace(' ', '-', strtolower($task['status'])) ?>">
                                            <?= htmlspecialchars($task['status']) ?>
                                            <i class="fas fa-caret-down" style="margin-left: 5px;"></i>
                                        </button>
                                        <div class="status-dropdown-menu">
                                            <form method="POST" action="">
                                                <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                                                <button type="submit" name="status" value="Pending" class="status-dropdown-item status-pending">Pending</button>
                                                <button type="submit" name="status" value="In Progress" class="status-dropdown-item status-in-progress">In Progress</button>
                                                <button type="submit" name="status" value="Completed" class="status-dropdown-item status-completed">Completed</button>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?= date('M d, Y', strtotime($task['deadline'])) ?>
                                    <?php if (strtotime($task['deadline']) < time() && $task['status'] != 'Completed'): ?>
                                        <br><small style="color: var(--danger)">Overdue</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <a href="edit-task.php?id=<?= $task['id'] ?>" class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" action="" onsubmit="return confirm('Are you sure you want to delete this task?');">
                                            <input type="hidden" name="delete_task_id" value="<?= $task['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-tasks"></i>
                    <h3>No tasks assigned yet</h3>
                    <p>Get started by assigning a new task to your team</p>
                    <a href="assign-task.php" class="btn btn-primary" style="margin-top: 1rem;">
                        <i class="fas fa-plus-circle"></i> Assign Task
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        document.querySelectorAll('.status-dropdown-toggle').forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                const dropdownMenu = this.nextElementSibling;
                dropdownMenu.classList.toggle('show');
                
                document.querySelectorAll('.status-dropdown-menu').forEach(menu => {
                    if (menu !== dropdownMenu) {
                        menu.classList.remove('show');
                    }
                });
            });
        });

        document.addEventListener('click', function(e) {
            if (!e.target.matches('.status-dropdown-toggle') && !e.target.closest('.status-dropdown-menu')) {
                document.querySelectorAll('.status-dropdown-menu').forEach(menu => {
                    menu.classList.remove('show');
                });
            }
        });
    </script>
</body>
