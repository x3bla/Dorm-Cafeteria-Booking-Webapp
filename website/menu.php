<?php
// Start the session to store the selected language
session_start();

// Set the default language to English
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'en';
}

// Toggle language based on user selection
if (isset($_GET['lang']) && ($_GET['lang'] == 'en' || $_GET['lang'] == 'ja')) {
    $_SESSION['lang'] = $_GET['lang'];
}

// Handle allergy filters
$filters = isset($_GET['filters']) ? $_GET['filters'] : [];

include 'db.php';

// Build SQL query with allergy filters
$allergyColumns = [
    'allergen_shrimp', 'allergen_crab','allergen_buckwheat', 'allergen_soba', 
    'allergen_egg', 'allergen_dairy', 'allergen_peanuts'
];

$whereClause = [];
foreach ($filters as $filter) {
    if (in_array($filter, $allergyColumns)) {
        $whereClause[] = "$filter = 0";
    }
}
$whereSQL = '';

$sql = "SELECT 
            date, 
            day_of_week, 
            meal_time, 
            dish, 
            is_available,
            allergen_shrimp, 
            allergen_crab, 
            allergen_buckwheat, 
            allergen_soba,
            allergen_egg, 
            allergen_dairy, 
            allergen_peanuts, 
            calories, 
            meal_type
        FROM weekly_menu 
        $whereSQL
        ORDER BY FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), meal_time";

$result = $conn->query($sql);

$menu = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $menu[$row['day_of_week']][$row['meal_time']][] = $row;
    }
} else {
    $menu = [];
}

$conn->close();

