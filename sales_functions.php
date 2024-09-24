<?php
// Include database connection
include 'connect.php';

// Function to retrieve the list of customers
function getCustomerList()
{
    global $con; // Assuming $con is your database connection object

    // Your SQL query to fetch customer names
    $sql = "SELECT customer_name FROM customers";
    $result = mysqli_query($con, $sql);

    if ($result) {
        $customerList = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $customerList[] = $row['customer_name'];
        }
        return $customerList;
    } else {
        // Handle query error
        return [];
    }
}

// Function to retrieve the list of products
function getSalesProductList()
{
    global $con; // Assuming $con is your database connection object

    // Your SQL query to fetch product names
    $sql = "SELECT product_name FROM products";
    $result = mysqli_query($con, $sql);

    if ($result) {
        $productList = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $productList[] = $row['product_name'];
        }
        return $productList;
    } else {
        // Handle query error
        return [];
    }
}

// Function to get customer ID based on customer name
function getCustomerId($customerName)
{
    global $con; // Assuming $con is your database connection object

    // Sanitize input to prevent SQL injection
    $customerName = mysqli_real_escape_string($con, $customerName);

    // Your SQL query to retrieve customer ID
    $sql = "SELECT customer_id FROM customers WHERE customer_name = '{$customerName}'";
    $result = mysqli_query($con, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['customer_id'];
    } else {
        // Handle no results found or query error
        return null;
    }
}

// Function to get product ID based on product name
function getProductId($productName)
{
    global $con; // Assuming $con is your database connection object

    // Sanitize the input to prevent SQL injection
    $productName = mysqli_real_escape_string($con, $productName);

    // Query to fetch product ID based on name
    $sql = "SELECT id FROM inventory WHERE product_name = '$productName'";
    $result = mysqli_query($con, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['id'];
    }

    // Product not found, return null or false as per your requirement
    return null;
}


// Function to save sales details into database
function saveSalesDetails($orderDetails)
{
    global $con; // Assuming $con is your database connection object

    // Sanitize input to prevent SQL injection
    $customerId = mysqli_real_escape_string($con, $orderDetails['customer_id']);
    $productId = mysqli_real_escape_string($con, $orderDetails['product_id']);
    $quantity = mysqli_real_escape_string($con, $orderDetails['quantity']);
    $date = mysqli_real_escape_string($con, $orderDetails['date']);
    $subtotal = mysqli_real_escape_string($con, $orderDetails['subtotal']);
    $paymentStatus = mysqli_real_escape_string($con, $orderDetails['payment_status']); // Add payment status here
    $paymentMethod = mysqli_real_escape_string($con, $orderDetails['payment_method']);
    $notes = mysqli_real_escape_string($con, $orderDetails['notes']);
    $currency = mysqli_real_escape_string($con, $orderDetails['currency']);

    // Your SQL query to insert sales details
    $sql = "INSERT INTO sales (customer_id, product_id, quantity, date, subtotal, payment_status, payment_method, notes, currency)
            VALUES ('{$customerId}', '{$productId}', '{$quantity}', '{$date}', '{$subtotal}', '{$paymentStatus}', '{$paymentMethod}', '{$notes}', '{$currency}')";

    $result = mysqli_query($con, $sql);

    if ($result) {
        return mysqli_insert_id($con); // Return the ID of the inserted record
    } else {
        // Handle query error
        return false;
    }
}





// Function to deduct inventory
function deductInventory($productId, $quantity)
{
    global $con; // Assuming $con is your database connection object

    // Prepare SQL statement to update inventory
    $sql = "UPDATE inventory SET quantity = quantity - ? WHERE id = ?";
    $stmt = mysqli_prepare($con, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ii", $quantity, $productId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true; // Return true if update successful
    } else {
        // Handle query preparation error
        return false; // Return false if query preparation fails
    }
}
