<?php
// Start the session at the beginning, check if session is already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include the database connection file
include 'connect.php';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve username and password from the form
    $username = $_POST["username"];
    $password = $_POST["password"];

    // Query to fetch hashed password from the database
    $sql = "SELECT password FROM registration WHERE username=?";
    $stmt = mysqli_prepare($con, $sql);

    if ($stmt) {
        // Bind parameters
        mysqli_stmt_bind_param($stmt, "s", $username);

        // Execute the statement
        mysqli_stmt_execute($stmt);

        // Get result
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) == 1) {
            // Fetch the hashed password from the result
            $row = mysqli_fetch_assoc($result);
            $hashed_password = $row['password'];

            // Verify the entered password with the hashed password
            if (password_verify($password, $hashed_password)) {
                // Password is correct, set session variables
                $_SESSION['username'] = $username;
                // Redirect to user.php or any other page
                header("Location: user.php");
                exit();
            } else {
                // Password is incorrect, display error message
                $error = "Invalid username or password";
            }
        } else {
            // User not found in the database, display error message
            $error = "Invalid username or password";
        }

        // Close the statement
        mysqli_stmt_close($stmt);
    } else {
        // Handle statement preparation error
        die(mysqli_error($con));
    }
}

// Close MySQLi connection
mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa; /* Background color */
            margin: 0; /* Remove default margin */
            background-image: url('backkground.jpg'); /* Background image URL */
            background-size: cover; /* Cover the entire content area */
            background-position: center; /* Center the background image */
            background-repeat: no-repeat; /* Prevent background image repetition */
            position: relative; /* Added for absolute positioning */
        }
        .taskbar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: 200px; /* Taskbar width */
            background-color: #343a40; /* Taskbar background color */
            padding-top: 60px; /* Space for user info */
            overflow-y: auto;
            display: flex; /* Use flexbox to align items */
            flex-direction: column; /* Stack items vertically */
        }
        .task-item {
            padding: 10px;
            color: #fff; /* Task item text color */
            display: block;
            text-decoration: none;
            flex-grow: 1; /* Grow to fill available space */
            text-align: center; /* Center text */
            transition: background-color 0.3s ease; /* Smooth hover effect */
        }
        .task-item:hover {
            background-color: #495057; /* Hover background color */
            cursor: pointer;
        }
        .task-icon {
            margin-right: 10px;
        }
        .content {
            margin-left: 200px; /* Adjust for taskbar width */
            padding: 20px;
            height: 100vh; /* Set content height to fill entire viewport */
            display: flex; /* Use flexbox to center content vertically */
            justify-content: center; /* Center content horizontally */
            align-items: center; /* Center content vertically */
            max-width: 800px; /* Set maximum width for content */
            text-align: center; /* Center text within content */
            flex-direction: column; /* Stack items vertically */
            margin-top: 20px; /* Adjust the margin from the top */
        }
        .user-info, .notifications {
            color: #fff; /* User info text color */
            padding: 10px;
            background-color: #343a40; /* Same as taskbar */
            text-align: right; /* Align text to the right */
            margin-top: auto; /* Align to the bottom */
        }
        .logout {
            color: #fff; /* Logout button text color */
            background-color: #dc3545; /* Red color */
            padding: 10px;
            margin: 10px; /* Add some margin */
            text-align: center; /* Center text */
        }
        .contact-info {
            position: absolute; /* Position the contact info */
            bottom: 20px; /* Bottom margin */
            right: 20px; /* Right margin */
            background-color: #343a40; /* Contact info background color */
            padding: 10px; /* Padding for contact info */
            border-radius: 5px; /* Rounded corners */
            text-align: right; /* Align text to the right */
        }
        .contact-info a {
            display: block;
            margin-top: 10px;
            text-decoration: none;
            color: #f0e6e6;
        }
        .contact-info a i {
            margin-right: 10px;
        }
        /* Message color */
        .welcome-message {
            color: #000000; /* Welcome message color */
        }
        .main-message {
            color: #00FFFF; /* Main message color */
        }
        .notification-badge {
            background-color: red;
            color: white;
            font-size: 12px;
            padding: 4px 8px;
            border-radius: 50%;
            position: absolute;
            top: 5px;
            right: 5px;
        }
    </style>
