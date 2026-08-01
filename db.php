<?php
// Database connection settings
$host = 'localhost';
$user = 'root';      // Default XAMPP username
$pass = '';          // Default XAMPP password is empty
$dbname = 'eseva';   // Your database name

// Create connection
$conn = mysqli_connect($host, $user, $pass, $dbname);

// Check connection
if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}

// Set charset
mysqli_set_charset($conn, 'utf8mb4');
?>

