<?php
$conn = mysqli_connect("localhost", "root", "", "eseva", 3309);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>

