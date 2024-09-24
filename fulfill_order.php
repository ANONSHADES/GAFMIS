<?php
// Include necessary files
include 'connect.php';
include 'functions.php';

// Check if order ID and action are provided via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'fulfill' && isset($_POST['order_id'])) {
    $orderId = $_POST['order_id'];

    // Fulfill the order (assuming inventory update logic is implemented in functions.php)
    $success = fulfillOrder($orderId);

    if ($success) {
        // If order fulfillment is successful, update the order status in the database
        $sql = "UPDATE orders SET status = 'Fulfilled' WHERE id = ?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "i", $orderId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // Return success response
        echo "success";
        exit;
    } else {
        // Return error response
        echo "error";
        exit;
    }
}
?>