</head>
<body>
    <div class="taskbar">
        <a href="inventory.php" class="task-item">
            <i class="fas fa-boxes task-icon"></i>
            Inventory
        </a>
        <a href="sales.php" class="task-item">
            <i class="fas fa-chart-line task-icon"></i>
            Sales
        </a>
        <a href="display_sales_details.php" class="task-item"> <!-- New "Sales Details" link -->
            <i class="fas fa-list task-icon"></i> <!-- Icon for Sales Details -->
            Sales Details
        </a>
        <a href="orders.php" class="task-item"> <!-- New "Orders" link -->
            <i class="fas fa-shopping-cart task-icon"></i> <!-- Icon for Orders -->
            Orders
        </a>
        <a href="display_order_details.php" class="task-item">
    <i class="fas fa-list task-icon"></i>
    Order Details
</a>
        <a href="customers.php" class="task-item">
            <i class="fas fa-users task-icon"></i>
            Customers
        </a>
        <a href="suppliers.php" class="task-item"> <!-- New "Suppliers" link -->
            <i class="fas fa-truck task-icon"></i> <!-- Icon for Suppliers -->
            Suppliers
        </a>
        <!-- Add Reports link in the taskbar -->
        <a href="reports.php" class="task-item">
            <i class="fas fa-file-alt task-icon"></i>
            Reports
        </a>

        <div class="logout" onclick="logout()">
            Logout
        </div>
    </div>
    <!-- User info and notifications -->
    <div class="notifications">
        <!-- Bell icon wrapped within its own div with onclick event -->
        <div id="notificationBell" onclick="goToNotificationsPage()">
            <i class="fas fa-bell"></i>
            <span id="notificationCount" style="color: red;"></span>
            <?php
            // Check if there are understocked products
            if (!empty($understockedProducts)) {
                // Count the number of understocked products
                $notificationCount = count($understockedProducts);
                // Display a badge with the notification count
                echo "<span class='notification-badge'>$notificationCount</span>";
            }
            ?>
        </div>
        <!-- Display logged-in user's name here -->
        <span id="loggedInUser">
            <?php
            // Check if the username is set in the session
            if (isset($_SESSION['username'])) {
                echo "Welcome, " . $_SESSION['username'];
            } else {
                echo "Session data not found";
            }
            ?>
        </span>
    </div>

    <!-- Main content -->
    <div class="content">
        <!-- Welcome message -->
        <h1 class="welcome-message" style="margin-top: 50px;">Welcome to Gakami Animal Feeds Inventory And Sales Management System</h1>
<!-- Main message -->
<div class="main-message">
    <p>Keep your furry (or feathered) friends fed! This dashboard gives you a quick overview of your animal feed inventory. Track current stock levels, identify products running low, and plan your next order efficiently. Ensure your animals stay happy and healthy with a well-managed feed supply.</p>
</div>
<!-- Contact Support section -->
<div class="contact-info">
    <h3>Contact Support</h3>
    <a href="mailto:ianmwas896@gmail.com"><i class="fas fa-envelope"></i>ianmwas896@gmail.com</a>
    <a href="https://wa.me/+254797074659"><i class="fab fa-whatsapp"></i>+254797074659</a>
</div>
</div>

<!-- Font Awesome JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
<!-- Logout function -->
<script>
function logout() {
    // Redirect user to logout page to clear authentication session variables
    window.location.href = "logout.php";
}
// Function to update the notification badge count
function updateNotificationCount() {
    // Check if there are understocked products
    <?php if (!empty($understockedProducts)) : ?>
        // Count the number of understocked products
        var notificationCount = <?php echo count($understockedProducts); ?>;
        // Create a new span element for the badge
        var notificationBadge = document.createElement('span');
        notificationBadge.classList.add('notification-badge');
        notificationBadge.textContent = notificationCount;
        // Append the badge to the notification bell
        document.getElementById('notificationBell').appendChild(notificationBadge);
    <?php endif; ?>
}

// Call the function when the page loads
window.onload = function() {
    updateNotificationCount();
};
function goToNotificationsPage() {
    // Redirect user to notifications page
    window.location.href = "notifications.php";
}
</script>
</body>
</html>

       
