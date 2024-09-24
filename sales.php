<?php
// Include database connection file
include 'connect.php';

// Fetch customers for dropdown
$customerQuery = "SELECT customer_id, customer_name FROM customers";
$customerResult = mysqli_query($con, $customerQuery);
$customers = mysqli_fetch_all($customerResult, MYSQLI_ASSOC);

// Fetch products for dropdown
$productQuery = "SELECT id, product_name, price FROM inventory";
$productResult = mysqli_query($con, $productQuery);
$products = mysqli_fetch_all($productResult, MYSQLI_ASSOC);

// Default currency
$currency = "Ksh";

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Initialize variables from form data
    $customer_id = $_POST["customer_id"];
    $productsData = $_POST["products"];
    $payment_status = $_POST["payment_status"];
    $payment_method = $_POST["payment_method"];
    $notes = $_POST["notes"];
    $date = $_POST["date"];

    // Validate data (you can add more validation as per your requirements)

    // Calculate subtotal and prepare for database insertion
    $subtotal = 0;
    foreach ($productsData as $productData) {
        $product_id = $productData['id'];
        $quantity = $productData['quantity'];
        $price = getPriceForProduct($product_id); // Function to fetch price from database
        $subtotal += $quantity * $price;
    }

    // Start transaction
    mysqli_begin_transaction($con);

    try {
        // Insert into sales table
        $sql = "INSERT INTO sales (customer_id, date, subtotal, payment_status, payment_method, notes, currency) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "isdssss", $customer_id, $date, $subtotal, $payment_status, $payment_method, $notes, $currency);
        mysqli_stmt_execute($stmt);
        $sales_id = mysqli_insert_id($con); // Get the last inserted ID
        mysqli_stmt_close($stmt);

        // Insert each product into sale_transactions table
        $sql = "INSERT INTO sale_transactions (sales_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($con, $sql);

        foreach ($productsData as $productData) {
            $product_id = $productData['id'];
            $quantity = $productData['quantity'];
            $price = getPriceForProduct($product_id); // Function to fetch price from database
            mysqli_stmt_bind_param($stmt, "iiid", $sales_id, $product_id, $quantity, $price);
            mysqli_stmt_execute($stmt);
        }
        mysqli_stmt_close($stmt);

        // Commit transaction
        mysqli_commit($con);

        // Redirect or display success message
        echo '<div class="alert alert-success mt-3" role="alert">Sale recorded successfully</div>';

    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($con);
        echo '<div class="alert alert-danger mt-3" role="alert">Error: ' . $e->getMessage() . '</div>';
    }

    // Close MySQLi connection
    mysqli_close($con);
}

// Function to get price for a product from the database
function getPriceForProduct($product_id) {
    global $con;
    $sql = "SELECT price FROM inventory WHERE id = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $price);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
    return $price;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Record Sale</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        form {
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }

        select, input[type="number"], input[type="date"], button[type="submit"], button[type="button"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        button[type="submit"], button[type="button"] {
            display: inline-block;
            padding: 10px 20px;
            background-color: #17a2b8;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button[type="submit"]:hover, button[type="button"]:hover {
            background-color: #138496;
        }

        .back-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #6c757d;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 10px;
            text-decoration: none;
            text-align: center;
            display: block;
            width: 100%;
        }

        .back-button:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Record Sale</h1>

        <!-- Sales Form -->
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <label for="customer_id">Customer:</label>
            <select id="customer_id" name="customer_id" required>
                <option value="">Select Customer</option>
                <?php foreach ($customers as $customer) : ?>
                    <option value="<?php echo $customer['customer_id']; ?>"><?php echo $customer['customer_name']; ?></option>
                <?php endforeach; ?>
            </select>

            <div id="product-section">
                <div class="product-group">
                    <label for="product_id">Product:</label>
                    <select name="products[0][id]" required>
                        <option value="">Select Product</option>
                        <?php foreach ($products as $product) : ?>
                            <option value="<?php echo $product['id']; ?>"><?php echo $product['product_name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label for="quantity">Quantity:</label>
                    <input type="number" name="products[0][quantity]" required>
                </div>
            </div>
            <button type="button" onclick="addProduct()">Add Another Product</button>

            <label for="payment_status">Payment Status:</label>
            <select id="payment_status" name="payment_status" required>
                <option value="paid">Paid</option>
                <option value="pending">Pending</option>
            </select>

            <label for="payment_method">Payment Method:</label>
            <select id="payment_method" name="payment_method" required>
                <option value="">Select Payment Method</option>
                <option value="Cash">Cash</option>
                <option value="Card">Card</option>
                <option value="M-pesa">M-pesa</option>
            </select>

            <label for="notes">Notes:</label>
<textarea id="notes" name="notes">Thank you for choosing Gakami Animal Feeds</textarea>

<label for="date">Date:</label>
<input type="date" id="date" name="date" autocomplete="off" required style="width: 100%; padding: 10px; margin-top: 5px; margin-bottom: 20px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box;">


            <button type="submit">Record Sale</button>
        </form>

        <!-- Back Button -->
        <a href="user.php" class="back-button">Back</a>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
    <script>
        // Initialize datepicker
        $('#date').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            todayHighlight: true
       
        });

// Function to add another product input group
function addProduct() {
    const productSection = document.getElementById('product-section');
    const productGroup = document.createElement('div');
    productGroup.className = 'product-group';
    productGroup.innerHTML = `
        <label for="product_id">Product:</label>
        <select name="products[${document.querySelectorAll('.product-group').length}][id]" required>
            <option value="">Select Product</option>
            <?php foreach ($products as $product) : ?>
                <option value="<?php echo $product['id']; ?>"><?php echo $product['product_name']; ?></option>
            <?php endforeach; ?>
        </select>
        <label for="quantity">Quantity:</label>
        <input type="number" name="products[${document.querySelectorAll('.product-group').length}][quantity]" required>
    `;
    productSection.appendChild(productGroup);
}
</script>
</body>
</html>
