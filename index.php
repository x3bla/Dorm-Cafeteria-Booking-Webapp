<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Handle language selection
$lang = isset($_GET['lang']) ? $_GET['lang'] : (isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en');
$_SESSION['lang'] = $lang;

// Connect to the database
$conn = new mysqli('localhost', 'root', '', 'student_meals_db');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Get the student's name and number from the session
$studentName = isset($_SESSION['student_name']) ? $_SESSION['student_name'] : '';
$studentNo = isset($_SESSION['student_no']) ? $_SESSION['student_no'] : '';

if (!$studentNo) {
    die('Error: Student not logged in.');
}

// Get the current date and calculate the cutoff date (2 days ahead)
$currentDate = date('Y-m-d');
$cutoffDate = date('Y-m-d', strtotime('+2 days'));

// Define holidays: a range and specific dates
$holidayStart = '2024-12-24';
$holidayEnd = '2025-01-05';
$specificHolidays = ['2024-12-10', '2025-01-20'];

// Handle navigation between months
$currentMonth = date('n'); // Current month (1 to 12)
$currentYear = date('Y');  // Current year
$month = isset($_GET['month']) ? (int)$_GET['month'] : $currentMonth;
$year = isset($_GET['year']) ? (int)$_GET['year'] : $currentYear;

// Ensure the month/year stays within the range of November 2024 to February 2025
if ($year < 2024 || ($year === 2024 && $month < 11)) {
    $month = 11;
    $year = 2024;
} elseif ($year > 2025 || ($year === 2025 && $month > 2)) {
    $month = 2;
    $year = 2025;
}

// Get the first day of the selected month and the number of days in the month
$firstDayOfMonth = strtotime("$year-$month-01");
$startDay = date('w', $firstDayOfMonth); // Day of the week (0 = Sunday, 6 = Saturday)
$totalDays = date('t', $firstDayOfMonth); // Number of days in the month

// Fetch already submitted meal requests for this student
$submittedMeals = [];
$result = $conn->query("SELECT * FROM students_has_meal_request WHERE student_no = '$studentNo'");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $submittedMeals[$row['meal_request_date']] = [
            'breakfast' => (bool)$row['breakfast'],
            'lunch' => (bool)$row['lunch'],
            'dinner' => (bool)$row['dinner'],
        ];
    }
}

// Generate links for navigation
$prevMonth = $month - 1;
$prevYear = $year;
if ($prevMonth < 1) {
    $prevMonth = 12;
    $prevYear--;
}

$nextMonth = $month + 1;
$nextYear = $year;
if ($nextMonth > 12) {
    $nextMonth = 1;
    $nextYear++;
}
// Translation strings
$translations = [
    'en' => [
        'meal_selection' => 'Meal Selection',
        'unavailable' => 'Unavailable',
        'welcome' => 'Welcome',
        'previous_month' => 'Previous Month',
        'next_month' => 'Next Month',
        'cancel' => 'Cancel',
        'submit' => 'Submit',
        'back_to_dashboard' => 'Back to Dashboard',
        'Breakfast' => 'Breakfast',
        'Lunch' => 'Lunch',
        'Dinner' => 'Dinner',
        'sunday' => 'SUNDAY',
        'monday' => 'MONDAY',
        'tuesday' => 'TUESDAY',
        'wednesday' => 'WEDNESDAY',
        'thursday' => 'THURSDAY',
        'friday' => 'FRIDAY',
        'saturday' => 'SATURDAY',
        'holiday' => 'Holiday',
        'Select All Meals' => 'Select All Meals',
        'Cancel All Meals' => 'Cancel All Meals',
        'Uncheck All' => 'Uncheck All',
        'Select All' => 'Select All',
    ],
    'ja' => [
        'meal_selection' => '食事選択',
        'unavailable' => '利用不可',
        'welcome' => 'ようこそ',
        'previous_month' => '前の月',
        'next_month' => '次の月',
        'cancel' => 'キャンセル',
        'submit' => '送信',
        'back_to_dashboard' => 'ダッシュボードに戻る',
        'Breakfast' => '朝食',
        'Lunch' => '昼食',
        'Dinner' => '夕食',
        'sunday' => '日曜日',
        'monday' => '月曜日',
        'tuesday' => '火曜日',
        'wednesday' => '水曜日',
        'thursday' => '木曜日',
        'friday' => '金曜日',
        'saturday' => '土曜日',
        'holiday' => '休日',
        'Select All Meals' => 'すべての食事を選択',
        'Cancel All Meals' => 'すべての食事をキャンセル',
        'Uncheck All' => 'すべてのチェックを外す',
        'Select All' => 'すべてを選択',
    ],
];

