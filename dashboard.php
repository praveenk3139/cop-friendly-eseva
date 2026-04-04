<?php
session_start();
include "db.php";

/* Protect page */
if(!isset($_SESSION['user_id'])){
header("Location: login.html");
exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Dashboard - Cop Friendly e-Seva</title>

<style>

/* GLOBAL */
*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:Arial, Helvetica, sans-serif;
}

/* BODY */
body{
background:linear-gradient(120deg,#0f2027,#203a43,#2c5364);
color:white;
}

/* NAVBAR */
.navbar{
display:flex;
justify-content:space-between;
align-items:center;
padding:15px 50px;
background:#111;
box-shadow:0 4px 10px rgba(0,0,0,0.5);
}

.logo{
font-size:20px;
font-weight:bold;
}

.nav-links a{
color:white;
margin-left:20px;
text-decoration:none;
transition:0.3s;
}

.nav-links a:hover{
color:#0ca678;
}

/* CONTAINER */
.container{
width:90%;
max-width:900px;
margin:30px auto;
background:rgba(255,255,255,0.08);
padding:25px;
border-radius:12px;
backdrop-filter:blur(10px);
box-shadow:0 5px 20px rgba(0,0,0,0.5);
}

/* HEADINGS */
h2,h3,h4{
color:#ffebeb;
margin-bottom:10px;
}

/* FORM */
input, textarea, select{
width:100%;
padding:10px;
margin:10px 0;
border-radius:6px;
border:none;
outline:none;
}

/* BUTTON */
button{
background:#22c55e;
color:white;
border:none;
padding:10px;
border-radius:6px;
cursor:pointer;
font-weight:bold;
transition:0.3s;
}

button:hover{
transform:scale(1.05);
}

/* COMPLAINT CARD */
.complaint{
background:rgba(255,255,255,0.1);
padding:15px;
margin:12px 0;
border-radius:10px;
}

.complaint b{
color:#fcd34d;
}

/* LINKS */
.btn{
display:inline-block;
margin-top:8px;
background:#22c55e;
padding:6px 10px;
border-radius:5px;
color:white;
text-decoration:none;
font-size:14px;
}

.download{
color:#00c6ff;
text-decoration:none;
}

/* LOGOUT */
.logout{
background:#ef4444;
padding:6px 12px;
border-radius:5px;
text-decoration:none;
color:white;
}

.logout:hover{
background:#dc2626;
}

hr{
border:1px solid #ffebeb;
margin:20px 0;
}

</style>

</head>

<body>

<!-- NAVBAR -->
<div class="navbar">
<div class="logo">Cop Friendly e-Seva</div>

<div class="nav-links">
<span>Welcome, <?php echo $_SESSION['name']; ?></span>
<a href="logout.php" class="logout">Logout</a>
</div>
</div>

<!-- MAIN CONTAINER -->
<div class="container">

<?php
if ($_SESSION['role'] == "police") {
?>

<h2>🚔 Police Dashboard</h2>
<h3>All Complaints</h3>

<?php
$result = mysqli_query($conn,"SELECT * FROM complaints ORDER BY complaint_id DESC");

if(mysqli_num_rows($result) > 0){
while($row = mysqli_fetch_assoc($result)){
?>

<div class="complaint">

<b>ID:</b> <?php echo $row['complaint_id']; ?><br>
<b>Type:</b> <?php echo $row['subject']; ?><br>
<b>Location:</b> <?php echo $row['location']; ?><br>
<b>Description:</b> <?php echo $row['description']; ?><br>
<b>Status:</b> <?php echo $row['status']; ?><br>
<b>Date:</b> <?php echo $row['created_at']; ?><br>

<?php if($row['file']!=""){ ?>
<b>Evidence:</b> 
<a class="download" href="uploads/<?php echo $row['file']; ?>" download>
Download File
</a><br>
<?php } ?>

<a href="download_complaint.php?id=<?php echo $row['complaint_id']; ?>" class="btn">
Download PDF
</a>

</div>

<?php
}
}else{
echo "No complaints found.";
}
?>

<?php
}else{
?>

<h2>👤 Citizen Dashboard</h2>

<h3>Register Complaint</h3>

<form action="add_complaint.php" method="post" enctype="multipart/form-data">

<select name="type" required>
<option value="">Select Complaint Type</option>
<option>Theft</option>
<option>Cyber Crime</option>
<option>Harassment</option>
<option>Fraud</option>
<option>Accident</option>
<option>Missing Person</option>
<option>Other</option>
</select>

<input type="text" name="location" placeholder="Enter location" required>

<textarea name="description" placeholder="Describe your complaint..." required></textarea>

<input type="file" name="file">

<button type="submit">Submit Complaint</button>

</form>

<hr>

<h3>Your Complaints</h3>

<?php

$user_id = $_SESSION['user_id'];

$result = mysqli_query($conn,"SELECT * FROM complaints WHERE user_id='$user_id' ORDER BY complaint_id DESC");

if(mysqli_num_rows($result) > 0){
while($row = mysqli_fetch_assoc($result)){
?>

<div class="complaint">

<b>ID:</b> <?php echo $row['complaint_id']; ?><br>
<b>Type:</b> <?php echo $row['subject']; ?><br>
<b>Location:</b> <?php echo $row['location']; ?><br>
<b>Status:</b> <?php echo $row['status']; ?><br>
<b>Date:</b> <?php echo $row['created_at']; ?><br>

<?php if($row['file']!=""){ ?>
<b>Evidence:</b> 
<a class="download" href="uploads/<?php echo $row['file']; ?>" download>
Download File
</a><br>
<?php } ?>

<a href="download_complaint.php?id=<?php echo $row['complaint_id']; ?>" class="btn">
Download PDF
</a>

</div>

<?php
}
}else{
echo "No complaints found.";
}
?>

<?php } ?>

</div>

</body>
</html>