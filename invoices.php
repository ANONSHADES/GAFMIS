<?php
// Include database connection and necessary functions
include 'connect.php';
include 'functions.php';

// Check if sales ID is provided in the URL
if (isset($_GET['sales_id'])) {
    $salesId = $_GET['sales_id'];

    // Get sales details based on $salesId
    $salesDetails = getSalesDetails($salesId); // Implement this function in your connect.php or functions.php file

    if ($salesDetails) {
        // Extract relevant details from $salesDetails
        $customerId = $salesDetails['customer_id'] ?? ''; // Assuming your sales table has 'customer_id' column
        $dueDate = date('Y-m-d'); // Example due date, you can set it based on your requirements
        $subtotal = $salesDetails['total_amount'] ?? 0; // Example, adjust as per your sales data
        $discountAmount = $salesDetails['discount_amount'] ?? 0; // Example discount amount
        $paymentStatus = 'unpaid'; // Default payment status
        $paymentMethod = ''; // Default payment method, update as needed
        $notes = ''; // Default notes, update as needed
        $currency = 'USD'; // Default currency, update as needed

        // Insert invoice details into the database
        $result = insertInvoiceDetails($customerId, $dueDate, $subtotal, $discountAmount, $paymentStatus, $paymentMethod, $notes, $currency);

        if ($result) {
            echo "Invoice details inserted successfully!";
        } else {
            echo "Error inserting invoice details.";
        }
    } else {
        echo "Sales details not found.";
    }
} else {
    echo "Sales ID not provided.";
}
?>
