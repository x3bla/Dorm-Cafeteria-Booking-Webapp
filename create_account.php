<?php
session_start();
include("db.php");


// Process form submission for creating an account
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentName = $_POST['studentName'] ?? '';
    $studentNo = $_POST['studentNo'] ?? '';

    if (!empty($studentName) && !empty($studentNo)) {
        // Check if the student number already exists
        $stmt = $conn->prepare("SELECT * FROM student_meals WHERE student_no = ?");
        $stmt->bind_param("s", $studentNo);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<p style='color: red;'>This student number is already registered. Please try logging in.</p>";
        } else {
            // Insert the new student into the database
            $stmt = $conn->prepare("INSERT INTO student_meals (student_name, student_no) VALUES (?, ?)");
            $stmt->bind_param("ss", $studentName, $studentNo);
            if ($stmt->execute()) {
                echo "<p style='color: green;'>Account created successfully! You can now <a href='login.php'>login</a>.</p>";
            } else {
                echo "<p style='color: red;'>Error creating account: " . $stmt->error . "</p>";
            }
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
    <title>Create Account - Student Meal Request</title>
</head>
<body>
    <h1>Create Account</h1>
    <form action="create_account.php" method="POST">
        <label for="studentName">Student Name:</label>
        <input type="text" id="studentName" name="studentName" required><br><br>

        <label for="studentNo">Student Number:</label>
        <input type="text" id="studentNo" name="studentNo" required><br><br>

        <button type="submit">Create Account</button>
    </form>
    <p>Already have an account? <a href="login.php">Login here</a>.</p>
</body>
</html>
