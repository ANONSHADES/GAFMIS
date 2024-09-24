<?php
// Include database connection
include 'connect.php';

// Function to retrieve sales details based on sales ID
function getSalesDetails($salesId)
{
    global $con;

    $sql = "SELECT * FROM sales WHERE sales_id = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "i", $salesId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $salesDetails = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    return $salesDetails;
}

// Function to insert invoice details into the invoices table
function insertInvoiceDetails($customerId, $dueDate, $subtotal, $discountAmount, $totalAmount, $paymentStatus, $paymentMethod, $notes, $currency)
{
    global $con;

    $sql = "INSERT INTO invoices (invoice_date, customer_id, due_date, subtotal, discount_amount, total_amount, payment_status, payment_method, notes, currency) 
            VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "isddddsss", $customerId, $dueDate, $subtotal, $discountAmount, $totalAmount, $paymentStatus, $paymentMethod, $notes, $currency);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    return $result;
}

// Function to get customer details by ID
function getCustomerDetails($customerId)
{
    global $con;

    $sql = "SELECT * FROM customers WHERE customer_id = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "i", $customerId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $customer = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    return $customer;
}

// Function to get product details by ID
function getProductDetails($productId)
{
    global $con;

    $sql = "SELECT * FROM products WHERE id = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "i", $productId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $product = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    return $product;
}

// Function to retrieve all orders
function getOrders()
{
    global $con;

    $sql = "SELECT o.*, GROUP_CONCAT(CONCAT(op.product_name, ' - ', op.quantity, ' units @ $', op.price) SEPARATOR '<br>') as products 
            FROM orders o 
            JOIN order_products op ON o.id = op.order_id 
            GROUP BY o.id";
    $result = mysqli_query($con, $sql);
    $orders = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $orders[] = $row;
    }

    return $orders;
}

// Function to save order details into the orders table
function saveOrderDetails($supplierName, $orderDate, $products) {
    global $con;

    // Start a transaction
    mysqli_begin_transaction($con);

    try {
        // Insert the main order details
        $sql = "INSERT INTO orders (supplier_name, order_date) VALUES (?, ?)";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $supplierName, $orderDate);
        mysqli_stmt_execute($stmt);
        $orderId = mysqli_stmt_insert_id($stmt);
        mysqli_stmt_close($stmt);

        // Insert each product into the order_products table
        foreach ($products as $product) {
            $productName = $product['name'];
            $quantity = $product['quantity'];
            $price = $product['price'];

            $sql = "INSERT INTO order_products (order_id, product_name, quantity, price) VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($con, $sql);
            mysqli_stmt_bind_param($stmt, "isii", $orderId, $productName, $quantity, $price);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            // Update inventory for each product ordered
            $success = updateInventoryByName($productName, $quantity);
            if (!$success) {
                throw new Exception("Failed to update inventory for product: " . $productName);
            }
        }

        // Commit the transaction
        mysqli_commit($con);

        return $orderId;
    } catch (Exception $e) {
        // Rollback the transaction if something went wrong
        mysqli_rollback($con);
        return false;
    }
}

// Function to fulfill an order by updating the inventory
function fulfillOrder($orderId) {
    global $con;

    // Fetch products in the order
    $sql = "SELECT product_name, quantity FROM order_products WHERE order_id = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "i", $orderId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    // Begin a transaction
    mysqli_begin_transaction($con);

    try {
        while ($row = mysqli_fetch_assoc($result)) {
            $productName = $row['product_name'];
            $quantity = $row['quantity'];

            // Update inventory by adding the quantity
            $success = updateInventoryByName($productName, $quantity);

            if (!$success) {
                throw new Exception("Failed to update inventory for product: " . $productName);
            }
        }

        // Commit the transaction
        mysqli_commit($con);
        return true;
    } catch (Exception $e) {
        // Rollback the transaction if something went wrong
        mysqli_rollback($con);
        return false;
    }
}
// Function to update inventory by product name
function updateInventoryByName($productName, $quantityChange) {
    global $con;

    // Fetch current quantity from the inventory table
    $sql_select = "SELECT id, quantity FROM inventory WHERE product_name = ?";
    $stmt_select = mysqli_prepare($con, $sql_select);
    mysqli_stmt_bind_param($stmt_select, "s", $productName);
    mysqli_stmt_execute($stmt_select);
    $result_select = mysqli_stmt_get_result($stmt_select);

    if ($result_select && mysqli_num_rows($result_select) > 0) {
        // Product exists in inventory, update the quantity
        $product = mysqli_fetch_assoc($result_select);
        $productId = $product['id'];
        $currentQuantity = $product['quantity'];

        // Calculate new quantity
        $newQuantity = $currentQuantity + $quantityChange;

        // Update quantity in inventory table
        $sql_update = "UPDATE inventory SET quantity = ? WHERE id = ?";
        $stmt_update = mysqli_prepare($con, $sql_update);
        mysqli_stmt_bind_param($stmt_update, "ii", $newQuantity, $productId);
        $success = mysqli_stmt_execute($stmt_update);

        mysqli_stmt_close($stmt_select);
        mysqli_stmt_close($stmt_update);

        if ($success) {
            // Debug output - successful update
            error_log("Inventory updated successfully for product: $productName. New quantity: $newQuantity");
        } else {
            // Debug output - update failed
            error_log("Failed to update inventory for product: $productName");
        }

        return $success;
    } else {
        // Product not found in inventory table, insert new record
        $sql_insert = "INSERT INTO inventory (product_name, quantity) VALUES (?, ?)";
        $stmt_insert = mysqli_prepare($con, $sql_insert);
        mysqli_stmt_bind_param($stmt_insert, "si", $productName, $quantityChange);
        $success = mysqli_stmt_execute($stmt_insert);

        mysqli_stmt_close($stmt_select);
        mysqli_stmt_close($stmt_insert);

        if ($success) {
            // Debug output - successful insertion
            error_log("New product added to inventory: $productName. Quantity: $quantityChange");
        } else {
            // Debug output - insertion failed
            error_log("Failed to add new product to inventory: $productName");
        }

        return $success;
    }
}



// Function to get all products
function getProducts() {
    global $con;
    
    $sql = "SELECT * FROM products";
    $result = mysqli_query($con, $sql);
    $products = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }

    return $products;
}
?>
