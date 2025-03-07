<?php
// Get the file path from the query string
$filePath = isset($_GET['file']) ? $_GET['file'] : '';

// Ensure the file exists
if (file_exists($filePath)) {
    echo "<script>window.open('$filePath', '_blank');</script>";
} else {
    echo "Invoice not found.";
}
?>
