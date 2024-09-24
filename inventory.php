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
    $product_name = $_POST["product_name"];
    $quantity = $_POST["quantity"];
    $unit_measured = $_POST["unit_measured"];
    $price_per_unit = $_POST["price_per_unit"];

    // Check if the product already exists
    $check_sql = "SELECT * FROM inventory WHERE product_name = '$product_name'";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows > 0) {
        // Product already exists, display error message
        echo '<div id="popupMessage" class="popup">Product already exists. <span class="close" onclick="closePopup()">&times;</span></div>';
    } else {
        // Product doesn't exist, insert new inventory item
        $insert_sql = "INSERT INTO inventory (product_name, quantity, unit_measured, price)
                       VALUES ('$product_name', '$quantity', '$unit_measured', '$price_per_unit')";

        if ($conn->query($insert_sql) === TRUE) {
            echo '<div id="popupMessage" class="popup">Inventory added successfully. <span class="close" onclick="closePopup()">&times;</span></div>';
        } else {
            echo "Error adding new inventory item: " . $conn->error;
        }
    }
}

// Retrieve inventory items from the database
$sql = "SELECT * FROM inventory";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f8ff;
            color: #333;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #007bff;
        }
        #buttonContainer {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        #addInventoryButton, #backButton {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
        }
        #addInventoryButton:hover, #backButton:hover {
            background-color: #0056b3;
        }
        #addInventoryForm {
            display: none;
            margin-bottom: 20px;
            background-color: #e6f7ff;
            padding: 20px;
            border: 1px solid #b3e0ff;
            border-radius: 8px;
        }
        #addInventoryForm label {
            display: block;
            margin-bottom: 5px;
            color: #007bff;
        }
        #addInventoryForm input[type="text"],
        #addInventoryForm input[type="number"],
        #addInventoryForm input[type="date"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        #addInventoryForm button[type="submit"] {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 4px;
        }
        #addInventoryForm button[type="submit"]:hover {
            background-color: #0056b3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ccc;
        }
        table th, table td {
            padding: 10px;
            text-align: center;
        }
        table th {
            background-color: #007bff;
            color: white;
        }
        table tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        table tr:hover {
            background-color: #ddd;
        }
        .popup {
            background-color: #4CAF50;
            color: white;
            text-align: center;
            padding: 10px;
            margin-top: 10px;
            display: none;
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            border-radius: 4px;
        }
        .close {
            color: #000;
            float: right;
            font-size: 20px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover {
            color: #888;
        }
    </style>
</head>
<body>
    <h1>Inventory</h1>

    <div id="buttonContainer">
        <!-- Add Inventory Button -->
        <button id="addInventoryButton" onclick="toggleForm()">Add Inventory</button>
        <!-- Back Button -->
        <a href="user.php"><button id="backButton">Back</button></a>
    </div>

    <!-- Add Inventory Form -->
    <div id="addInventoryForm">
        <form action="inventory.php" method="POST">
            <label for="product_name">Product Name:</label>
            <input type="text" id="product_name" name="product_name" required><br><br>
            
            <label for="quantity">Quantity:</label>
            <input type="number" id="quantity" name="quantity" required><br><br>
            
            <label for="unit_measured">Unit Measured:</label>
            <input type="text" id="unit_measured" name="unit_measured" required><br><br>
            
            <label for="price_per_unit">Price Per Unit:</label>
            <input type="text" id="price_per_unit" name="price_per_unit" required><br><br>
            
            <button type="submit">Add Inventory</button>
        </form>
    </div>

    <!-- Display Inventory Table -->
    <table>
        <tr>
            <th>ID</th>
            <th>Product Name</th>
            <th>Quantity</th>
            <th>Unit Measured</th>
            <th>Price</th>
            <th>Actions</th> <!-- New column for edit button -->
        </tr>
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row["id"] . "</td>";
                echo "<td>" . $row["product_name"] . "</td>";
                echo "<td>" . $row["quantity"] . "</td>";
                echo "<td>" . $row["unit_measured"] . "</td>";
                echo "<td>" . $row["price"] . "</td>";
                // Edit button linking to update_inventory.php with ID
                echo "<td><a href='update_inventory.php?id=" . $row['id'] . "'>Edit</a></td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='6'>No inventory items found</td></tr>";
        }
        ?>
    </table>

    <!-- Popup message -->
    <div id="popupMessage" class="popup">
        <!-- Popup content goes here -->
    </div>

    <script>
        function toggleForm() {
            var form = document.getElementById('addInventoryForm');
            form.style.display = form.style.display === 'block' ? 'none' : 'block';
        }

        function closePopup() {
            var popup = document.getElementById('popupMessage');
            popup.style.display = 'none';
        }

        // Show the popup message if it exists
        document.addEventListener('DOMContentLoaded', function() {
            var popup = document.getElementById('popupMessage');
            if (popup && popup.innerHTML.trim() !== '') {
                popup.style.display = 'block';
            }
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>


