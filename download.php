<?php
// Include database connection
include 'connect.php';

// Function to get file path based on invoice ID
function getFilePath($invoiceId)
{
    global $con; // Assuming $con is your database connection object

    $sql = "SELECT file_path FROM invoices WHERE invoice_id = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "i", $invoiceId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    return $row['file_path'];
}

// Check if invoice ID is provided in the URL
if (isset($_GET['invoice_id'])) {
    $invoiceId = $_GET['invoice_id'];
    $filePath = getFilePath($invoiceId);

    if (!$filePath) {
        echo "Invoice not found or file path not available.";
        exit;
    }

    // Download file
    if (file_exists($filePath)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . basename($filePath));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    } else {
        echo "File not found.";
        exit;
    }
} else {
    echo "Invalid request.";
    exit;
}
?>
