# 🗂️ Task Manager Web Application

## 📌 Overview

The **Task Manager** is a robust, full-stack web application built with **PHP**, **MySQL**, and a structured **MVC architecture**. It is designed to help small teams or organizations manage tasks efficiently. Admins can assign tasks to users, monitor task statuses, and view progress through a centralized dashboard. The system also supports real-time task updates, automatic deadline tracking, and email notifications.

---

## 🧰 Technologies Used

| Layer        | Technology               |
|--------------|---------------------------|
| Backend      | PHP 7.4+                  |
| Database     | MySQL / MariaDB           |
| Frontend     | HTML5, CSS3 (Flexbox/Grid), Vanilla JS |
| Design       | Google Fonts, Font Awesome |
| Mail Service | PHP `mail()` function     |
| Hosting      | Localhost (XAMPP/WAMP)    |

---

## ⚙️ Features

### 🔐 Authentication & Authorization
- Secure login and logout for users.
- Session-based access control.
- Two user roles: **admin** and **standard user**.

### 📋 Task Management
- Admins can:
  - Assign tasks to users.
  - Set deadlines and task descriptions.
  - View, edit, and delete tasks.
- Users can:
  - View their assigned tasks.
  - Mark tasks as completed (pending admin confirmation).
  - See task deadlines and status badges.

### 📬 Email Notifications
- Sends email to the assigned user when a new task is created.
- Customizable email body with task details.

### 📊 Dashboard Statistics
- View total tasks, pending tasks, tasks in progress, and completed tasks.
- Sort and filter tasks by status and deadline.

### 🧼 Input Security
- All inputs are sanitized using `real_escape_string` or parameterized queries.
- Prepared statements to avoid SQL injection.

---

## 🧱 Project Directory Structure

task-manager/
├── config/
│ └── db.php # Database configuration and connection
├── controllers/
│ ├── AuthController.php # Session & role management
│ ├── TaskController.php # Business logic for tasks
│ └── UserController.php # User logic and queries
├── models/
│ ├── Task.php # Task DB methods (CRUD)
│ └── User.php # User DB methods
├── public/
│ ├── login.php # Login form
│ ├── register.php # Admin & user registration
│ ├── dashboard.php # Standard user dashboard
│ ├── admin-dashboard.php # Admin overview of all tasks
│ ├── assign-task.php # Task assignment form (admin only)
│ ├── edit-task.php # Task editing page
│ ├── logout.php # Session destruction
│ └── deleteTaskController.php # Handle deletions
├── scripts/
│ └── send_mail.php # Utility to send email (uses mail())
├── assets/ # Optional folder for CSS/JS/images
└── README.md # Project documentation