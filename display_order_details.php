<?php
// Include necessary files
include 'connect.php';
include 'functions.php';

// Fetch all orders from the database
$sql = "SELECT * FROM orders";
$result = mysqli_query($con, $sql);
$orders = [];
while ($row = mysqli_fetch_assoc($result)) {
    $orders[] = $row;
}
mysqli_free_result($result);

// Function to handle order actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['order_id'])) {
        $orderId = $_POST['order_id'];

        if ($_POST['action'] === 'edit') {
            // Redirect to edit order page
            header("Location: edit_order.php?order_id=" . $orderId);
            exit;
        } elseif ($_POST['action'] === 'delete') {
            // Delete associated order products first to handle foreign key constraint
            $sql = "DELETE FROM order_products WHERE order_id = ?";
            $stmt = mysqli_prepare($con, $sql);
            mysqli_stmt_bind_param($stmt, "i", $orderId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            // Then delete the order from the database
            $sql = "DELETE FROM orders WHERE id = ?";
            $stmt = mysqli_prepare($con, $sql);
            mysqli_stmt_bind_param($stmt, "i", $orderId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            // Refresh the page to show updated orders
            header("Location: display_order_details.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Orders</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }

        .container {
            max-width: 1000px;
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

        .button {
            padding: 5px 10px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .button:hover {
            background-color: #0056b3;
        }

        .button-edit {
            background-color: #ffc107;
        }

        .button-edit:hover {
            background-color: #e0a800;
        }

        .button-delete {
            background-color: #dc3545;
        }

        .button-delete:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>All Orders</h1>

        <table>
            <tr>
                <th>Order ID</th>
                <th>Supplier Name</th>
                <th>Order Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($orders as $order) : ?>
                <tr>
                    <td><?php echo htmlspecialchars($order['id']); ?></td>
                    <td><?php echo htmlspecialchars($order['supplier_name']); ?></td>
                    <td><?php echo htmlspecialchars($order['order_date']); ?></td>
                    <td><?php echo htmlspecialchars($order['status']); ?></td>
                    <td>
                        <form method="post" style="display: inline;">
                            <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order['id']); ?>">
                            <button type="submit" name="action" value="edit" class="button button-edit">Edit</button>
                        </form>
                        <form method="post" style="display: inline;">
                            <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order['id']); ?>">
                            <button type="submit" name="action" value="delete" class="button button-delete" onclick="return confirm('Are you sure you want to delete this order?');">Delete</button>
                        </form>
                        <a href="view_receipt.php?order_id=<?php echo htmlspecialchars($order['id']); ?>" class="button">View</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <!-- Back Button -->
        <a href="user.php" class="button">Back</a>
    </div>
</body>
</html>
