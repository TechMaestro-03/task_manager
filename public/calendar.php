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
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">

  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: #f8f9fa;
      margin: 0;
    }
    .container {
      max-width: 1000px;
      margin: 30px auto;
      padding: 20px;
      background: white;
      border-radius: 8px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .fc-event {
      cursor: pointer;
    }
    .modal {
      display: none;
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0,0,0,0.5);
      justify-content: center;
      align-items: center;
    }
    .modal-content {
      background: white;
      padding: 20px;
      border-radius: 8px;
      width: 400px;
    }
    .close-btn {
      float: right;
      cursor: pointer;
      font-weight: bold;
    }
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
