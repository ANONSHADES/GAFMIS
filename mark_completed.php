<?php
session_start(); // Start the session

// Include database connection file
include 'connect.php';

// Check if sales_id is provided via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sales_id'])) {
    $salesId = $_POST['sales_id'];

    // Update the sales status to 'completed'
    $sql_update_sales = "UPDATE sales SET status = 'completed' WHERE sales_id = ?";
    $stmt_update_sales = $con->prepare($sql_update_sales);
    $stmt_update_sales->bind_param("i", $salesId);
    $stmt_update_sales->execute();
    $stmt_update_sales->close();

    // Fetch products for the sale
    $sql_fetch_products = "SELECT product_id, quantity FROM sale_transactions WHERE sales_id = ?";
    $stmt_fetch_products = $con->prepare($sql_fetch_products);
    $stmt_fetch_products->bind_param("i", $salesId);
    $stmt_fetch_products->execute();
    $result_products = $stmt_fetch_products->get_result();

    // Update inventory quantities
    while ($row = $result_products->fetch_assoc()) {
        $productId = $row['product_id'];
        $quantitySold = $row['quantity'];

        // Deduct sold quantity from inventory
        $sql_update_inventory = "UPDATE inventory SET quantity = quantity - ? WHERE id = ?";
        $stmt_update_inventory = $con->prepare($sql_update_inventory);
        $stmt_update_inventory->bind_param("ii", $quantitySold, $productId);
        $stmt_update_inventory->execute();
        $stmt_update_inventory->close();
    }

    // Close statement and connection
    $stmt_fetch_products->close();
    $con->close();

    // Return success response
    http_response_code(200);
    echo "Sale marked as completed successfully.";
    exit;
} else {
    // Return error response if sales_id is not provided
    http_response_code(400);
    echo "Error: Sales ID not provided.";
    exit;
}
?>
