<?php
include 'connect.php';

if (isset($_GET['id'])) {
    $customer_id = $_GET['id'];
    $sql = "DELETE FROM customers WHERE customer_id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $customer_id);
    
    if ($stmt->execute()) {
        echo "Customer deleted successfully";
        header("Location: customers.php");
        exit;
    } else {
        echo "Error deleting customer: " . $con->error;
    }
    $stmt->close();
}

mysqli_close($con);
?>
