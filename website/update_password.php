<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session
session_start();

// Check if language has been changed
if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

// Set the language, default is English
$lang = $_SESSION['lang'] ?? 'en';

// Language strings
$translations = [
    'en' => [
        'title' => 'Update Password',
        'student_id' => 'Student ID',
        'new_password' => 'New Password',
        'confirm_password' => 'Confirm New Password',
        'update_button' => 'Update Password',
        'notification_success' => 'Password updated successfully!',
        'notification_error' => 'Error updating password: ',
        'password_short' => 'Password must be at least 8 characters long.',
        'password_mismatch' => 'Passwords do not match. Please try again.',
        'fill_fields' => 'Please fill in all required fields.',
        'back_to_login' => 'Back to Login'
    ],
    'ja' => [
        'title' => 'パスワードを更新する',
        'student_id' => '学生ID',
        'new_password' => '新しいパスワード',
        'confirm_password' => '新しいパスワードを確認する',
        'update_button' => 'パスワードを更新する',
        'notification_success' => 'パスワードが正常に更新されました。',
        'notification_error' => 'パスワードの更新中にエラーが発生しました：',
        'password_short' => 'パスワードは8文字以上である必要があります。',
        'password_mismatch' => 'パスワードが一致しません。再度お試しください。',
        'fill_fields' => 'すべての必須項目を入力してください。',
        'back_to_login' => 'ログイン画面に戻る'
    ]
];

include 'db.php';

// Initialize notification message
$notification = "";

// Process the password update form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_no = $_POST['student_no'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validate required fields
    if (!empty($student_no) && !empty($new_password) && !empty($confirm_password)) {
        if (strlen($new_password) < 8) {
            $notification = "<p style='color: red; text-align: center;'>{$translations[$lang]['password_short']}</p>";
        } elseif ($new_password !== $confirm_password) {
            $notification = "<p style='color: red; text-align: center;'>{$translations[$lang]['password_mismatch']}</p>";
        } else {
            // Hash the new password using PHP's password_hash function
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // Prepare the SQL query to update the password
            $updateStmt = $conn->prepare("UPDATE students SET password = ? WHERE student_no = ?");
            $updateStmt->bind_param("ss", $hashed_password, $student_no);

            // Execute the query
            if ($updateStmt->execute()) {
                $notification = "<p style='color: green; text-align: center;'>{$translations[$lang]['notification_success']}</p>";
            } else {
                $notification = "<p style='color: red; text-align: center;'>{$translations[$lang]['notification_error']} " . $updateStmt->error . "</p>";
            }

            // Close the statement
            $updateStmt->close();
        }
    } else {
        $notification = "<p style='color: red; text-align: center;'>{$translations[$lang]['fill_fields']}</p>";
    }
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $translations[$lang]['title']; ?></title>
    <style>
        body {
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f5f5f5;
            font-family: Arial, sans-serif;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 600px;
            text-align: left;
            
        }
        img {
            width: 400px;
            height: auto;
            margin-bottom: 20px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        h1 {
            font-size: 28px;
            margin-bottom: 20px;
            text-align: center;
        }
        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 14px;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 14px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #8b5a2b;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background-color: #5a3820;
        }
        p {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }
        p a {
            color: #8b5a2b;
            text-decoration: none;
        }
        p a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">
<img src="./6255500733277389823.png" alt="Gifu College Logo">
    <h1><?php echo $translations[$lang]['title']; ?></h1>

    <?php if (!empty($notification)) { echo $notification; } ?>

    <form action="" method="POST">
        <label for="student_no"><?php echo $translations[$lang]['student_id']; ?></label>
        <input type="text" id="student_no" name="student_no" required>

        <label for="new_password"><?php echo $translations[$lang]['new_password']; ?></label>
        <input type="password" id="new_password" name="new_password" required>

        <label for="confirm_password"><?php echo $translations[$lang]['confirm_password']; ?></label>
        <input type="password" id="confirm_password" name="confirm_password" required>

        <button type="submit"><?php echo $translations[$lang]['update_button']; ?></button>
    </form>

    <p><a href="login.php"><?php echo $translations[$lang]['back_to_login']; ?></a></p>
</div>

</body>
</html>