// Language phrases
$lang = $_SESSION['lang'] == 'ja' ? [
    'title' => '週刊メニュー',
    'date' => '日付',
    'day' => '曜日',
    'meal_time' => '食事時間',
    'dish' => '料理',
    'shrimp' => 'エビ',
    'crab' => 'カニ',
    'soba' => 'そば',
    'buckwheat' => 'そば粉',
    'egg' => '卵',
    'dairy' => '乳製品',
    'peanuts' => 'ピーナッツ',
    'calories' => 'カロリー',
    'mealtype' => 'ノート',
    'availability' => '利用可能',
    'back_button' => 'ダッシュボードに戻る',
    'no_data' => 'メニューデータがありません！',
    'filter' => 'アレルギーフィルター',
    'apply_filter' => 'フィルターを適用'
] : [
    'title' => 'Weekly Menu',
    'date' => 'Date',
    'day' => 'Day',
    'meal_time' => 'Meal Time',
    'dish' => 'Dish',
    'shrimp' => 'Shrimp',
    'crab' => 'Crab',
    'soba' => 'Soba',
    'buckwheat' => 'Buckwheat',
    'egg' => 'Egg',
    'dairy' => 'Dairy',
    'peanuts' => 'Peanuts',
    'calories' => 'Calories',
    'mealtype' => 'Meal Type',
    'notes' => 'Notes',
    'availability' => 'Availability',
    'back_button' => 'Back to Dashboard',
    'no_data' => 'No menu data available!',
    'filter' => 'Allergy Filter',
    'apply_filter' => 'Apply Filter'
];
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $lang['title']; ?></title>
    <style>
        table {
            width: 60%;
            border-collapse: collapse;
            margin: 20px auto;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 5px;
            text-align: center;
            font-size: 14px;
        }
        th {
            background-color: #f2f2f2;
        }
        .back-button {
            display: inline-block;
            margin: 20px auto;
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .back-button:hover {
            background-color: #45a049;
        }
        .language-toggle {
            margin: 10px auto;
            text-align: center;
        }
        form {
            text-align: center;
            margin: 20px auto;
        }
        fieldset {
            display: inline-block;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            margin-top: 10px;
            padding: 5px 10px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 3px;
        }
        button:hover {
            background-color: #0056b3;
        }
        .tooltip {
    position: relative;
    display: inline-block;
    cursor: pointer;
}
.tooltip .tooltiptext {
    visibility: hidden;
    width: 120px;
    background-color: black;
    color: #fff;
    text-align: center;
    border-radius: 5px;
    padding: 5px;
    position: absolute;
    z-index: 1;
    bottom: 100%;
    left: 50%;
    margin-left: -60px;
    opacity: 0;
    transition: opacity 0.3s;
}
.tooltip:hover .tooltiptext {
    visibility: visible;
    opacity: 1;
}

    </style>
</head>
<body>
    <div class="language-toggle">
        <a href="?lang=en">English</a> | <a href="?lang=ja">日本語</a>
    </div>
    <h1 style="text-align: center;"><?php echo $lang['title']; ?></h1>

    <!-- Allergy Filter Form -->
    <form method="GET" action="">
        <fieldset>
            <legend><?php echo $lang['filter']; ?></legend>
            <label><input type="checkbox" name="filters[]" value="allergen_shrimp" <?php echo in_array('allergen_shrimp', $filters) ? 'checked' : ''; ?>> <?php echo $lang['shrimp']; ?></label>
            <label><input type="checkbox" name="filters[]" value="allergen_crab" <?php echo in_array('allergen_crab', $filters) ? 'checked' : ''; ?>> <?php echo $lang['crab']; ?></label>
            <label><input type="checkbox" name="filters[]" value="allergen_soba" <?php echo in_array('allergen_soba', $filters) ? 'checked' : ''; ?>> <?php echo $lang['soba']; ?></label>
            <label><input type="checkbox" name="filters[]" value="allergen_buckwheat" <?php echo in_array('allergen_buckwheat', $filters) ? 'checked' : ''; ?>> <?php echo $lang['buckwheat']; ?></label>
            <label><input type="checkbox" name="filters[]" value="allergen_egg" <?php echo in_array('allergen_egg', $filters) ? 'checked' : ''; ?>> <?php echo $lang['egg']; ?></label>
            <label><input type="checkbox" name="filters[]" value="allergen_dairy" <?php echo in_array('allergen_dairy', $filters) ? 'checked' : ''; ?>> <?php echo $lang['dairy']; ?></label>
            <label><input type="checkbox" name="filters[]" value="allergen_peanuts" <?php echo in_array('allergen_peanuts', $filters) ? 'checked' : ''; ?>> <?php echo $lang['peanuts']; ?></label>
            <button type="submit"><?php echo $lang['apply_filter']; ?></button>
        </fieldset>
    </form>

    <!-- Menu Table -->
    <table>
    <thead>
        <tr>
            <th><?php echo $lang['date']; ?></th>
            <th><?php echo $lang['day']; ?></th>
            <th><?php echo $lang['meal_time']; ?></th>
            <th><?php echo $lang['dish']; ?></th>
            <th><?php echo $lang['availability']; ?></th>
            <th>
                <div class="tooltip">🦐
                    <span class="tooltiptext"><?php echo $lang['shrimp']; ?></span>
                </div>
            </th>
            <th>
                <div class="tooltip">🦀
                    <span class="tooltiptext"><?php echo $lang['crab']; ?></span>
                </div>
            </th>
            <th>
                <div class="tooltip">🌿
                    <span class="tooltiptext"><?php echo $lang['buckwheat']; ?></span>
                </div>
            </th>
            <th>
                <div class="tooltip">🌾
                    <span class="tooltiptext"><?php echo $lang['soba']; ?></span>
                </div>
            </th>
            <th>
                <div class="tooltip">🥚
                    <span class="tooltiptext"><?php echo $lang['egg']; ?></span>
                </div>
            </th>
            <th>
                <div class="tooltip">🧀
                    <span class="tooltiptext"><?php echo $lang['dairy']; ?></span>
                </div>
            </th>
            <th>
                <div class="tooltip">🥜
                    <span class="tooltiptext"><?php echo $lang['peanuts']; ?></span>
                </div>
            </th>
            <th><?php echo $lang['calories']; ?></th>
            <th><?php echo $lang['mealtype']; ?></th>
        </tr>
    </thead>
    <tbody>
    <?php if (!empty($menu)): ?>
        <?php foreach ($menu as $day => $meals): ?>
            <?php foreach ($meals as $mealTime => $dishes): ?>
                <?php
                // Check if all dishes pass or fail the filters
                $allPassFilters = !empty($filters); // Assume true if filters are applied
                $allFailFilters = !empty($filters); // Same assumption for failure

                foreach ($dishes as $dish) {
                    foreach ($filters as $filter) {
                        if (empty($dish[$filter])) {
                            $allFailFilters = false; // Found a dish that passes at least one filter
                        } else {
                            $allPassFilters = false; // Found a dish that fails at least one filter
                        }
                    }
                }

                // Determine the color for Date, Day, and Meal Time
                $headerColor = '';
                if ($allPassFilters) {
                    $headerColor = '#c3e6cb'; // Green
                } elseif ($allFailFilters) {
                    $headerColor = '#f5c6cb'; // Red
                }
                ?>
                <?php foreach ($dishes as $index => $dish): ?>
    <?php
    // Determine row color for individual dishes
    $rowColor = '';
    if (!empty($filters)) {
        $meetsFilters = true;
        foreach ($filters as $filter) {
            if (!empty($dish[$filter])) {
                $meetsFilters = false; // Dish fails the filter
                break;
            }
        }
        $rowColor = $meetsFilters ? '#c3e6cb' : '#f5c6cb'; // Green for success, red for failure
    }
    ?>
    <tr style="<?= $rowColor ? "background-color: $rowColor;" : ''; ?>">
    <?php if ($index === 0): ?>
        <td rowspan="<?= count($dishes); ?>" style="background-color: white;"><?= htmlspecialchars($dish['date']); ?></td>
        <td rowspan="<?= count($dishes); ?>" style="background-color: white;"><?= htmlspecialchars($day); ?></td>
        <td rowspan="<?= count($dishes); ?>" style="background-color: white;"><?= ucfirst(htmlspecialchars($mealTime)); ?></td>
    <?php endif; ?>

    <td><?= htmlspecialchars($dish['dish']); ?></td>
    <td><?= $dish['is_available'] ? '✔' : '✖'; ?></td> <!-- Availability (✔ for available, ✖ for unavailable) -->
    <td><?= $dish['allergen_shrimp'] ? 'O' : ''; ?></td>
    <td><?= $dish['allergen_crab'] ? 'O' : ''; ?></td>
    <td><?= $dish['allergen_buckwheat'] ? 'O' : ''; ?></td>
    <td><?= $dish['allergen_soba'] ? 'O' : ''; ?></td>
    <td><?= $dish['allergen_egg'] ? 'O' : ''; ?></td>
    <td><?= $dish['allergen_dairy'] ? 'O' : ''; ?></td>
    <td><?= $dish['allergen_peanuts'] ? 'O' : ''; ?></td>
    <td><?= htmlspecialchars($dish['calories'] ?? 'N/A'); ?></td> <!-- Calories Fix -->
    <td><?= htmlspecialchars($dish['meal_type'] ?? 'N/A'); ?></td> <!-- Meal Type Fix -->
</tr>

<?php endforeach; ?>


                <?php if ($mealTime === 'dinner'): ?>
                    <!-- Separator row after Dinner -->
                    <tr>
                        <td colspan="12" style="background-color: #f0f0f0; height: 10px;"></td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="12"><?php echo $lang['no_data']; ?></td>
        </tr>
    <?php endif; ?>
    </tbody>
</table>
    <a href="dashboard.php" class="back-button"><?php echo $lang['back_button']; ?></a>
</body>
</html>
