<?php
session_start();

if(!isset($_SESSION['admin'])){
header("Location: admin_login.php");
exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Dashboard - Cop Friendly e-Seva</title>

<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:Arial, Helvetica, sans-serif;
}

body{
background:linear-gradient(120deg,#0f2027,#203a43,#2c5364);
color:white;
}

/* NAVBAR */

.navbar{
display:flex;
justify-content:space-between;
align-items:center;
padding:15px 40px;
background:#111;
box-shadow:0 3px 10px rgba(0,0,0,0.5);
}

.logo{
font-size:20px;
font-weight:bold;
color:#fff;
}

.navbar a{
color:white;
text-decoration:none;
margin-left:20px;
}

/* MAIN CONTAINER */

.container{
width:90%;
max-width:1000px;
margin:40px auto;
text-align:center;
}

/* TITLE */

h2{
margin-bottom:30px;
color:#fff;
}

/* DASHBOARD CARDS */

.cards{
display:flex;
justify-content:center;
gap:30px;
flex-wrap:wrap;
}

.card{
background:rgba(255,255,255,0.1);
backdrop-filter:blur(10px);
padding:30px;
width:220px;
border-radius:12px;
box-shadow:0 5px 15px rgba(0,0,0,0.5);
transition:0.3s;
}

.card:hover{
transform:translateY(-5px);
}

.card h3{
margin-bottom:15px;
}

.card a{
display:inline-block;
padding:10px 18px;
background:#007BFF;
color:white;
border-radius:6px;
text-decoration:none;
font-size:14px;
}

.card a:hover{
background:#0056b3;
}

/* LOGOUT BUTTON */

.logout{
margin-top:40px;
display:inline-block;
padding:10px 20px;
background:#dc3545;
color:white;
text-decoration:none;
border-radius:6px;
}

.logout:hover{
background:#a71d2a;
}

/* FOOTER */

.footer{
margin-top:205px;
text-align:center;
padding:15px;
background:#111;
font-size:14px;
color:#aaa;
}

</style>

</head>

<body>

<!-- NAVBAR -->

<div class="navbar">

<div class="logo">Cop Friendly e-Seva Admin</div>

<div>
Welcome Admin |
<a href="logout.php">Logout</a>
</div>

</div>

<!-- MAIN -->

<div class="container">

<h2>Admin Control Panel</h2>

<div class="cards">

<div class="card">
<h3>👥 Users</h3>
<p>View registered citizens and police users.</p>
<br>
<a href="view_users.php">View Users</a>
</div>

<div class="card">
<h3>📋 Complaints</h3>
<p>Check all submitted complaints.</p>
<br>
<a href="view_complaints.php">View Complaints</a>
</div>

</div>

<a href="logout.php" class="logout">🚪 Logout</a>

</div>

<div class="footer">
© 2026 Cop Friendly e-Seva | Admin Panel
</div>

</body>
</html>