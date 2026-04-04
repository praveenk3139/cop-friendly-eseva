<?php
session_start();
include "db.php"; // Database connection

// Check login
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get form data
$type = mysqli_real_escape_string($conn, $_POST['type']);
$description = mysqli_real_escape_string($conn, $_POST['description']);

// Insert complaint
$sql = "INSERT INTO complaints (user_id, subject, description, status, created_at)
        VALUES ('$user_id', '$type', '$description', 'Pending', NOW())";

if(mysqli_query($conn,$sql)){

    // Get last inserted complaint id
    $complaint_id = mysqli_insert_id($conn);

    // redirect to success page with id
    header("Location: complaint_success.php?id=".$complaint_id);
    exit();

}else{
    echo "Error : ".mysqli_error($conn);
}
?>