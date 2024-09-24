<?php
// Include necessary files
include 'connect.php';
include 'sales_functions.php';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $customerId = $_POST['customer_id']; // Assuming you get customer_id from somewhere
    $productName = $_POST['product_name']; // Assuming you get product_name from somewhere
    $quantity = $_POST['quantity'];
    $date = $_POST['date']; // Assuming date is in YYYY-MM-DD format
    $subtotal = $_POST['subtotal'];
    $paymentStatus = isset($_POST['payment_status']) ? $_POST['payment_status'] : '';
    // Get payment status from dropdown
    $paymentMethod = $_POST['payment_method'];
    $notes = $_POST['notes'];
    $currency = $_POST['currency'];

    // Get product ID based on product name
    $productId = getProductId($productName);

    if ($productId) {
        // Prepare order details array
        $orderDetails = [
            'customer_id' => $customerId,
            'product_id' => $productId,
            'quantity' => $quantity,
            'date' => $date,
            'subtotal' => $subtotal,
            'payment_status' => $paymentStatus,
            'payment_method' => $paymentMethod,
            'notes' => $notes,
            'currency' => $currency
        ];

        // Save sales details
        $salesId = saveSalesDetails($orderDetails);

        if ($salesId) {
            // Deduct inventory
            deductInventory($productId, $quantity);

            // Redirect or display success message
            header("Location: sales_success.php");
            exit();
        } else {
            // Handle error saving sales details
            $error = "Failed to save sales details.";
        }
    } else {
        // Handle error finding product ID
        $error = "Product not found.";
    }
} else {
    // Handle if form is not submitted
    $error = "Form not submitted.";
}
?>
