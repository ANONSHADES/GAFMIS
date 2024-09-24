<?php
// Include necessary files
include 'connect.php';
include 'functions.php';

// Check if 'order_id' parameter is set and not empty
if (isset($_GET['order_id']) && !empty($_GET['order_id'])) {
    $orderId = $_GET['order_id'];

    // Fetch order details from the orders table
    $sql = "SELECT * FROM orders WHERE id = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "i", $orderId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $order = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    // Fetch order products from the database
    $sql = "SELECT op.product_name, op.quantity, op.price
            FROM order_products op
            WHERE op.order_id = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "i", $orderId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $products = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
    mysqli_stmt_close($stmt);
} else {
    echo "Invalid or missing order ID.";
    exit;
}

// Handle fulfill button action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'fulfill') {
    // Update order status to Fulfilled
    $sql = "UPDATE orders SET status = 'Fulfilled' WHERE id = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "i", $orderId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Redirect to the same page to prevent form resubmission on refresh
    header("Location: view_receipt.php?order_id=" . $orderId);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Receipt</title>
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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
        }

        .button-back {
            display: inline-block;
            padding: 10px 20px;
            background-color: #6c757d;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .button-back:hover {
            background-color: #5a6268;
        }

        .button-print {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s ease;
            margin-bottom: 20px;
        }

        .button-print:hover {
            background-color: #0056b3;
        }

        .button-fulfill {
            display: inline-block;
            padding: 10px 20px;
            background-color: #28a745;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s ease;
            margin-bottom: 20px;
        }

        .button-fulfill:hover {
            background-color: #218838;
        }
    </style>
    <script>
        function printReceipt() {
            window.print();
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Order Receipt</h1>
        <p><strong>Order ID:</strong> <?php echo htmlspecialchars($order['id']); ?></p>
        <p><strong>Supplier Name:</strong> <?php echo htmlspecialchars($order['supplier_name']); ?></p>
        <p><strong>Order Date:</strong> <?php echo htmlspecialchars($order['order_date']); ?></p>
        <p><strong>Status:</strong> <?php echo htmlspecialchars($order['status']); ?></p>

        <h2>Order Products</h2>
        <table>
            <tr>
                <th>Product Name</th>
                <th>Quantity</th>
                <th>Price</th>
            </tr>
            <?php foreach ($products as $product) : ?>
                <tr>
                    <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                    <td><?php echo htmlspecialchars($product['quantity']); ?></td>
                    <td><?php echo htmlspecialchars($product['price']); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <!-- Fulfill Button Form -->
        <?php if ($order['status'] !== 'Fulfilled') : ?>
            <form method="post" style="display: inline;">
                <input type="hidden" name="action" value="fulfill">
                <button type="submit" class="button-fulfill">Fulfill Order</button>
            </form>
        <?php endif; ?>

        <button class="button-print" onclick="printReceipt()">Print</button>
        <a href="display_order_details.php?order_id=<?php echo htmlspecialchars($order['id']); ?>" class="button-back">Back</a>
    </div>
</body>
</html>
