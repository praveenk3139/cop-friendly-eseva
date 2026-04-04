<!DOCTYPE html>
<html>
<head>
<title>Admin Login - Cop Friendly e-Seva</title>

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

/* HEADER */

.header{
display:flex;
justify-content:space-between;
align-items:center;
padding:15px 60px;
background:#111;
box-shadow:0 4px 10px rgba(0,0,0,0.5);
}

.logo{
font-size:22px;
font-weight:bold;
color:#fff;
}

.nav a{
color:white;
text-decoration:none;
margin-left:25px;
font-size:16px;
transition:0.3s;
}

.nav a:hover{
color:#0ca678;
}

/* MAIN */

.main{
height:85vh;
display:flex;
justify-content:center;
align-items:center;
}

/* LOGIN BOX */

.container{
background:rgba(255,255,255,0.12);
backdrop-filter:blur(10px);
padding:45px;
border-radius:15px;
box-shadow:0 10px 30px rgba(0,0,0,0.6);
text-align:center;
width:350px;
}

.container h2{
margin-bottom:25px;
}

/* INPUT */

.input-box{
position:relative;
margin:10px 0;
}

.input-box input{
width:100%;
padding:11px;
border-radius:6px;
border:none;
outline:none;
font-size:14px;
}

/* PASSWORD ICON */

.eye{
position:absolute;
right:12px;
top:50%;
transform:translateY(-50%);
cursor:pointer;
color:black;
}

/* BUTTON */

.btn{
margin-top:15px;
padding:12px;
background:linear-gradient(to right,#28a745,#5cd65c);
color:white;
border:none;
border-radius:25px;
font-size:16px;
cursor:pointer;
transition:0.3s;
width:100%;
}

.btn:hover{
background:linear-gradient(to right,#1e7e34,#3fbf3f);
transform:scale(1.05);
}

/* BACK LINK */

.back{
display:block;
margin-top:15px;
text-decoration:none;
color:#00c6ff;
}

.back:hover{
text-decoration:underline;
}

/* FOOTER */

.footer{
background:#111;
padding:15px;
text-align:center;
font-size:14px;
color:#bbb;
}

</style>

</head>

<body>

<!-- HEADER -->

<div class="header">

<div class="logo">Cop Friendly e-Seva</div>

<div class="nav">
<a href="index.html">Home</a>
<a href="register.html">Register</a>
<a href="login.html">User Login</a>
<a href="admin_login.php">Admin</a>
</div>

</div>


<!-- MAIN -->

<div class="main">

<div class="container">

<h2>Police Admin Login</h2>

<form action="admin_check.php" method="POST">

<div class="input-box">
<input type="text" name="username" placeholder="Admin Username" required>
</div>

<div class="input-box">
<input type="password" name="password" id="password" placeholder="Password" required>
<span class="eye" onclick="togglePassword()">👁</span>
</div>

<button type="submit" class="btn">Login</button>

</form>

<a href="index.html" class="back">⬅ Back to Home</a>

</div>

</div>


<!-- FOOTER -->

<div class="footer">
© 2026 Cop Friendly e-Seva | Police Administration Portal
</div>


<script>

function togglePassword(){

var x=document.getElementById("password");

if(x.type==="password"){
x.type="text";
}else{
x.type="password";
}

}

</script>

</body>
</html>