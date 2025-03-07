<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'student_meals_db');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_no = $_POST['student_no'] ?? '';
    $phone_number = $_POST['phone_number'] ?? '';

    if (!empty($student_no) && !empty($phone_number)) {
        // Generate a random 6-digit OTP
        $otp = rand(100000, 999999);
        $_SESSION['otp'] = $otp;
        $_SESSION['student_no'] = $student_no;

        // Store OTP in database (optional, for added security)
        $stmt = $conn->prepare("UPDATE students SET otp = ? WHERE student_no = ? AND phone_number = ?");
        $stmt->bind_param("sss", $otp, $student_no, $phone_number);
        
        if ($stmt->execute()) {
            echo "OTP sent successfully! (For testing: $otp)";
        } else {
            echo "Error sending OTP!";
        }

        $stmt->close();
    } else {
        echo "Please enter your Student ID and phone number!";
    }
}

$conn->close();
?>
