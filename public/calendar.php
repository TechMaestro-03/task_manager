<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../controllers/AuthController.php';

if (!AuthController::isUser() && !AuthController::isAdmin()) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$role = $_SESSION['role'];
$filter = "";

// Filter handling
if (isset($_GET['status'])) {
    $status = $conn->real_escape_string($_GET['status']);
    $filter = " AND status = '$status'";
}

if ($role === 'admin') {
    $query = "SELECT id, title, deadline, status, description FROM tasks WHERE 1 $filter";
} else {
    $query = "SELECT id, title, deadline, status, description FROM tasks WHERE user_id = $userId $filter";
}

$result = $conn->query($query);

$events = [];
while ($task = $result->fetch_assoc()) {
    $color = match($task['status']) {
        'completed' => '#28a745',
        'in progress' => '#ffc107',
        'pending' => '#dc3545',
        default => '#007bff'
    };

    $events[] = [
        'id' => $task['id'],
        'title' => $task['title'],
        'start' => $task['deadline'],
        'color' => $color,
        'url' => "#taskModal",
        'extendedProps' => [
            'description' => $task['description'],
            'status' => $task['status'],
        ]
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Task Calendar</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css">
  <link rel="stylesheet" href="/assets/css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">


  <style>
    <style>
/* Base */
* {
  box-sizing: border-box;
  font-family: 'Inter', sans-serif;
  font-size: 13px;
}

body {
  background: #f8f9fa;
  margin: 0;
  padding: 0;
}

/* Container */
.container {
  max-width: 1100px;
  margin: 30px auto;
  padding: 24px;
  background: #ffffff;
  border-radius: 14px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.08);
}

/* Header */
.container h1 {
  font-size: 1.5rem;
  font-weight: 600;
  margin-bottom: 1rem;
  color: #2b2d42;
}

/* Filter */
form {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1.5rem;
}

label {
  font-weight: 500;
  color: #555;
}

select {
  padding: 0.45rem 0.75rem;
  border-radius: 6px;
  border: 1px solid #ddd;
  background: #f8f9fa;
  cursor: pointer;
  transition: 0.2s ease;
}

select:hover,
select:focus {
  border-color: #4361ee;
  outline: none;
}

/* Calendar */
#calendar {
  margin-top: 1rem;
}

/* FullCalendar overrides */
.fc {
  font-size: 0.75rem;

  
}

.fc-toolbar-title {
  font-size: 1.1rem;
  font-weight: 600;
  color: #2b2d42;
}

.fc-button {
  background: #4361ee !important;
  border: none !important;
  border-radius: 6px !important;
  padding: 0.4rem 0.75rem !important;
  font-size: 0.8rem !important;
  transition: 0.2s ease;
}

.fc-button:hover {
  background: #3a56d4 !important;
}

.fc-button:disabled {
  background: #e6e9ff !important;
  color: #4361ee !important;
}

.fc-daygrid-day {
  transition: background 0.2s ease;
}

.fc-daygrid-day:hover {
  background: rgba(67, 97, 238, 0.04);
}

/* Events */
.fc-event {
  border-radius: 6px;
  padding: 2px 6px;
  font-weight: 500;
  cursor: pointer;
  border: none;
}

.fc-event-title {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Modal overlay */
.modal {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.55);
  backdrop-filter: blur(3px);
  justify-content: center;
  align-items: center;
  z-index: 999;
}

/* Modal content */
.modal-content {
  background: #ffffff;
  padding: 1.75rem;
  border-radius: 14px;
  width: 420px;
  box-shadow: 0 20px 40px rgba(0,0,0,0.2);
  animation: fadeIn 0.25s ease;
}

.modal-content h3 {
  font-size: 1.1rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
  color: #2b2d42;
}

.modal-content p {
  font-size: 0.85rem;
  color: #555;
  margin-bottom: 0.75rem;
  line-height: 1.5;
}

/* Close button */
.close-btn {
  float: right;
  font-size: 1.2rem;
  cursor: pointer;
  color: #888;
  transition: 0.2s ease;
}

.close-btn:hover {
  color: #f72585;
}

/* Animations */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(8px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Responsive */
@media (max-width: 768px) {
  .container {
    margin: 15px;
    padding: 18px;
  }

  .modal-content {
    width: 90%;
  }
}
</style>

  </style>
</head>
<body>
<div class="container">
  <h1>Task Calendar</h1>
  <form method="GET">
    <label>Filter by Status:</label>
    <select name="status" onchange="this.form.submit()">
      <option value="">All</option>
      <option value="pending">Pending</option>
      <option value="in progress">In Progress</option>
      <option value="completed">Completed</option>
    </select>
  </form>

  <div id="calendar"></div>
</div>

<div id="taskModal" class="modal">
  <div class="modal-content">
    <span class="close-btn" onclick="document.getElementById('taskModal').style.display='none'">&times;</span>
    <h3 id="modalTitle"></h3>
    <p id="modalDesc"></p>
    <p><strong>Status:</strong> <span id="modalStatus"></span></p>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script>
  const calendarEl = document.getElementById('calendar');
  const modal = document.getElementById('taskModal');

  const calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    editable: true,
    eventClick: function(info) {
      info.jsEvent.preventDefault();
      document.getElementById('modalTitle').textContent = info.event.title;
      document.getElementById('modalDesc').textContent = info.event.extendedProps.description;
      document.getElementById('modalStatus').textContent = info.event.extendedProps.status;
      modal.style.display = 'flex';
    },
    eventDrop: function(info) {
      // Optional: Send AJAX request to update deadline
      alert('New date: ' + info.event.startStr);
    },
    events: <?= json_encode($events) ?>
  });

  calendar.render();

  window.onclick = function(event) {
    if (event.target == modal) {
      modal.style.display = "none";
    }
  }
</script>
</body>
</html>