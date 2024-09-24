<?php
// Include database connection
include 'connect.php';

// Function to calculate total price, subtotal, tax, discount, and total amount
function calculateTotalPrice($productId, $quantity, $discount)
{
    global $con; // Assuming $con is your database connection object

    // Fetch unit price per kilogram from database
    $sql = "SELECT price FROM products WHERE id = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "i", $productId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $pricePerKg);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    // Calculate subtotal, discount amount, tax amount, and total amount
    $subtotal = $pricePerKg * $quantity;
    $discountAmount = ($discount / 100) * $subtotal;
    $totalBeforeTax = $subtotal - $discountAmount;
    $taxAmount = 0.10 * $totalBeforeTax; // Assuming 10% tax rate
    $totalAmount = $totalBeforeTax + $taxAmount;

    return [
        'subtotal' => $subtotal,
        'discount_amount' => $discountAmount,
        'tax_amount' => $taxAmount,
        'total_amount' => $totalAmount
    ];
}

// Function to generate invoice and insert into database
function generateInvoice($orderDetails)
{
    global $con; // Assuming $con is your database connection object

    // Calculate total price and related amounts
    $priceDetails = calculateTotalPrice($orderDetails['product_id'], $orderDetails['quantity'], $orderDetails['discount']);

    // Generate invoice number
    $invoiceNumber = generateInvoiceNumber();

    // Insert invoice details into invoices table
    $sql = "INSERT INTO invoices (customer_id, due_date, subtotal, tax_amount, discount_amount, total_amount, payment_status, payment_method, notes, currency, invoice_number, invoice_date) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())"; // Assuming invoice_date is automatically set to current timestamp
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param(
        $stmt,
        "idddddsssss", // Updated data type string
        $orderDetails['customer_id'],
        $orderDetails['due_date'],
        $priceDetails['subtotal'],
        $priceDetails['tax_amount'],
        $priceDetails['discount_amount'],
        $priceDetails['total_amount'],
        $orderDetails['payment_status'],
        $orderDetails['payment_method'],
        $orderDetails['notes'],
        $orderDetails['currency'],
        $invoiceNumber
    );
    
    mysqli_stmt_execute($stmt);
    $invoiceId = mysqli_insert_id($con);
    mysqli_stmt_close($stmt);

    return $invoiceId;
}

// Function to generate auto-incremented invoice number
function generateInvoiceNumber() {
    // Example logic to generate invoice number (replace this with your actual logic)
    $prefix = 'INV'; // Example prefix for invoice number
    $randomDigits = mt_rand(100000, 999999); // Generate random 6-digit number
    $invoiceNumber = $prefix . $randomDigits;
    return $invoiceNumber;
}

// Function to save invoice details and generate invoice number
function saveInvoice($invoiceDetails) {
    // Call the generateInvoice function to generate the invoice and return the invoice ID
    $invoiceId = generateInvoice($invoiceDetails);
    return $invoiceId;
}
?>


