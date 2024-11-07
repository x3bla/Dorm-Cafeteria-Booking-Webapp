<?php
$conn = new mysqli('localhost', 'webapp_user', 'password123', 'student_meals_db');

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}
