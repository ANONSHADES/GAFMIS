<?php
// Database credentials
$HOSTNAME = 'localhost';
$USERNAME = 'root';
$PASSWORD = ''; 
$DATABASE = 'signupforms';

// Establish connection
$con = mysqli_connect($HOSTNAME, $USERNAME, $PASSWORD, $DATABASE);

// Check connection and handle errors
if (!$con) {
    die("Connection failed: " . mysqli_connect_error()); // Use mysqli_connect_error() for connection errors
} else {
    // Optionally, you can echo a message indicating successful connection
    // echo "Connected successfully!";
}
?>

