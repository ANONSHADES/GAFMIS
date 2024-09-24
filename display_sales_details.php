<?php
session_start(); // Start the session

// Include database connection file
include 'connect.php';

// Function to fetch sales details
function fetchSalesDetails($con) {
    // Fetch consolidated sales details using GROUP BY and aggregation
    $sql = "
        SELECT 
            sales.sales_id,
            sales.customer_id,
            customers.customer_name,
            sales.date,
            sales.payment_status,
            sales.payment_method,
            sales.notes,
            sales.currency,
            sales.status,
            SUM(sale_transactions.quantity) AS total_quantity,
            SUM(sale_transactions.quantity * inventory.price) AS subtotal
        FROM 
            sales 
        JOIN 
            customers ON sales.customer_id = customers.customer_id 
        JOIN 
            sale_transactions ON sales.sales_id = sale_transactions.sales_id
        JOIN 
            inventory ON sale_transactions.product_id = inventory.id
        WHERE 
            sales.status != 'completed'
        GROUP BY 
            sales.sales_id
    ";
    $result = $con->query($sql);
    return $result;
}

// Fetch sales details
$result = fetchSalesDetails($con);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #007bff;
        }
        .container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            max-width: 1000px;
            margin: 0 auto;
        }
        .sale-details {
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 15px;
        }
        .sale-details h2 {
            text-align: center;
            color: #007bff;
            margin-bottom: 10px;
        }
        .sale-details table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .sale-details th, .sale-details td {
            border: 1px solid #dee2e6;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            color: #000;
        }
        .sale-details th {
            background-color: #007bff;
            color: white;
        }
        .action-buttons {
            text-align: center;
            margin-top: 10px;
        }
        .action-buttons button {
            margin-right: 10px;
            padding: 8px 12px;
            cursor: pointer;
            border: none;
            border-radius: 4px;
            color: white;
        }
        .print-btn {
            background-color: #28a745;
        }
        .print-btn:hover {
            background-color: #218838;
        }
        .completed-btn {
            background-color: #007bff;
        }
        .completed-btn:hover {
            background-color: #0056b3;
        }
        .back-btn {
            background-color: #6c757d;
            margin-top: 20px;
        }
        .back-btn:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <h1>Sales Details</h1>
    <div class="container">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="sale-details" id="sale-<?php echo $row['sales_id']; ?>">
                    <h2>Sale ID: <?php echo htmlspecialchars($row['sales_id']); ?></h2>
                    <p><strong>Customer Name:</strong> <?php echo htmlspecialchars($row['customer_name']); ?></p>
                    <p><strong>Date:</strong> <?php echo htmlspecialchars($row['date']); ?></p>
                    <p><strong>Payment Status:</strong> <?php echo htmlspecialchars($row['payment_status']); ?></p>
                    <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($row['payment_method']); ?></p>
                    <p><strong>Notes:</strong> <?php echo htmlspecialchars($row['notes']); ?></p>
                    <table>
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Total</th>
                        </tr>
                        <?php
                        // Query to fetch products for the current sale
                        $products_query = "
                            SELECT 
                                inventory.product_name,
                                sale_transactions.quantity,
                                inventory.price,
                                sale_transactions.quantity * inventory.price AS total_price
                            FROM 
                                sale_transactions
                            JOIN 
                                inventory ON sale_transactions.product_id = inventory.id
                            WHERE 
                                sale_transactions.sales_id = " . $row['sales_id'];
                        $products_result = $con->query($products_query);

                        if ($products_result->num_rows > 0):
                            while ($product_row = $products_result->fetch_assoc()):
                        ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($product_row['product_name']); ?></td>
                                    <td><?php echo htmlspecialchars($product_row['quantity']); ?></td>
                                    <td><?php echo htmlspecialchars($product_row['price']); ?></td>
                                    <td><?php echo htmlspecialchars($product_row['total_price']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4">No products found for this sale.</td>
                            </tr>
                        <?php endif; ?>
                    </table>
                    <p><strong>Subtotal:</strong> <?php echo htmlspecialchars($row['subtotal']); ?> <?php echo htmlspecialchars($row['currency']); ?></p>
                    <div class="action-buttons">
                        <button class="print-btn" onclick="printReceipt(<?php echo $row['sales_id']; ?>)">Print Receipt</button>
                        <button class="completed-btn" onclick="markCompleted(<?php echo $row['sales_id']; ?>)">Completed</button>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="sale-details">
                <p>No sales details found.</p>
            </div>
        <?php endif; ?>
        <a href="user.php"><button class="back-btn">Back</button></a>
    </div>

    <script>
        function printReceipt(salesId) {
            // Open a new window or tab to display the receipt content
            var printWindow = window.open('print_receipt.php?sales_id=' + salesId, '_blank');
            if (!printWindow) {
                alert('Please allow popups for this site'); // Alert if popups are blocked
            }
        }

        function markCompleted(salesId) {
        if (confirm("Are you sure you want to mark this sale as completed?")) {
            // Make an AJAX request to mark the sale as completed
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "mark_completed.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onload = function () {
                if (xhr.status === 200) {
                    // Hide the sale detail on successful completion
                    document.getElementById('sale-' + salesId).style.display = 'none';
                    alert(xhr.responseText); // Display success message
                } else {
                    alert("Failed to mark sale as completed.");
                }
            };
            xhr.send("sales_id=" + salesId);
            }
        }
    </script>
</body>
</html>
