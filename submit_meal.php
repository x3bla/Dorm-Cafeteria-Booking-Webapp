<?php
session_start();

include("db.php");


// Handle form submission and store student name in session
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['studentName'])) {
    $_SESSION['student_name'] = $_POST['studentName'];
    $studentName = $_SESSION['student_name'];
    $mealOptions = isset($_POST['mealOption']) ? (array) $_POST['mealOption'] : [];
    $date = $_POST['date'];

    // Validate input
    if (empty($studentName) || empty($date) || empty($mealOptions)) {
        die("Error: All fields are required.");
    }

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
            // If no duplicate found, insert the new meal request
            $stmt->bind_param("sss", $studentName, $date, $mealOption);
            if (!$stmt->execute()) {
                echo "Error: " . $stmt->error;
            }
        } else {
            echo "<p style='color: red;'>A request for $mealOption on $date already exists.</p>";
        }
        $checkStmt->close();
    }

    // Close the statement
    $stmt->close();
}

// Close the connection
$conn->close();

// Redirect back to index.php (or display a message)
header("Location: index.php");
exit();
?>
