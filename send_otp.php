<?php
include "db.php";
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;

$email = $_POST['email'];

// Generate OTP
$otp = rand(100000, 999999);
$expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));

// Save OTP
mysqli_query($conn, "UPDATE users SET otp='$otp', otp_expiry='$expiry' WHERE email='$email'");

// Send Mail
$mail = new PHPMailer(true);

$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'cop.friendly.eseva@gmail.com';
$mail->Password = 'ijisfizlxbogfztw';
$mail->SMTPSecure = 'tls';
$mail->Port = 587;

$mail->setFrom('cop.friendly.eseva@gmail.com', 'e-Seva');
$mail->addAddress($email);

$mail->isHTML(true);
$mail->Subject = "Your OTP Code";
$mail->Body = "<h3>Your OTP is: <b>$otp</b></h3><p>Valid for 5 minutes</p>";

$mail->send();

header("Location: verify_otp.php?email=$email");
?>