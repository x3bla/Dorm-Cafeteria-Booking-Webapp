<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Connect to the database
$conn = new mysqli('localhost', 'root', '', 'student_meals_db');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentNo = $_SESSION['student_no'] ?? null; // Get the logged-in student's number
    if (!$studentNo) {
        die('Error: Student not logged in.');
    }

    // Handle meal cancellations
if (isset($_POST['cancel']) && !empty($_POST['cancel'])) {
    foreach ($_POST['cancel'] as $date => $meals) {
        foreach ($meals as $meal) {
            $mealKey = strtolower($meal); // breakfast, lunch, or dinner
            // Update the canceled meal to be disabled (set 'disabled' column to 1)
            $stmtCancel = $conn->prepare("UPDATE students_has_meal_request SET $mealKey = 0, disabled = 1 WHERE student_no = ? AND meal_request_date = ?");
            $stmtCancel->bind_param("ss", $studentNo, $date);
            if (!$stmtCancel->execute()) {
                echo "Error canceling $meal for $date: " . $stmtCancel->error . "<br>";
            }
            $stmtCancel->close();
        }

        // After updating meals, check if the record has all meals disabled and update accordingly
        $checkEmptyStmt = $conn->prepare("SELECT breakfast, lunch, dinner FROM students_has_meal_request WHERE student_no = ? AND meal_request_date = ?");
        $checkEmptyStmt->bind_param("ss", $studentNo, $date);
        $checkEmptyStmt->execute();
        $result = $checkEmptyStmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if ($row['breakfast'] == 0 && $row['lunch'] == 0 && $row['dinner'] == 0) {
                // Set 'disabled' to 1 if all meals are canceled, but do not delete the record
                $disableStmt = $conn->prepare("UPDATE students_has_meal_request SET disabled = 1 WHERE student_no = ? AND meal_request_date = ?");
                $disableStmt->bind_param("ss", $studentNo, $date);
                if (!$disableStmt->execute()) {
                    echo "Error disabling record for $date: " . $disableStmt->error . "<br>";
                }
                $disableStmt->close();
            }
        }

        $checkEmptyStmt->close();
    }
}


    // Handle new meal selections
    if (isset($_POST['meals']) && !empty($_POST['meals'])) {
        $meals = $_POST['meals']; // Array containing selected meals by day

        // Prepare the SQL statement for inserting new records
        $stmtInsert = $conn->prepare("INSERT INTO students_has_meal_request (student_no, meal_request_date, breakfast, lunch, dinner) VALUES (?, ?, ?, ?, ?)");
        if (!$stmtInsert) {
            die('Error preparing statement: ' . $conn->error);
        }

        foreach ($meals as $date => $selectedMeals) {
            // Validate the date format
            if (!DateTime::createFromFormat('Y-m-d', $date)) {
                die('Error: Invalid date format.');
            }

            $breakfast = in_array('Breakfast', $selectedMeals) ? 1 : 0;
            $lunch = in_array('Lunch', $selectedMeals) ? 1 : 0;
            $dinner = in_array('Dinner', $selectedMeals) ? 1 : 0;

            // Check if a record already exists for the same student and date
            $checkStmt = $conn->prepare("SELECT * FROM students_has_meal_request WHERE student_no = ? AND meal_request_date = ?");
            $checkStmt->bind_param("ss", $studentNo, $date);
            $checkStmt->execute();
            $result = $checkStmt->get_result();

            if ($result->num_rows === 0) {
                // Insert the new record
                $stmtInsert->bind_param("ssiii", $studentNo, $date, $breakfast, $lunch, $dinner);
                if (!$stmtInsert->execute()) {
                    echo "Error inserting data for $date: " . $stmtInsert->error . "<br>";
                }
            } else {
                // Update the existing record
                $updateStmt = $conn->prepare("UPDATE students_has_meal_request SET breakfast = breakfast | ?, lunch = lunch | ?, dinner = dinner | ? WHERE student_no = ? AND meal_request_date = ?");
                $updateStmt->bind_param("iiiss", $breakfast, $lunch, $dinner, $studentNo, $date);
                if (!$updateStmt->execute()) {
                    echo "Error updating data for $date: " . $updateStmt->error . "<br>";
                }
                $updateStmt->close();
            }

            $checkStmt->close();
        }

        $stmtInsert->close();
    }

    // Set a success notification message
    $_SESSION['notification'] = "Meal selections successfully submitted!";
    
    // Redirect with a notification bar
    header("Location: dashboard.php");
    exit;
}

// Close the database connection
$conn->close();
?>
