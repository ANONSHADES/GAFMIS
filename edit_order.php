<?php
include 'connect.php';

// Check if order ID is provided in the URL
if (!isset($_GET['id'])) {
    // Redirect or handle error if ID is not provided
    header("Location: orders.php");
    exit;
}

// Fetch order details based on ID
$order_id = $_GET['id'];
$sql = "SELECT * FROM orders WHERE id = ?";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result || mysqli_num_rows($result) == 0) {
    // Order not found, redirect or handle error
    header("Location: orders.php");
    exit;
}

$order = mysqli_fetch_assoc($result);

// Handle form submission for updating order
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $supplierName = $_POST['supplier_name'];
    $productName = $_POST['product_name'];
    $quantity = $_POST['quantity'];
    $price = $_POST['price'];

    // Update order details in the database
    $sql = "UPDATE orders SET supplier_name=?, product_name=?, quantity=?, price=? WHERE id=?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "ssiii", $supplierName, $productName, $quantity, $price, $order_id);
    mysqli_stmt_execute($stmt);

    // Redirect to orders.php after updating
    header("Location: orders.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Order</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
        }
        h1 {
            color: #333;
            text-align: center;
            margin-top: 50px;
        }
        form {
            max-width: 400px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        label {
            display: block;
            margin-bottom: 10px;
            color: #333;
        }
        input[type="text"],
        input[type="number"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button[type="submit"] {
            background-color: #4caf50;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        button[type="submit"]:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <h1>Edit Order</h1>
    <form method="POST">
        <label for="supplier_name">Supplier Name:</label>
        <input type="text" name="supplier_name" value="<?php echo $order['supplier_name']; ?>" required>

        <label for="product_name">Product Name:</label>
        <input type="text" name="product_name" value="<?php echo $order['product_name']; ?>" required>

        <label for="quantity">Quantity:</label>
        <input type="number" name="quantity" value="<?php echo $order['quantity']; ?>" required>

        <label for="price">Price:</label>
        <input type="number" name="price" value="<?php echo $order['price']; ?>" required>

        <button type="submit">Update Order</button>
    </form>
</body>
</html>

<?php mysqli_close($con); ?>

