<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include("db.php");

// Process login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentName = $_POST['studentName'] ?? '';
    $studentNo = $_POST['studentNo'] ?? '';

    
    if (!empty($studentName) && !empty($studentNo)) {
        // Check if the student number exists in the database
        $stmt = $conn->prepare("SELECT * FROM student_meals WHERE student_name = ? AND student_no = ?");
        $stmt->bind_param("ss", $studentName, $studentNo);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Store student name and number in session variables
            $_SESSION['student_name'] = $studentName;
            $_SESSION['student_no'] = $studentNo;

            // Redirect to the meal request page
            header('Location: index.php');
            exit;
        } else {
            echo "<p style='color: red;'>Student not found. Please check your details or <a href='create_account.php'>create an account</a>.</p>";
        }

        $stmt->close();
    } else {
        echo "<p style='color: red;'>Please enter both student name and student number.</p>";
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Student Meal Request</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
    </style>
</head>
<body>
    <h1>Login</h1>
    <form action="login.php" method="POST">
        <label for="studentName">Student Name:</label>
        <input type="text" id="studentName" name="studentName" required><br><br>

        <label for="studentNo">Student Number:</label>
        <input type="text" id="studentNo" name="studentNo" required><br><br>

        <button type="submit">Login</button>
    </form>

    <p>Don't have an account? <a href="create_account.php">Create one here</a>.</p>
</body>
</html>
