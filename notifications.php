<?php
// Include database connection file
include 'connect.php';

// Fetch products with quantities below 100
$sql = "SELECT product_name FROM inventory WHERE quantity < 100";
$result = $con->query($sql);

// Initialize array to store understocked product names
$understockedProducts = [];

if ($result->num_rows > 0) {
    // Fetch product names and store them in the array
    while ($row = $result->fetch_assoc()) {
        $understockedProducts[] = $row['product_name'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        h2 {
            color: #17a2b8;
        }

        ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }

        li {
            margin-bottom: 10px;
            font-size: 18px; /* Adjusted font size */
        }

        .back-btn {
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

        .back-btn:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Notifications</h1>

        <?php if (!empty($understockedProducts)) : ?>
            <div>
                <h2>Understocked Products:</h2>
                <ul>
                    <?php foreach ($understockedProducts as $product) : ?>
                        <li><?php echo $product; ?> is understocked.</li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php else : ?>
            <p>No understocked products at the moment.</p>
        <?php endif; ?>

        <!-- Back Button -->
        <a href="user.php" class="back-btn">Back</a>
    </div>
</body>
</html>

<?php
// Close database connection
$con->close();
?>


