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

// Check if the product ID is provided in the URL
if (!isset($_GET['id'])) {
    echo "Product ID is not provided.";
    exit;
}

// Retrieve the product ID from the URL
$id = $_GET['id'];

// Retrieve inventory item details from the database based on the ID passed through GET method
$sql = "SELECT * FROM inventory WHERE id = '$id'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
} else {
    echo "No inventory item found with the given ID.";
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_name = $_POST["product_name"];
    $quantity = $_POST["quantity"];
    $unit_measured = $_POST["unit_measured"];
    $price = $_POST["price"];

    // Prepare and execute SQL statement to update inventory item
    $update_sql = "UPDATE inventory SET product_name = '$product_name', quantity = '$quantity', unit_measured = '$unit_measured', price = '$price' WHERE id = '$id'";

    if ($conn->query($update_sql) === TRUE) {
        echo "Inventory updated successfully.";
        // Redirect to inventory.php after updating
        header("Location: inventory.php");
        exit;
    } else {
        echo "Error updating inventory: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Inventory</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f3f3f3;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #17a2b8;
        }
        form {
            margin-top: 20px;
        }
        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }
        input[type="text"],
        input[type="number"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        button[type="submit"] {
            background-color: #17a2b8;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button[type="submit"]:hover {
            background-color: #138496;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Update Inventory</h1>

        <!-- Update Inventory Form -->
        <form action="update_inventory.php?id=<?php echo $id; ?>" method="POST">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <label for="product_name">Product Name:</label>
            <input type="text" id="product_name" name="product_name" value="<?php echo $row['product_name']; ?>" required><br><br>
            
            <label for="quantity">Quantity:</label>
            <input type="number" id="quantity" name="quantity" value="<?php echo $row['quantity']; ?>" required><br><br>
            
            <label for="unit_measured">Unit Measured:</label>
            <input type="text" id="unit_measured" name="unit_measured" value="<?php echo $row['unit_measured']; ?>" required><br><br>
            
            <label for="price">Price:</label>
            <input type="text" id="price" name="price" value="<?php echo $row['price']; ?>" required><br><br>
            
            <button type="submit">Update Inventory</button>
        </form>
    </div>
</body>
</html>

<?php
$conn->close();
?>



