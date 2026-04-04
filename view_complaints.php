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
<title>View Complaints</title>

<style>

body{
font-family:Arial;
background:linear-gradient(to right,#0f2027,#203a43,#2c5364);
color:white;
text-align:center;
}

table{
margin:40px auto;
border-collapse:collapse;
width:95%;
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

.btn{
padding:3px 6px;
background:red;
color:white;
text-decoration:none;
border-radius:3px;
}

.download{
background:green;
}

.update{
background:#28a745;
color:white;
border:none;
padding:6px 10px;
border-radius:5px;
cursor:pointer;
}

.back{
padding:10px 20px;
background:#28a745;
color:white;
text-decoration:none;
border-radius:6px;
}

img{
width:80px;
border-radius:5px;
}

</style>

</head>

<body>

<h2>All Complaints</h2>

<table>

<tr>
<th>ID</th>
<th>User ID</th>
<th>Subject</th>
<th>Location</th>
<th>Description</th>
<th>Status</th>
<th>Date</th>
<th>Evidence</th>
<th>Update Status</th>
<th>Action</th>
</tr>

<?php

$sql = "SELECT * FROM complaints";
$result = mysqli_query($conn,$sql);

if(mysqli_num_rows($result) > 0)
{
while($row = mysqli_fetch_assoc($result))
{
?>

<tr>

<td><?php echo $row['complaint_id']; ?></td>
<td><?php echo $row['user_id']; ?></td>
<td><?php echo $row['subject']; ?></td>
<td><?php echo $row['location']; ?></td>
<td><?php echo $row['description']; ?></td>
<td><?php echo $row['status']; ?></td>
<td><?php echo $row['created_at']; ?></td>

<td>
<?php
if($row['file']!=""){
?>
<img src="uploads/<?php echo $row['file']; ?>">
<br>
<a class="download btn" href="uploads/<?php echo $row['file']; ?>" download>Download Photo</a>
<?php
}else{
echo "No File";
}
?>
</td>

<td>

<form action="update_status.php" method="POST">

<input type="hidden" name="id" value="<?php echo $row['complaint_id']; ?>">

<select name="status">
<option value="Pending">Pending</option>
<option value="In Progress">In Progress</option>
<option value="Resolved">Resolved</option>
</select>

<button class="update" type="submit">Update</button>

</form>

</td>

<td>

<a class="btn" href="delete_complaint.php?id=<?php echo $row['complaint_id']; ?>">
Delete
</a>

<br><br>

<a class="btn download" href="download_complaint.php?id=<?php echo $row['complaint_id']; ?>">
Download PDF
</a>

</td>

</tr>

<?php
}
}
else
{
echo "<tr><td colspan='10'>No Complaints Found</td></tr>";
}
?>

</table>

<br>

<a class="back" href="admin_dashboard.php">Back to Dashboard</a>

</body>
</html>