<?php
// Include database connection
include 'connect.php';

// Function to get price per unit based on product ID
function getPricePerUnit($productId)
{
    global $con;

    $sql = "SELECT price FROM inventory WHERE id = ?";
    $stmt = mysqli_prepare($con, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $productId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $pricePerUnit);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        return $pricePerUnit; // Return price per unit
    } else {
        // Handle query preparation error
        return null; // Return null if query preparation fails
    }
}

// Function to get available quantity of a product based on product ID
function getAvailableQuantity($productId)
{
    global $con;

    $sql = "SELECT quantity FROM inventory WHERE id = ?";
    $stmt = mysqli_prepare($con, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $productId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $availableQuantity);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        return $availableQuantity; // Return available quantity
    } else {
        // Handle query preparation error
        return null; // Return null if query preparation fails
    }
}

// Function to get product list
function getProductList() {
    global $con;
    $productList = array();

    $sql = "SELECT product_name FROM inventory";
    $result = $con->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $productList[] = $row['product_name'];
        }
    }

    return $productList;
}

?>




    



