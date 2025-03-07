<?php
// Start the session
session_start();

// Include the Composer autoloader to load PHPMailer
require 'vendor/autoload.php';

// Database connection
$conn = new mysqli('localhost', 'root', '', 'student_meals_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get email from the form
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);  // Sanitize email input

    // Validate email format
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Check if the email exists in the database
        $stmt = $conn->prepare("SELECT student_no, name FROM students WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($student_id, $student_name);
            $stmt->fetch();

            // Generate a unique reset token
            $reset_token = bin2hex(random_bytes(16)); // Generates a 32-character token
            $expiration_time = date("Y-m-d H:i:s", strtotime("+1 hour")); // Token expires in 1 hour

            // Insert the token into the password reset requests table
            $insertStmt = $conn->prepare("INSERT INTO password_reset_requests (reset_token, student_no, token_expiry) VALUES (?, ?, ?)");
            $insertStmt->bind_param("sss", $reset_token, $student_id, $expiration_time);
            if ($insertStmt->execute()) {

                // Send the reset email using PHPMailer
                $reset_link = "http://yourdomain.com/reset_password.php?token=" . $reset_token;

                $mail = new PHPMailer\PHPMailer\PHPMailer();
                $mail->isSMTP();
                $mail->Host = 'smtp.office365.com';  // Outlook SMTP Server
                $mail->SMTPAuth = true;
                $mail->Username = 'your-email@outlook.com';  // Replace with your Outlook email
                $mail->Password = 'your-app-password';   // Use an Outlook App Password (not your real password)
                $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Enable debugging (for testing)
                $mail->SMTPDebug = 2; // Set to 0 for production
                $mail->Debugoutput = 'html'; // Print debug output in HTML format

                // Set the sender and recipient
                $mail->setFrom('your-email@outlook.com', 'Your Name');
                $mail->addAddress($email);

                // Set the email subject and body
                $mail->Subject = 'Password Reset Request';
                $mail->Body    = "Hi " . $student_name . ",\n\n" . 
                                 "You requested a password reset. Click the following link to reset your password:\n" . 
                                 $reset_link . "\n\n" . 
                                 "If you didn't request this, you can ignore this email.";

                // Send the email and check if it was successful
                if ($mail->send()) {
                    echo "A password reset link has been sent to your email address.";
                } else {
                    echo "There was an error sending the reset email: " . $mail->ErrorInfo;
                }

            } else {
                echo "Error while saving the reset token.";
            }
            $insertStmt->close();
        } else {
            echo "No account found with that email address.";
        }
        $stmt->close();
    } else {
        echo "Please enter a valid email address.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
</head>
<body>
    <h1>Request Password Reset</h1>
    <form method="POST" action="">
        <label for="email">Enter your email:</label>
        <input type="email" name="email" required>
        <button type="submit">Submit</button>
    </form>

    <!-- Button to go back to the login page -->
    <br><br>
    <form action="login.php" method="get">
        <button type="submit">Go Back to Login</button>
    </form>
</body>
</html>
