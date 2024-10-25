<?php
session_start(); // Start the session

// Connect to the database
$conn = new mysqli('localhost', 'root', '', 'student_meals_db');

// Check for connection errors
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $studentName = $_POST['studentName'] ?? '';
    $mealOptions = isset($_POST['mealOption']) ? (array) $_POST['mealOption'] : []; // Handle multiple meal options
    $date = $_POST['date'] ?? ''; // Use null coalescing operator to prevent undefined index warning

    // Validate input
    if (empty($studentName) || empty($date) || empty($mealOptions)) {
        die("Error: All fields are required.");
    }

    // Store the student name in the session
    $_SESSION['student_name'] = $studentName;

    // Remove duplicates from the meal options
    $mealOptions = array_unique($mealOptions);

    // Prepare the statement for inserting meal options
    $stmt = $conn->prepare("INSERT INTO student_meals (student_name, date, meal_option) VALUES (?, ?, ?)");

    // Loop through each selected meal option
    foreach ($mealOptions as $mealOption) {
        // Check for duplicate entry
        $checkStmt = $conn->prepare("SELECT * FROM student_meals WHERE student_name = ? AND date = ? AND meal_option = ?");
        $checkStmt->bind_param("sss", $studentName, $date, $mealOption);
        $checkStmt->execute();
        $result = $checkStmt->get_result();

        if ($result->num_rows === 0) {
            // Bind parameters and execute for each meal option
            $stmt->bind_param("sss", $studentName, $date, $mealOption);
            if ($stmt->execute()) {
                echo "Meal selection for $mealOption saved successfully!<br>";
            } else {
                echo "Error: " . $stmt->error . "<br>";
            }
        } else {
            echo "<p style='color: red;'>A request for $mealOption on $date already exists.</p>";
        }
        $checkStmt->close();
    }

    // Close the statement
    $stmt->close();
}

// Get the student's name from the session
$studentName = isset($_SESSION['student_name']) ? $_SESSION['student_name'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
    </style>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Meal Selection</title>
</head>
<body>
    <h1>Student Meal Request</h1>
    <form action="index.php" method="POST">
        <label for="studentName">Student Name:</label>
        <input type="text" id="studentName" name="studentName" required><br><br>

        <label for="mealOption">Meal Options:</label><br>
        <input type="checkbox" name="mealOption[]" value="breakfast"> Breakfast<br>
        <input type="checkbox" name="mealOption[]" value="lunch"> Lunch<br>
        <input type="checkbox" name="mealOption[]" value="dinner"> Dinner<br><br>

        <label for="date">Select Date:</label>
        <input type="date" id="date" name="date" min="2024-10-01" max="2025-02-29" required><br><br>

        <button type="submit">Submit</button>
    </form>

    <h2>Your Meal Requests</h2>
    <table border="1">
        <tr>
            <th>Student Name</th>
            <th>Date</th>
            <th>Meal Options</th>
        </tr>
    <?php
        // Fetch and group data from the database
        if (!empty($studentName)) {
            $stmt = $conn->prepare("SELECT date, GROUP_CONCAT(DISTINCT meal_option ORDER BY meal_option SEPARATOR ' || ') AS meal_options 
                                    FROM student_meals 
                                    WHERE student_name = ? 
                                    GROUP BY date");
            $stmt->bind_param("s", $studentName);
            $stmt->execute();
            $result = $stmt->get_result();

            // Display data in the table
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                    <td>" . htmlspecialchars($studentName) . "</td>
                    <td>" . htmlspecialchars($row['date']) . "</td>
                    <td>" . htmlspecialchars($row['meal_options']) . "</td>
                </tr>";
            }

            // Close the statement
            $stmt->close();
        }
        // Close the connection
        $conn->close();
    ?>
    </table>
</body>
</html>
