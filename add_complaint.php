<?php
session_start();
include "db.php"; // Database connection

// Check if user logged in
if(!isset($_SESSION['user_id']))
{
header("Location: login.php");
exit();
}

// Get user id
$user_id = $_SESSION['user_id'];

// Secure form data
$type = mysqli_real_escape_string($conn, $_POST['type']);
$location = mysqli_real_escape_string($conn, $_POST['location']);
$description = mysqli_real_escape_string($conn, $_POST['description']);

$fileName = "";

// File Upload
if(isset($_FILES['file']) && $_FILES['file']['name'] != "")
{
$fileName = time() . "_" . $_FILES['file']['name'];
$target = "uploads/" . $fileName;

move_uploaded_file($_FILES['file']['tmp_name'], $target);
}

// Insert complaint into database
$sql = "INSERT INTO complaints (user_id, subject, location, description, file, status, created_at)
VALUES ('$user_id', '$type', '$location', '$description', '$fileName', 'Pending', NOW())";

if(mysqli_query($conn,$sql))
{
header("Location: dashboard.php");
exit();
}
else
{
echo "Complaint submission failed: " . mysqli_error($conn);
}

?>