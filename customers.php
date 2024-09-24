<?php
// Include database connection file
include 'connect.php';

// Initialize error message variable
$errorMsg = "";

// Check if form submitted for adding customer
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_customer'])) {
    // Retrieve form data
    $customer_name = $_POST['customer_name'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];
    
    // Check if email already exists
    $check_query = "SELECT * FROM customers WHERE email='$email'";
    $check_result = $con->query($check_query);

    if ($check_result && $check_result->num_rows > 0) {
        // Email already exists
        $errorMsg = "Error: Email already exists.";
    } else {
        // Insert into database
        $insert_query = "INSERT INTO customers (customer_name, email, phone_number, created_at) VALUES ('$customer_name', '$email', '$phone_number', NOW())";
        if ($con->query($insert_query) === TRUE) {
            echo "New customer added successfully";
            // Redirect or refresh to update the customer list
            header("Refresh:0");
            exit; // Stop further execution
        } else {
            echo "Error: " . $insert_query . "<br>" . $con->error;
        }
    }
}

// Fetch and display customers
$sql = "SELECT * FROM customers";
$result = $con->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 50px;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background-color: #17a2b8;
            color: #fff;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
        }
        .btn-info {
            background-color: #17a2b8;
            border: none;
        }
        .btn-info:hover {
            background-color: #138496;
        }
        .btn-back {
            background-color: #6c757d;
            border: none;
        }
        .btn-back:hover {
            background-color: #5a6268;
        }
        .table th, .table td {
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header text-center">
                <h2>Customer Management</h2>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <button class="btn btn-info" onclick="showAddCustomerForm()">Add New Customer</button>
                    <a href="user.php" class="btn btn-back">Back</a>
                </div>
                <!-- Add Customer Form -->
                <div id="addCustomerForm" style="display: none;">
                    <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                        <div class="form-group">
                            <label for="customer_name">Customer Name:</label>
                            <input type="text" name="customer_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="phone_number">Phone Number:</label>
                            <input type="text" name="phone_number" class="form-control" required>
                        </div>
                        <button type="submit" name="add_customer" class="btn btn-success">Add Customer</button>
                        <!-- Display error message -->
                        <?php if (!empty($errorMsg)) { ?>
                            <p style="color: red;"><?php echo $errorMsg; ?></p>
                        <?php } ?>
                    </form>
                </div>

                <!-- Customer Table -->
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Customer ID</th>
                            <th>Customer Name</th>
                            <th>Email</th>
                            <th>Phone Number</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row["customer_id"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["customer_name"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["email"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["phone_number"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["created_at"]) . "</td>";
                                echo "<td>";
                                echo "<a href='edit_customer.php?id=" . $row['customer_id'] . "' class='btn btn-warning btn-sm'><i class='fas fa-edit'></i> Edit</a> ";
                                echo "<a href='delete_customer.php?id=" . $row['customer_id'] . "' class='btn btn-danger btn-sm' onclick=\"return confirm('Are you sure you want to delete this customer?');\"><i class='fas fa-trash-alt'></i> Delete</a>";
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6'>No customers found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script>
        function showAddCustomerForm() {
            var form = document.getElementById('addCustomerForm');
            if (form.style.display === 'none') {
                form.style.display = 'block';
            } else {
                form.style.display = 'none';
            }
        }
    </script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php mysqli_close($con); ?>



