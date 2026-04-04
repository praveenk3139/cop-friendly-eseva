<?php
session_start();
include("db.php");

if(!isset($_SESSION['admin']))
{
    header("Location: admin_login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>View Users</title>

<style>

body{
font-family: Arial, sans-serif;
background: linear-gradient(to right,#0f2027,#203a43,#2c5364);
color:white;
text-align:center;
}

h2{
margin-top:30px;
}

table{
margin:40px auto;
border-collapse:collapse;
width:85%;
background:white;
color:black;
}

th,td{
padding:12px;
border:1px solid #ccc;
}

th{
background:#007BFF;
color:white;
}

.back{
padding:10px 20px;
background:#28a745;
color:white;
text-decoration:none;
border-radius:6px;
}

</style>

</head>

<body>

<h2>Registered Users</h2>

<table>

<tr>
<th>User ID</th>
<th>Name</th>
<th>Email</th>
<th>Role</th>
</tr>

<?php

$sql = "SELECT * FROM users";
$result = mysqli_query($conn,$sql);

if(mysqli_num_rows($result) > 0)
{
while($row = mysqli_fetch_assoc($result))
{
?>

<tr>
<td><?php echo $row['user_id']; ?></td>
<td><?php echo $row['name']; ?></td>
<td><?php echo $row['email']; ?></td>
<td><?php echo $row['role']; ?></td>
</tr>

<?php
}
}
else
{
echo "<tr><td colspan='4'>No Users Found</td></tr>";
}

?>

</table>

<br>

<a class="back" href="admin_dashboard.php">Back to Dashboard</a>

</body>
</html>