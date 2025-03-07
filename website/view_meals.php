<?php
session_start();


// Handle language selection
$lang = isset($_GET['lang']) ? $_GET['lang'] : (isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en');
$_SESSION['lang'] = $lang;

// Check if the student is logged in
$studentNo = isset($_SESSION['student_no']) ? $_SESSION['student_no'] : '';
if (!$studentNo) {
    header('Location: login.php'); // Redirect to login page if not logged in
    exit();
}

// Connect to the database
$conn = new mysqli('localhost', 'root', '', 'student_meals_db');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Meal prices
$mealPrices = [
    'breakfast' => 272, // Breakfast cost in yen
    'lunch' => 529,     // Lunch cost in yen
    'dinner' => 569     // Dinner cost in yen
];

// Fetch meals selected by the student, ordered by date
$query = "SELECT meal_request_date, breakfast, lunch, dinner 
          FROM students_has_meal_request 
          WHERE student_no = '$studentNo' 
          ORDER BY meal_request_date ASC";
$result = $conn->query($query);

// Initialize an array to store total costs per month
$monthlyTotals = [];
$totalAmountDue = 0;

if ($result && $result->num_rows > 0) {
    // Set locale for months
    if ($lang === 'ja') {
        setlocale(LC_TIME, 'ja_JP.UTF-8'); // Japanese locale
    } else {
        setlocale(LC_TIME, 'en_US.UTF-8'); // English locale
    }

    while ($row = $result->fetch_assoc()) {
        // Get the month and year from the meal request date
        $mealDate = strtotime($row['meal_request_date']);
        $monthYear = $lang === 'ja' 
            ? date('Y年 n月', $mealDate) // Format: 2024年 11月
            : strftime('%B %Y', $mealDate); // Format: November 2024
        // Calculate the total for this day's meals
        $mealTotal = 0;
        if ($row['breakfast']) {
            $mealTotal += $mealPrices['breakfast'];
        }
        if ($row['lunch']) {
            $mealTotal += $mealPrices['lunch'];
        }
        if ($row['dinner']) {
            $mealTotal += $mealPrices['dinner'];
        }

        // Add to the respective month's total
        if (!isset($monthlyTotals[$monthYear])) {
            $monthlyTotals[$monthYear] = 0;
        }
        $monthlyTotals[$monthYear] += $mealTotal;
        $totalAmountDue += $mealTotal; // Add the daily total to the overall total
    }
}

// Translation strings
$translations = [
    'en' => [
        'title' => 'View Meals Selected',
        'meal_details' => 'Meal Details',
        'monthly_total' => 'Monthly Total Amount Due',
        'total_due' => 'Total Amount Due',
        'meal_selection' => 'Meal Selection',
        'view_meals' => 'View Meals Selected',
        'back_to_dashboard' => 'Back to Dashboard',
        'date' => 'Date',
        'breakfast' => 'Breakfast',
        'lunch' => 'Lunch',
        'dinner' => 'Dinner',
        'total_cost' => 'Total Cost',
        'yes' => 'Yes',
        'no' => 'No',
        'yen' => 'yen',
        'generate_invoice' => 'Generate Invoice PDF'
    ],
    'ja' => [
        'title' => '選択した食事の確認',
        'meal_details' => '食事詳細',
        'monthly_total' => '月別合計金額',
        'total_due' => '合計支払金額',
        'meal_selection' => '食事選択',
        'view_meals' => '選択した食事を見る',
        'back_to_dashboard' => 'ダッシュボードに戻る',
        'date' => '日付',
        'breakfast' => '朝食',
        'lunch' => '昼食',
        'dinner' => '夕食',
        'total_cost' => '合計金額',
        'yes' => 'はい',
        'no' => 'いいえ',
        'yen' => '円',
        'generate_invoice' => '請求書を生成するPDF'
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
    <style>
body {
    font-family: Arial, sans-serif;
    padding: 20px;
    min-height: 100vh; /* Ensure the body takes full viewport height */
    display: flex;
    flex-direction: column;
}

img {
    display: block;
    margin: 0 auto;
    max-width: 30% !important;  /* Adjust this value to control the image size */
    width: 100%; /* Ensures responsiveness */
    height: auto;
}

h1 {
    text-align: center;
    margin-bottom: 20px;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

th, td {
    border: 1px solid #ccc;
    padding: 10px;
    text-align: center;
}

th {
    background-color: #8b5a2b;
    color: white;
}

.total-amount {
    margin-top: 20px;
    font-size: 18px;
    font-weight: bold;
}

.navigation {
    margin-bottom: 20px;
    text-align: center;
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
.back-button {
    padding: 12px 24px;
    border-radius: 8px;
    text-align: center;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    text-decoration: none;
    background-color: #8b5a2b; /* Same color as the Submit button */
    color: white;
    border: none;
    transition: background-color 0.3s ease, transform 0.3s ease;
    margin-top: auto; /* Push it to the bottom */
    align-self: center; /* Center the button horizontally */
}

.back-button:hover {
    background-color: #704c29; /* Darker shade for hover */
    transform: scale(1.05);
}

.back-button:active {
    background-color: #5b3c1a; /* Even darker shade for active */
}


    </style>
</head>
<body>

    <!-- Language Toggle -->
    <div class="language-toggle">
        <a href="?lang=en">English |</a>
        <a href="?lang=ja">日本語</a>
    </div>

    <img src="./6255500733277389823.png" alt="Gifu College Logo">
    <h1><?php echo $currentLang['title']; ?></h1>

    <!-- Table to Show Meal Details -->
    <?php if ($result && $result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th><?php echo $currentLang['date']; ?></th>
                    <th><?php echo $currentLang['breakfast']; ?></th>
                    <th><?php echo $currentLang['lunch']; ?></th>
                    <th><?php echo $currentLang['dinner']; ?></th>
                    <th><?php echo $currentLang['total_cost']; ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Reset the result pointer and iterate through meals again to show details
                $result->data_seek(0);
                while ($row = $result->fetch_assoc()): 
                    $mealTotal = 0;
                    if ($row['breakfast']) {
                        $mealTotal += $mealPrices['breakfast'];
                    }
                    if ($row['lunch']) {
                        $mealTotal += $mealPrices['lunch'];
                    }
                    if ($row['dinner']) {
                        $mealTotal += $mealPrices['dinner'];
                    }
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['meal_request_date']); ?></td>
                        <td><?php echo $row['breakfast'] ? $currentLang['yes'] : $currentLang['no']; ?></td>
                        <td><?php echo $row['lunch'] ? $currentLang['yes'] : $currentLang['no']; ?></td>
                        <td><?php echo $row['dinner'] ? $currentLang['yes'] : $currentLang['no']; ?></td>
                        <td><?php echo number_format($mealTotal, 0) . ' ' . $currentLang['yen']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- Monthly Totals -->
    <div class="monthly-totals">
        <h3><?php echo $currentLang['monthly_total']; ?></h3>
        <?php if (!empty($monthlyTotals)): ?>
            <ul>
                <?php foreach ($monthlyTotals as $monthYear => $total): ?>
                    <li><strong><?php echo $monthYear; ?>:</strong> <?php echo number_format($total, 0); ?> <?php echo $currentLang['yen']; ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No meals have been selected yet.</p>
        <?php endif; ?>
    </div>

    <!-- Total Amount Due (For all selected meals) -->
    <div class="total-amount">
        <h3><?php echo $currentLang['total_due']; ?>: <?php echo number_format($totalAmountDue, 0); ?> <?php echo $currentLang['yen']; ?></h3>
    </div>
    <form action="generate_invoice.php" method="post" style="margin-top: 20px;">
    <div style="text-align: left; width: 100%;">
        <input type="submit" value="<?php echo $currentLang['generate_invoice']; ?>" class="back-button">
    </div>
</form>




    <!-- Back Button to Dashboard -->
    <a href="dashboard.php" class="back-button"><?php echo $currentLang['back_to_dashboard']; ?></a>

</body>
</html>
