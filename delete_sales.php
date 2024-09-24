<?php
include 'connect.php';

if (isset($_POST['sales_id'])) {
    $sales_id = intval($_POST['sales_id']);
    
    $sql = "DELETE FROM sales WHERE sales_id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $sales_id);
    
    if ($stmt->execute()) {
        echo "Sale deleted successfully";
    } else {
        echo "Error deleting sale";
    }
    
    $stmt->close();
    $con->close();
} else {
    echo "Invalid request";
}
?>
