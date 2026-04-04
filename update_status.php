<?php
include("db.php");

$id = $_POST['id'];
$status = $_POST['status'];

$sql = "UPDATE complaints SET status='$status' WHERE complaint_id='$id'";

mysqli_query($conn,$sql);

header("Location: view_complaints.php");
?>