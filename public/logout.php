<?php
session_start();
session_unset();
session_destroy();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Logged Out</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <meta http-equiv="refresh" content="2;url=login.php">
  <style>
    * {
      font-family: 'Poppins', sans-serif;
      box-sizing: border-box;
    }
    body {
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
      background-color: #f0f4f8;
    }
    .message {
      background: white;
      padding: 30px 50px;
      border-radius: 10px;
      box-shadow: 0 10px 25px rgba(0, 123, 255, 0.1);
      text-align: center;
    }
    .message h2 {
      color: #007BFF;
      margin-bottom: 10px;
    }
    .message p {
      font-size: 14px;
      color: #555;
    }
  </style>
</head>
<body>
  <div class="message">
    <h2>Logged Out</h2>
    <p>You have been successfully logged out.<br>Redirecting to login...</p>
  </div>
</body>
</html>

