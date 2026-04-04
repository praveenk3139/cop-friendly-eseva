<?php
include "db.php";

$message = "";
$login_link = false;

if($_SERVER["REQUEST_METHOD"] == "POST"){

$name = mysqli_real_escape_string($conn, $_POST['name']);
$email = mysqli_real_escape_string($conn, $_POST['email']);
$mobile = mysqli_real_escape_string($conn, $_POST['mobile']);
$password = $_POST['password'];
$role = $_POST['role'];

/* Check if email already exists */
$check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");

if(mysqli_num_rows($check) > 0){

$message = "❌ Email already registered! Try another email.";

}else{

/* Hash password */
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

/* Insert user */
$sql = "INSERT INTO users (name, email, mobile, password, role)
VALUES ('$name', '$email', '$mobile', '$hashed_password', '$role')";

if(mysqli_query($conn, $sql)){

$message = "✅ Registration Successful!";
$login_link = true;

}else{

$message = "❌ Registration Failed!";

}

}

}
?>

<!DOCTYPE html>
<html>
<head>
<title>Register - Cop Friendly e-Seva</title>

<style>

body {
font-family: Arial, sans-serif;
background:linear-gradient(120deg,#0f2027,#203a43,#2c5364);
color: white;
display: flex;
justify-content: center;
align-items: center;
height: 100vh;
margin: 0;
}

.container {
background:rgba(255,255,255,0.1);
backdrop-filter:blur(10px);
padding: 35px;
border-radius: 12px;
box-shadow: 0 4px 15px rgba(0,0,0,0.5);
width: 400px;
text-align: center;
}

h2 {
color: #facc15;
margin-bottom: 20px;
}

a {
color: #00c6ff;
text-decoration: none;
font-weight: bold;
}

a:hover {
text-decoration: underline;
}

.message {
margin-top: 20px;
font-size: 16px;
}

.success{
color:#22c55e;
}

.error{
color:#ef4444;
}

</style>

</head>

<body>

<div class="container">

<h2>Register Status</h2>

<div class="message">

<?php

if($message != ""){

if($login_link){
echo "<span class='success'>$message</span>";
echo "<br><br>";
echo "<a href='login.html'>👉 Login Now</a>";
}else{
echo "<span class='error'>$message</span>";
echo "<br><br>";
echo "<a href='register.html'>🔄 Try Again</a>";
}

}else{

echo "<span class='error'>Invalid Request</span>";

}

?>

</div>

</div>

</body>
</html>