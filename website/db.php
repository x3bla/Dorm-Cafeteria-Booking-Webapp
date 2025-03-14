<?php
// Connect to the database
$conn = new mysqli('localhost', 'root', 'toor', 'student_meals_db');

// Check for connection errors
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}
?>