<?php
session_start();
include 'db.php';

$notification = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered_otp = $_POST['otp'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $student_no = $_SESSION['student_no'] ?? '';

    if (!empty($entered_otp) && !empty($new_password) && !empty($confirm_password)) {
        if ($new_password !== $confirm_password) {
            $notification = "<p style='color: red;'>Passwords do not match!</p>";
        } elseif ($entered_otp != $_SESSION['otp']) {
            $notification = "<p style='color: red;'>Invalid OTP!</p>";
        } else {
            // Hash the new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // Update the password in the database
            $stmt = $conn->prepare("UPDATE students SET password = ?, otp = NULL WHERE student_no = ?");
            $stmt->bind_param("ss", $hashed_password, $student_no);

            if ($stmt->execute()) {
                $notification = "<p style='color: green;'>Password updated successfully!</p>";
                unset($_SESSION['otp'], $_SESSION['student_no']);
            } else {
                $notification = "<p style='color: red;'>Error updating password!</p>";
            }

            $stmt->close();
        }
    } else {
        $notification = "<p style='color: red;'>Please fill in all fields!</p>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
</head>
<body>
    <h1>Reset Password</h1>
    <?php echo $notification; ?>
    <form action="" method="POST">
        <label>Enter OTP:</label>
        <input type="text" name="otp" required><br>
        <label>New Password:</label>
        <input type="password" name="new_password" required><br>
        <label>Confirm Password:</label>
        <input type="password" name="confirm_password" required><br>
        <button type="submit">Update Password</button>
    </form>
</body>
</html>