$currentLang = $translations[$lang];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meal Selection</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
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
        td {
            vertical-align: top;
            text-align: left; /* Align the contents to the left */
            padding-left: 15px; /* Optional: add some left padding to keep it neat */
        }

        input[type="checkbox"] {
            margin-right: 5px; /* Add some space between the checkbox and the label */
        }
        .date-label {
            font-weight: bold;
        }
        .disabled {
            color: #ccc;
        }
        .navigation {
            margin-bottom: 20px;
        }
        .navigation a {
            text-decoration: none;
            color: #8b5a2b;
            margin: 0 10px;
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
        .submit-button, .back-button {
            padding: 12px 24px;
            border-radius: 8px;
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s ease, transform 0.3s ease;
            background-color: #8b5a2b; /* Same color as the Submit button */
            color: white;
            border: none;
        }

        .submit-button:hover, .back-button:hover {
            background-color: #704c29; /* Darker shade for hover */
            transform: scale(1.05);
        }

        .submit-button:active, .back-button:active {
            background-color: #5b3c1a; /* Even darker shade for active */
        }


    </style>
   <script>
    // Initialize state variables for toggle functionality
    let mealsSelected = false;
    let cancellationsSelected = false;

    // Function to toggle meal selection
    function selectAllMealsForMonth() {
        const checkboxes = document.querySelectorAll('input[type="checkbox"][name^="meals"]');
        mealsSelected = !mealsSelected; // Toggle state
        checkboxes.forEach(checkbox => (checkbox.checked = mealsSelected));

        // Update button text dynamically
        const button = document.querySelector('button[onclick="selectAllMealsForMonth()"]');
        button.textContent = mealsSelected ? 'Unselect All Meals' : 'Select All Meals';
    }

    // Function to toggle cancellation selection
    function cancelAllMealsForMonth() {
        const cancelCheckboxes = document.querySelectorAll('input[type="checkbox"][name^="cancel"]');
        cancellationsSelected = !cancellationsSelected; // Toggle state
        cancelCheckboxes.forEach(checkbox => (checkbox.checked = cancellationsSelected));

        // Update button text dynamically
        const button = document.querySelector('button[onclick="cancelAllMealsForMonth()"]');
        button.textContent = cancellationsSelected ? 'Unselect All Cancellations' : 'Cancel All Meals';
    }
    // Function to uncheck all selected checkboxes
    function uncheckAllBoxes() {
        const checkboxes = document.querySelectorAll('input[type="checkbox"]'); // Select all checkboxes
        checkboxes.forEach(checkbox => checkbox.checked = false); // Uncheck each checkbox
    }
</script>


</head>
<body>
<div class="language-toggle">
    <a href="?lang=en&month=<?php echo $month; ?>&year=<?php echo $year; ?>">English |</a>
    <a href="?lang=ja&month=<?php echo $month; ?>&year=<?php echo $year; ?>">日本語</a>
</div>

<div style="text-align: center; margin-bottom: 20px;">
    <img src="./6255500733277389823.png" alt="Gifu College Logo" style="max-width: 30%; height: auto;">
</div>

<h1><?php echo $currentLang['meal_selection']; ?></h1>

<div class="navigation">
    <a href="?month=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?>&lang=<?php echo $lang; ?>">
        <?php echo $currentLang['previous_month']; ?>
    </a>
    <span>
        <?php echo $lang === 'ja' ? date('Y年 n月', $firstDayOfMonth) : date('F Y', $firstDayOfMonth); ?>
    </span>
    <a href="?month=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?>&lang=<?php echo $lang; ?>">
        <?php echo $currentLang['next_month']; ?>
    </a>
</div>

<!-- Button to select/unselect all meals -->
<div style="text-align: right; margin-bottom: 15px;">
    <button type="button" class="submit-button" onclick="selectAllMealsForMonth()"><?php echo $currentLang['Select All Meals']; ?></button>
    <button type="button" class="submit-button" style="background-color: #dc3545;" onclick="cancelAllMealsForMonth()"><?php echo $currentLang['Cancel All Meals']; ?></button>
    <button type="button" class="submit-button" style="background-color: #6c757d;" onclick="uncheckAllBoxes()"><?php echo $currentLang['Uncheck All']; ?></button>
</div>
<script>

function selectAllMeals(day) {
    const mealOptions = document.querySelectorAll(`.meal-option-${day}`);
    mealOptions.forEach(option => option.checked = true);
}

</script>
<form action="submit_meals.php" method="POST">
    <table>
        <thead>
        <tr>
            <th><?php echo $currentLang['sunday']; ?></th>
            <th><?php echo $currentLang['monday']; ?></th>
            <th><?php echo $currentLang['tuesday']; ?></th>
            <th><?php echo $currentLang['wednesday']; ?></th>
            <th><?php echo $currentLang['thursday']; ?></th>
            <th><?php echo $currentLang['friday']; ?></th>
            <th><?php echo $currentLang['saturday']; ?></th>
        </tr>
        </thead>
        <tbody>
            <?php
            $currentDay = 1;

            // Generate calendar rows
            for ($row = 0; $row < 6; $row++) {
                echo '<tr>';
                for ($col = 0; $col < 7; $col++) {
                    if (($row === 0 && $col < $startDay) || $currentDay > $totalDays) {
                        // Empty cell
                        echo '<td></td>';
                    } else {
                        $date = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-" . str_pad($currentDay, 2, '0', STR_PAD_LEFT);
                        $isHoliday = (strtotime($date) >= strtotime($holidayStart) && strtotime($date) <= strtotime($holidayEnd)) || in_array($date, $specificHolidays);
                        $isSelectableDate = !$isHoliday && (strtotime($date) >= strtotime($cutoffDate));
                        $selectedMeals = $submittedMeals[$date] ?? ['breakfast' => false, 'lunch' => false, 'dinner' => false];
                        echo '<td>';
                        echo '<span class="date-label">' . $currentDay . '</span><br>';
                        if ($isHoliday) {
                            echo '<span class="disabled">' . $currentLang['holiday'] . '</span><br>';
                        } elseif ($isSelectableDate) {
                            foreach (['Breakfast', 'Lunch', 'Dinner'] as $meal) {
                                $mealKey = strtolower($meal);
                                if ($selectedMeals[$mealKey]) {
                                    // Show cancellation option for reserved meals
                                    echo '<input type="checkbox" class="meal-option-' . $currentDay . '" name="cancel[' . $date . '][]" value="' . ucfirst($meal) . '"> ' . $currentLang['cancel'] . ' ' . $currentLang[$meal] . '<br>';
                                } else {
                                    // Allow selection for non-reserved meals
                                    echo '<input type="checkbox" class="meal-option-' . $currentDay . '" name="meals[' . $date . '][]" value="' . ucfirst($meal) . '"> ' . $currentLang[$meal] . '<br>';
                                }
                            }
                            // Include "Select All" functionality
                            echo '<button type="button" class="submit-button" style="background-color:rgba(189, 92, 163, 0.73);"onclick="selectAllMeals(' . $currentDay . ')">' . $currentLang["Select All"] . '</button>';

                        } else {
                            echo '<span class="disabled">' . $currentLang['unavailable'] . '</span><br>';
                        }
                        echo '</td>';
                        $currentDay++;
                    }
                }
                echo '</tr>';
            }
            ?>
        </tbody>
    </table>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px;">
        <!-- Back to Dashboard Button -->
        <a href="dashboard.php" class="back-button"><?php echo $currentLang['back_to_dashboard']; ?></a>

        <!-- Submit Button -->
        <button type="submit" class="submit-button"><?php echo $currentLang['submit']; ?></button>

    </div>
</form>

<script>
    function selectAllMealsForMonth() {
        const checkboxes = document.querySelectorAll('input[type="checkbox"][name^="meals"]');
        checkboxes.forEach(checkbox => checkbox.checked = true);
    }

    function cancelAllMealsForMonth() {
        const cancelCheckboxes = document.querySelectorAll('input[type="checkbox"][name^="cancel"]');
        cancelCheckboxes.forEach(checkbox => checkbox.checked = true);
    }
</script>
</body>

</html>
