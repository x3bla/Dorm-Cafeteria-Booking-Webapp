<?php
session_start();

// Connect to the database
$conn = new mysqli('localhost', 'root', '', 'student_meals_db');

// Check for connection errors
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

$studentName = isset($_SESSION['name']) ? $_SESSION['name'] : '';

// Handle language selection
$lang = isset($_GET['lang']) ? $_GET['lang'] : (isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en');
$_SESSION['lang'] = $lang; // Store language preference in session

// Translation strings (same as before)
$translations = [
    'en' => [
        'title' => 'Login - Student Meal Request',
        'login' => 'Login',
        'id_label' => 'ID Number *',
        'id_placeholder' => 'E.g. 00000001',
        'password_label' => 'Password *',
        'password_placeholder' => '********',
        'submit' => 'Login',
        'create_account' => 'Don\'t have an account? Create one here.',
        'Sign_up' => 'Sign up',
        'error' => 'Please enter both student password and student number.',
        'notification' => 'Invalid student number or password.',
        'forgot_password' => 'Forgot Password? Click here to reset.'
    ],
    'ja' => [
        'title' => 'ログイン - 学生食事リクエスト',
        'login' => 'ログイン',
        'id_label' => '入構許可番号 *',
        'id_placeholder' => '例: 00000001',
        'password_label' => 'パスワード *',
        'password_placeholder' => '********',
        'submit' => 'ログイン',
        'create_account' => 'アカウントがありませんか？ここで作成してください。',
        'Sign_up' => 'サインアップ',
        'error' => '学生のパスワードと番号の両方を入力してください。',
        'notification' => '学生番号またはパスワードが無効です。',
        'forgot_password' => 'パスワードを忘れましたか？ ここをクリックしてリセットしてください。'
    ],
];

$currentLang = $translations[$lang];

// Process login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $studentNo = $_POST['studentNo'] ?? '';

    if (!empty($password) && !empty($studentNo)) {
        // Prepare the SQL query to retrieve the hashed password from the database
        $stmt = $conn->prepare("SELECT name, password FROM students WHERE student_no = ?");
        $stmt->bind_param("s", $studentNo);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $hashedPassword = $row['password']; // Hashed password from database

            // Verify the password
            if (password_verify($password, $hashedPassword)) {
                // Password matches, set session variables
                $_SESSION['student_no'] = $studentNo;
                $_SESSION['name'] = $row['name'];
                header('Location: dashboard.php');
                exit();
            } else {
                echo "<p style='color: red; text-align: center;'>{$currentLang['notification']}</p>";
            }
        } else {
            echo "<p style='color: red; text-align: center;'>{$currentLang['notification']}</p>";
        }

        $stmt->close();
    } else {
        echo "<p style='color: red; text-align: center;'>{$currentLang['error']}</p>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $currentLang['title']; ?></title>
    <style>
        body {
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background-color: white;
            font-family: 'Arial', sans-serif;
        }

        .wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
        }

        .container {
            text-align: center;
            background-color: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
        }

        .language-toggle {
            text-align: right;
            margin-bottom: 25px;
        }

        .language-toggle a {
            text-decoration: none;
            margin-left: 12px;
            color: #0066cc;
            font-weight: bold;
            font-size: 14px;
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
            margin-bottom: 30px;
            font-size: 28px;
            color: #333;
        }

        label {
            display: block;
            text-align: left;
            margin-bottom: 8px;
            font-weight: bold;
            font-size: 14px;
            color: #555;
        }

        input {
            width: 100%;
            padding: 12px;
            margin-bottom: 18px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
            background-color: #f9f9f9;
            box-sizing: border-box;
        }

        input:focus {
            border-color: #0066cc;
            outline: none;
            background-color: #fff;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #8b5a2b;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            cursor: pointer;
            box-sizing: border-box;
        }

        button:hover {
            background-color: #6e3c1f;
        }

        p {
            margin-top: 15px;
            font-size: 14px;
            color: #666;
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
<div class="wrapper">
    <div class="container">
        <div class="language-toggle">
            <a href="?lang=en">English |</a>
            <a href="?lang=ja">日本語</a>
        </div>

        <img src="./6255500733277389823.png" alt="Gifu College Logo">

        <h1><?php echo $currentLang['login']; ?></h1>
        <form action="login.php?lang=<?php echo $lang; ?>" method="POST">
            <label for="studentNo"><?php echo $currentLang['id_label']; ?></label>
            <input type="text" id="studentNo" name="studentNo" placeholder="<?php echo $currentLang['id_placeholder']; ?>" required>

            <label for="password"><?php echo $currentLang['password_label']; ?></label>
            <input type="password" id="password" name="password" placeholder="<?php echo $currentLang['password_placeholder']; ?>" required>

            <button type="submit"><?php echo $currentLang['submit']; ?></button>
        </form>

        <p><a href="update_password.php"><?php echo $currentLang['forgot_password']; ?></a></p>
        <p><?php echo $currentLang['create_account']; ?> <a href="create_account.php"><?php echo $currentLang['Sign_up']; ?></a>.</p>
    </div>
</div>
</body>
</html>
