<?php
session_start();
include("db.php");

if(!isset($_SESSION['admin']))
{
header("Location: admin_login.php");
exit();
}

$id = $_GET['id'];

$sql = "DELETE FROM complaints WHERE complaint_id='$id'";
mysqli_query($conn,$sql);

header("Location: view_complaints.php");
?>