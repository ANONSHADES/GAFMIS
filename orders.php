<?php
// Include necessary files
include 'connect.php';
include 'functions.php';

// Fetch suppliers from the database
$suppliers = []; // Initialize array to store suppliers
$sql = "SELECT * FROM suppliers";
$result = $con->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $suppliers[] = $row['name'];
    }
}

// Fetch products from the inventory table
$products = []; // Initialize array to store products
$sql = "SELECT * FROM inventory";
$result = $con->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row['product_name'];
    }
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle form submission (saving order details)
    // Retrieve form data
    $supplierName = $_POST['supplier_name']; // Supplier name input
    $orderDate = $_POST['order_date']; // Order date input
    $productsData = $_POST['products'];

    // Save order details in the database
    $orderId = saveOrderDetails($supplierName, $orderDate, $productsData);

    if ($orderId) {
        // Redirect to display_order_details.php to display the updated list of orders
        header("Location: display_order_details.php?order_id=$orderId");
        exit();
    } else {
        // Handle error if order details couldn't be saved
        $error = "Failed to save order details. Please try again.";
    }
}

// Fetch orders from the database
$orders = getOrders();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        form {
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }

        input[type="text"],
        input[type="number"],
        input[type="date"],
        select {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        button[type="submit"] {
            display: inline-block;
            padding: 10px 20px;
            background-color: #17a2b8;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button[type="submit"]:hover {
            background-color: #138496;
        }

        button[type="button"] {
            display: inline-block;
            padding: 10px 20px;
            background-color: #6c757d;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 10px;
        }

        button[type="button"]:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Orders</h1>

        <!-- New Order Form -->
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <label for="supplier_name">Supplier Name:</label>
            <select name="supplier_name" id="supplier_name" required>
                <!-- Dropdown menu options filled with suppliers -->
                <?php foreach ($suppliers as $supplier) : ?>
                    <option value="<?php echo $supplier; ?>"><?php echo $supplier; ?></option>
                <?php endforeach; ?>
            </select>

            <label for="order_date">Order Date:</label>
            <input type="date" name="order_date" id="order_date" required>

            <label for="products">Products:</label>
            <div id="products">
                <div class="product">
                    <select name="products[0][name]" required>
                        <option value="">Select Product</option>
                        <!-- Dropdown menu options filled with products -->
                        <?php foreach ($products as $product) : ?>
                            <option value="<?php echo $product; ?>"><?php echo $product; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="number" name="products[0][quantity]" placeholder="Quantity" required>
                    <input type="number" name="products[0][price]" placeholder="Price" required>
                </div>
            </div>
            <button type="button" onclick="addProduct()">Add Another Product</button>

            <button type="submit">Submit Order</button>
        </form>

        <!-- Back Button -->
        <a href="user.php" class="button-back">Back</a>
    </div>

    <script>
        let productIndex = 1;

        function addProduct() {
            const productsDiv = document.getElementById('products');
            const newProductDiv = document.createElement('div');
            newProductDiv.className = 'product';
            newProductDiv.innerHTML = `
                <select name="products[${productIndex}][name]" required>
                    <option value="">Select Product</option>
                    <?php foreach ($products as $product) : ?>
                        <option value="<?php echo $product; ?>"><?php echo $product; ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="number" name="products[${productIndex}][quantity]" placeholder="Quantity" required>
                <input type="number" name="products[${productIndex}][price]" placeholder="Price" required>
            `;
            productsDiv.appendChild(newProductDiv);
            productIndex++;
        }
    </script>
</body>
</html>
