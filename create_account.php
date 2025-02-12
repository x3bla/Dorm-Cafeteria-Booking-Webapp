<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Set the default language to English
$lang = isset($_GET['lang']) && $_GET['lang'] == 'ja' ? 'ja' : 'en';

// Language content arrays
$langContent = [
    'en' => [
        'title' => 'Create Account - Student Meal Request',
        'signup' => 'SIGN UP',
        'id_label' => 'ID Number *',
        'id_placeholder' => 'E.g. 00000001',
        'name_label' => 'Name *',
        'name_placeholder' => 'Your name here',
        'email_label' => 'Email *',
        'email_placeholder' => 'example@gmail.com',
        'password_label' => 'Password *',
        'password_placeholder' => '********',
        'retype_password_label' => 'Re-type Password *',
        'retype_password_placeholder' => '********',
        'allergies_label' => 'Allergies',
        'submit' => 'Sign Up',
        'already_account' => 'Already have an account?',
        'login_here' => 'Login here',
        'password_mismatch' => 'Passwords do not match. Please try again.',
        'student_exists' => 'This student number is already registered. Please try logging in.',
        'email_exists' => 'This email is already registered. Please try logging in.',
        'account_created' => 'Account created successfully! You can now <a href="login.php">login</a>.',
        'error_creating_account' => 'Error creating account: ',
        'fill_required_fields' => 'Please fill in all required fields.',
        'password_length_error' => 'Password must be at least 8 characters long.',
        'allergy_options' => [
            'Shrimp' => 'Shrimp',
            'Crab' => 'Crab',
            'Soba (Buckwheat)' => 'Soba (Buckwheat)',
            'Egg' => 'Egg',
            'Dairy' => 'Dairy',
            'Peanuts' => 'Peanuts',
        ]
    ],
    'ja' => [
        'title' => 'アカウント作成 - 学生食事リクエスト',
        'signup' => 'サインアップ',
        'id_label' => '入構許可番号 / ID番号 *',
        'id_placeholder' => '例: 00000001',
        'name_label' => '名前 *',
        'name_placeholder' => 'ここに名前を入力',
        'email_label' => 'メール *',
        'email_placeholder' => 'example@gmail.com',
        'password_label' => 'パスワード *',
        'password_placeholder' => '********',
        'retype_password_label' => 'パスワード再入力 *',
        'retype_password_placeholder' => '********',
        'allergies_label' => 'アレルギー',
        'submit' => 'サインアップ',
        'already_account' => 'すでにアカウントをお持ちですか?',
        'login_here' => 'こちらからログイン',
        'password_mismatch' => 'パスワードが一致しません。もう一度お試しください。',
        'student_exists' => 'この入構許可番号はすでに登録されています。ログインしてください。',
        'email_exists' => 'このメールアドレスはすでに登録されています。ログインしてください。',
        'account_created' => 'アカウントが作成されました！<a href="login.php">ログイン</a>できます。',
        'error_creating_account' => 'アカウント作成エラー: ',
        'fill_required_fields' => 'すべての必須フィールドを入力してください。',
        'password_length_error' => 'パスワードは8文字以上である必要があります。',
        'allergy_options' => [
            'Shrimp' => 'エビ',
            'Crab' => 'カニ',
            'Soba (Buckwheat)' => 'そば (そば粉)',
            'Egg' => '卵',
            'Dairy' => '乳製品',
            'Peanuts' => 'ピーナッツ',
        ]
    ]
];

// Get the selected language content
$currentLang = $langContent[$lang];

// Connect to the database
$conn = new mysqli('localhost', 'root', '', 'student_meals_db');

