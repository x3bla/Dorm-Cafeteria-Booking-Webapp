<?php
session_start();
ob_start(); // Start output buffering

// Get the student's name and number from session
$studentName = isset($_SESSION['name']) ? $_SESSION['name'] : '';
$studentNo = isset($_SESSION['student_no']) ? $_SESSION['student_no'] : '';

// Include the Composer autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Connect to the database
$conn = new mysqli('localhost', 'root', '', 'student_meals_db');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Fetch student name
$queryStudentInfo = "SELECT name FROM students WHERE student_no = '$studentNo'";
$resultStudentInfo = $conn->query($queryStudentInfo);

if ($resultStudentInfo && $resultStudentInfo->num_rows > 0) {
    $studentInfo = $resultStudentInfo->fetch_assoc();
    $studentName = $studentInfo['name'];
}

// Fetch meal request details for the student grouped by month
$query = "SELECT meal_request_date, breakfast, lunch, dinner 
          FROM students_has_meal_request 
          WHERE student_no = '$studentNo'
          ORDER BY meal_request_date ASC";
$result = $conn->query($query);

// Meal prices
$mealPrices = [
    'breakfast' => 272,
    'lunch' => 529,
    'dinner' => 569
];

// Initialize TCPDF
$pdf = new TCPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Gifu College');
$pdf->SetTitle('Meal Invoice');
$pdf->SetMargins(5, 10, 5);

// Group data by month
$mealsByMonth = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $mealMonth = date('Y-m', strtotime($row['meal_request_date']));
        $mealDay = (int)date('d', strtotime($row['meal_request_date']));
        
        if (!isset($mealsByMonth[$mealMonth])) {
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, (int)date('m', strtotime($mealMonth)), (int)date('Y', strtotime($mealMonth)));
            $mealsByMonth[$mealMonth] = [
                'days_in_month' => $daysInMonth,
                'days' => array_fill(1, $daysInMonth, ['breakfast' => false, 'lunch' => false, 'dinner' => false]),
                'totals' => ['breakfast' => 0, 'lunch' => 0, 'dinner' => 0, 'amount_due' => 0]
            ];
        }
        
        // Track meal reservations
        $mealsByMonth[$mealMonth]['days'][$mealDay] = [
            'breakfast' => $row['breakfast'],
            'lunch' => $row['lunch'],
            'dinner' => $row['dinner']
        ];

        // Calculate totals
        if ($row['breakfast']) {
            $mealsByMonth[$mealMonth]['totals']['breakfast']++;
            $mealsByMonth[$mealMonth]['totals']['amount_due'] += $mealPrices['breakfast'];
        }
        if ($row['lunch']) {
            $mealsByMonth[$mealMonth]['totals']['lunch']++;
            $mealsByMonth[$mealMonth]['totals']['amount_due'] += $mealPrices['lunch'];
        }
        if ($row['dinner']) {
            $mealsByMonth[$mealMonth]['totals']['dinner']++;
            $mealsByMonth[$mealMonth]['totals']['amount_due'] += $mealPrices['dinner'];
        }
    }
}

// Generate PDF for each month
foreach ($mealsByMonth as $month => $data) {
    // Add new page for each month
    $pdf->AddPage('L'); // Landscape orientation for more space
    
    // Header
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, "Meal Invoice for $month - Gifu College", 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 8, "Student Number: $studentNo", 0, 1);
    $pdf->Cell(0, 8, "Student Name: $studentName", 0, 1);
    $pdf->Ln(4);

   // Table Header
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(20, 10, 'Dates', 1, 0, 'C');
for ($i = 1; $i <= $data['days_in_month']; $i++) {
    $currentDate = "$month-" . str_pad($i, 2, '0', STR_PAD_LEFT); // Format the date
    $dayOfWeek = date('D', strtotime($currentDate)); // Get day of the week (e.g., Mon, Tue)
    
    // Set color for Saturdays and Sundays
    if ($dayOfWeek === 'Sat') {
        $pdf->SetFillColor(173, 216, 230); // Light blue for Saturday
    } elseif ($dayOfWeek === 'Sun') {
        $pdf->SetFillColor(255, 182, 193); // Light red for Sunday
    } else {
        $pdf->SetFillColor(255, 255, 255); // Default white for other days
    }
    $pdf->Cell(8, 10, $i, 1, 0, 'C', true); // Fill the cell
}
$pdf->Ln();

// Add a row for days of the week
$pdf->SetFont('helvetica', '', 8);
$pdf->Cell(20, 8, 'Days', 1, 0, 'C');
for ($i = 1; $i <= $data['days_in_month']; $i++) {
    $currentDate = "$month-" . str_pad($i, 2, '0', STR_PAD_LEFT); // Format the date
    $dayOfWeek = date('D', strtotime($currentDate)); // Get day of the week (e.g., Mon, Tue)
    
    // Set color for Saturdays and Sundays
    if ($dayOfWeek === 'Sat') {
        $pdf->SetFillColor(173, 216, 230); // Light blue for Saturday
    } elseif ($dayOfWeek === 'Sun') {
        $pdf->SetFillColor(255, 182, 193); // Light red for Sunday
    } else {
        $pdf->SetFillColor(255, 255, 255); // Default white for other days
    }
    $pdf->Cell(8, 8, $dayOfWeek, 1, 0, 'C', true); // Fill the cell
}
$pdf->Ln();

// Table Rows: Breakfast, Lunch, Dinner
$mealTypes = ['breakfast', 'lunch', 'dinner'];
foreach ($mealTypes as $meal) {
    $pdf->Cell(20, 10, ucfirst($meal), 1, 0, 'C');
    for ($i = 1; $i <= $data['days_in_month']; $i++) {
        $currentDate = "$month-" . str_pad($i, 2, '0', STR_PAD_LEFT); // Format the date
        $dayOfWeek = date('D', strtotime($currentDate)); // Get day of the week (e.g., Mon, Tue)
        
        // Set color for Saturdays and Sundays
        if ($dayOfWeek === 'Sat') {
            $pdf->SetFillColor(173, 216, 230); // Light blue for Saturday
        } elseif ($dayOfWeek === 'Sun') {
            $pdf->SetFillColor(255, 182, 193); // Light red for Sunday
        } else {
            $pdf->SetFillColor(255, 255, 255); // Default white for other days
        }
        
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->Cell(8, 10, '', 1, 0, 'C', true); // Fill the cell with the appropriate color
        
        // Draw circle if meal is reserved
        if ($data['days'][$i][$meal]) {
            $pdf->Circle($x + 4, $y + 5, 2); // Center of the circle inside the cell
        }
    }
    $pdf->Ln();
}


    // Summary Row
    $pdf->Ln(5);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(60, 10, "Total Breakfasts Reserved:", 0, 0, 'L');
    $pdf->Cell(30, 10, $data['totals']['breakfast'], 0, 1, 'L');

    $pdf->Cell(60, 10, "Total Lunches Reserved:", 0, 0, 'L');
    $pdf->Cell(30, 10, $data['totals']['lunch'], 0, 1, 'L');

    $pdf->Cell(60, 10, "Total Dinners Reserved:", 0, 0, 'L');
    $pdf->Cell(30, 10, $data['totals']['dinner'], 0, 1, 'L');

    $pdf->Cell(60, 10, "Total Amount Due:", 0, 0, 'L');
    $pdf->Cell(30, 10, number_format($data['totals']['amount_due']) . ' yen', 0, 1, 'L');
}

// End output buffering and clean
ob_end_clean();

// Close and output PDF
$pdf->Output('meal_invoice.pdf', 'I'); // Force download
?>
