<?php
session_start();
// Get the student's name and number from session
$studentName = isset($_SESSION['name']) ? $_SESSION['name'] : '';
$studentNo = isset($_SESSION['student_no']) ? $_SESSION['student_no'] : '';

if (!$studentNo) {
    header('Location: login.php'); // Redirect to login page if not logged in
    exit();
}

// Handle language selection
$lang = isset($_GET['lang']) ? $_GET['lang'] : (isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en');
$_SESSION['lang'] = $lang;

// Translation strings
$translations = [
    'en' => [
        'title' => 'Student Dashboard',
        'welcome' => 'Welcome,',
        'meal_selection' => 'Meal Selection',
        'view_meals' => 'View Meals Selected',
        'view_menu' => 'View Menu',
        'notification' => 'meal successfully reserved.',
        'log_out' => 'Log Out'
    ],
    'ja' => [
        'title' => '学生ダッシュボード',
        'welcome' => 'ようこそ、',
        'meal_selection' => '食事選択',
        'view_meals' => '選択した食事を見る',
        'view_menu' => 'メニューを見る',
        'notification' => '食事は正常に予約されました',
        'log_out' => 'ログアウト'
    ],
];

$currentLang = $translations[$lang];
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $currentLang['title']; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Open+Sans:wght@300;400&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color:rgb(255, 255, 255); /* White background */
            margin: 0;
            padding: 0;
            text-align: center;
            color: #333;
        }
        h1 {
            margin-bottom: 20px;
            font-family: 'Open Sans', sans-serif;
            font-weight: 700;
        }
        .button {
            display: inline-block;
            margin: 15px;
            padding: 15px 30px;
            font-size: 30px;
            color: white;
            background-color: #8b5a2b;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            text-decoration: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }
        /* Specific larger buttons */
        .button.large {
            padding: 20px 40px;
            font-size: 30px;
        }
        .button:hover {
            background-color: #5a3820;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }
        .button:active {
            transform: translateY(0);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        .welcome-message {
            margin-bottom: 30px;
            font-size: 20px;
            font-weight: bold;
            color: #333;
        }
        .language-toggle {
            text-align: right;
            margin-bottom: 25px;
            margin-right: 50px;
        }
        .language-toggle a {
            text-decoration: none;
            margin-left: 5px;
            color: #0066cc;
            font-weight: bold;
            font-size: 20px;
        }
        .notification {
            background-color: #5a3820;
            color: white;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-size: 16px;
            display: inline-block;
            width: 80%;
            margin: 0 auto;
        }
        .header {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
            margin-bottom: 40px;
            flex-wrap: wrap;
        }
        .buttons-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
        }
        .student-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .logout-btn {
            margin-left: 10px;
            background-color: #f44336;
        }
        .card {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 40px 20px;
            border-radius: 10px;
            /* box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2); */
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="language-toggle">
        <a href="?lang=en">English  |</a>
        <a href="?lang=ja">日本語</a>
    </div>

    <div>
        <img src="./6255500733277389823.png" alt="Gifu College Logo" style="max-width: 30%; height: auto; margin-top: 30px;">
    </div>

    <?php
        if (isset($_SESSION['notification'])) {
            echo "<div class='notification'>{$currentLang['notification']}</div>";
            unset($_SESSION['notification']);
        }
    ?>

    <div class="card">
        <h1><?php echo $currentLang['title']; ?></h1>

        <!-- Student Info and Log Out Button -->
        <div class="header">
            <div class="student-info">
                <p class="welcome-message">
                    <?php echo $currentLang['welcome']; ?> <?php echo htmlspecialchars($studentName); ?> (<?php echo htmlspecialchars($studentNo); ?>)
                </p>
                <a href="login.php?lang=<?php echo $lang; ?>" class="button logout-btn"><?php echo $currentLang['log_out']; ?></a>
            </div>
        </div>

        <!-- Meal Selection Buttons in Vertical Layout -->
        <div class="buttons-container">
            <a href="index.php?lang=<?php echo $lang; ?>" class="button large"><?php echo $currentLang['meal_selection']; ?></a>
            <a href="view_meals.php?lang=<?php echo $lang; ?>" class="button large"><?php echo $currentLang['view_meals']; ?></a>
            <a href="menu.php?lang=<?php echo $lang; ?>" class="button large"><?php echo $currentLang['view_menu']; ?></a>
        </div>
    </div>
</body>
</html>