// Check for connection errors
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Process form submission for creating an account
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentName = $_POST['studentName'] ?? '';
    $studentNo = $_POST['studentNo'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $retypePassword = $_POST['retypePassword'] ?? '';
    $allergies = $_POST['allergies'] ?? [];

    // Validate required fields
    if (!empty($studentName) && !empty($studentNo) && !empty($email) && !empty($password) && !empty($retypePassword)) {
        if (strlen($password) < 8) {
            $notification = "<p style='color: red; text-align: center;'>{$currentLang['password_length_error']}</p>";
        } elseif ($password !== $retypePassword) {
            $notification = "<p style='color: red; text-align: center;'>{$currentLang['password_mismatch']}</p>";
        } else {
            // Check if the student number or email already exists
            $stmt = $conn->prepare("SELECT student_no, email FROM students WHERE student_no = ? OR email = ?");
            $stmt->bind_param("ss", $studentNo, $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $existingRecord = $result->fetch_assoc();
                if ($existingRecord['student_no'] === $studentNo) {
                    $notification = "<p style='color: red; text-align: center;'>{$currentLang['student_exists']}</p>";
                } elseif ($existingRecord['email'] === $email) {
                    $notification = "<p style='color: red; text-align: center;'>{$currentLang['email_exists']}</p>";
                }
            } else {
                // Hash the password for security
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

                // Insert the new student into the database
                $stmt = $conn->prepare("INSERT INTO students (name, student_no, email, password) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $studentName, $studentNo, $email, $hashedPassword);

                if ($stmt->execute()) {
                    $student_id = $stmt->insert_id;

                    // Insert allergies into the allergies table
                    if (!empty($allergies)) {
                        $stmt = $conn->prepare("INSERT INTO allergies (student_id, allergy) VALUES (?, ?)");
                        foreach ($allergies as $allergy) {
                            $stmt->bind_param("is", $student_id, $allergy);
                            $stmt->execute();
                        }
                    }

                    // Set session variables for student name and number
                    $_SESSION['name'] = $studentName;
                    $_SESSION['student_no'] = $studentNo;

                    $notification = "<p style='color: green; text-align: center;'>{$currentLang['account_created']}</p>";
                } else {
                    $notification = "<p style='color: red; text-align: center;'>{$currentLang['error_creating_account']} " . $stmt->error . "</p>";
                }
            }
            $stmt->close();
        }
    } else {
        $notification = "<p style='color: red; text-align: center;'>{$currentLang['fill_required_fields']}</p>";
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
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-image: url('/mnt/data/image.png');
            background-size: cover;
            background-position: center;
            font-family: Arial, sans-serif;
        }
        .container {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 600px;
            position: relative;
            text-align: left; /* Align all text and form content to the left */
        }
        h1 {
            font-size: 28px;
            margin-bottom: 20px;
            text-align: center; /* Keep the title centered */
        }
        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 14px;
        }
        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%;
            max-width: 700px; /* Ensure input fields don't stretch too wide */
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 14px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            max-width: 700px;
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
        .checkbox-list {
            list-style: none;
            padding: 0;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .checkbox-list li {
            margin: 8px 0;
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
        .container img {
            display: block;
            width: 400px;
            height: auto;
            margin: 0 auto 20px auto;
        }

    </style>
</head>
<body>
    <div class="container">
    <div class="language-toggle">
            <a href="?lang=en">English</a> | <a href="?lang=ja">日本語</a>
        </div>
        <img src="./6255500733277389823.png" alt="Gifu College Logo" style="max-width: 500%; height: auto;">

        <?php if (isset($notification)) { echo $notification; } ?>

        <h1><?php echo $currentLang['signup']; ?></h1>
        <form action="" method="POST">
            <label for="studentNo"><?php echo $currentLang['id_label']; ?></label>
            <input type="text" id="studentNo" name="studentNo" placeholder="<?php echo $currentLang['id_placeholder']; ?>" required>
            
            <label for="studentName"><?php echo $currentLang['name_label']; ?></label>
            <input type="text" id="studentName" name="studentName" placeholder="<?php echo $currentLang['name_placeholder']; ?>" required>

            <label for="email"><?php echo $currentLang['email_label']; ?></label>
            <input type="email" id="email" name="email" placeholder="<?php echo $currentLang['email_placeholder']; ?>" required>

            <label for="password"><?php echo $currentLang['password_label']; ?></label>
            <input type="password" id="password" name="password" placeholder="<?php echo $currentLang['password_placeholder']; ?>" required>

            <label for="retypePassword"><?php echo $currentLang['retype_password_label']; ?></label>
            <input type="password" id="retypePassword" name="retypePassword" placeholder="<?php echo $currentLang['retype_password_placeholder']; ?>" required>

            <label><?php echo $currentLang['allergies_label']; ?></label>
<ul class="checkbox-list">
    <?php foreach ($currentLang['allergy_options'] as $value => $label): ?>
        <li>
            <input type="checkbox" name="allergies[]" value="<?php echo htmlspecialchars($value); ?>">
            <?php echo htmlspecialchars($label); ?>
        </li>
    <?php endforeach; ?>
</ul>

            <button type="submit"><?php echo $currentLang['submit']; ?></button>
        </form>

        <p><?php echo $currentLang['already_account']; ?> <a href="login.php"><?php echo $currentLang['login_here']; ?></a></p>

        
    </div>
</body>
</html>
