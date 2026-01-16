<?php
$host = 'localhost';
$dbname = 'task_manager';
$username = 'root';  // change to your DB username
$password = '';      // change to your DB password

$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
