<?php
// Establish connection to your database
$servername = 'localhost'; 
$username = 'root'; 
$password = ''; 
$dbname = 'signupforms'; 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input to prevent SQL injection
    $product_name = mysqli_real_escape_string($conn, $_POST["product_name"]);
    $quantity = $_POST["quantity"];
    $unit_measured = $_POST["unit_measured"];
    $price_per_unit = $_POST["price_per_unit"];

    // Check if the product already exists
    $check_sql = "SELECT * FROM inventory WHERE product_name = '$product_name'";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows > 0) {
        // Product already exists, update price or quantity
        $update_sql = "UPDATE inventory SET quantity = '$quantity', price_per_unit = '$price_per_unit' WHERE product_name = '$product_name'";
        
        if ($conn->query($update_sql) === TRUE) {
            echo "Product '$product_name' details updated successfully.";
        } else {
            echo "Error updating product details: " . $conn->error;
        }
    } else {
        // Product doesn't exist, insert new inventory item
        $insert_sql = "INSERT INTO inventory (product_name, quantity, unit_measured, price_per_unit)
                       VALUES ('$product_name', '$quantity', '$unit_measured', '$price_per_unit')";

        if ($conn->query($insert_sql) === TRUE) {
            echo "New inventory item added successfully.";
        } else {
            echo "Error adding new inventory item: " . $conn->error;
        }
    }
} else {
    // Redirect to the inventory page if the form is not submitted
    header("Location: inventory.php");
    exit();
}

$conn->close();
?>

