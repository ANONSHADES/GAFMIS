<?php
session_start(); // Start the session

// Include database connection file
include 'connect.php';

// Validate the sales_id parameter
if (!isset($_GET['sales_id']) || !is_numeric($_GET['sales_id'])) {
    die('Invalid sales ID.');
}

$salesId = $_GET['sales_id'];

// Fetch the sales details including all products from the database
$sql = "
    SELECT 
        sales.sales_id,
        sales.date,
        sales.payment_status,
        sales.payment_method,
        sales.notes,
        sales.currency,
        customers.customer_name,
        inventory.product_name,
        sale_transactions.quantity,
        sale_transactions.price AS unit_price
    FROM 
        sales
    JOIN 
        customers ON sales.customer_id = customers.customer_id
    JOIN 
        sale_transactions ON sales.sales_id = sale_transactions.sales_id
    JOIN 
        inventory ON sale_transactions.product_id = inventory.id
    WHERE 
        sales.sales_id = $salesId
";
$result = $con->query($sql);

// Check if sale exists
if ($result->num_rows == 0) {
    die('Sale not found.');
}

// Fetch sale details
$saleDetails = array();
$totalAmount = 0;

while ($row = $result->fetch_assoc()) {
    // Calculate subtotal for each product
    $subtotal = $row['quantity'] * $row['unit_price'];
    $totalAmount += $subtotal;

    // Store sale details for output
    $saleDetails[] = [
        'product_name' => htmlspecialchars($row['product_name']),
        'quantity' => htmlspecialchars($row['quantity']),
        'unit_price' => htmlspecialchars($row['unit_price']),
        'subtotal' => htmlspecialchars($subtotal),
    ];
}

// Fetch sale details for header information
$result->data_seek(0); // Reset result set pointer to fetch header details
$sale = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #ffffff;
            color: #333;
            padding: 20px;
        }
        .receipt-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            background-color: #f8f9fa;
        }
        .receipt-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .receipt-header h2 {
            color: #007bff;
        }
        .receipt-details {
            margin-bottom: 20px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 10px;
        }
        .receipt-details p {
            margin: 5px 0;
        }
        .product-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .product-table th, .product-table td {
            border: 1px solid #dee2e6;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            color: #000;
        }
        .product-table th {
            background-color: #007bff;
            color: white;
        }
        .print-btn {
            text-align: center;
            margin-top: 20px;
        }
        .print-btn button {
            padding: 8px 12px;
            cursor: pointer;
            border: none;
            border-radius: 4px;
            background-color: #007bff;
            color: white;
        }
        .print-btn button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="receipt-header">
            <h2>Sale Receipt</h2>
        </div>
        <div class="receipt-details">
            <p><strong>Customer:</strong> <?php echo htmlspecialchars($sale['customer_name']); ?></p>
            <p><strong>Date:</strong> <?php echo htmlspecialchars($sale['date']); ?></p>
            <p><strong>Payment Status:</strong> <?php echo htmlspecialchars($sale['payment_status']); ?></p>
            <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($sale['payment_method']); ?></p>
            <p><strong>Notes:</strong> <?php echo htmlspecialchars($sale['notes']); ?></p>
        </div>
        <table class="product-table">
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Subtotal</th>
            </tr>
            <?php foreach ($saleDetails as $detail): ?>
                <tr>
                    <td><?php echo $detail['product_name']; ?></td>
                    <td><?php echo $detail['quantity']; ?></td>
                    <td><?php echo $detail['unit_price']; ?> <?php echo htmlspecialchars($sale['currency']); ?></td>
                    <td><?php echo $detail['subtotal']; ?> <?php echo htmlspecialchars($sale['currency']); ?></td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="3" style="text-align: right;"><strong>Total:</strong></td>
                <td><?php echo htmlspecialchars($totalAmount); ?> <?php echo htmlspecialchars($sale['currency']); ?></td>
            </tr>
        </table>
        <div class="print-btn">
            <button onclick="printReceipt()">Print Receipt</button>
        </div>
    </div>

    <script>
        function printReceipt() {
            window.print();
        }
    </script>
</body>
</html>
