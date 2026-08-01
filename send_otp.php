<?php
session_start();
include "db.php";

// PHPMailer files
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Only allow POST
if($_SERVER["REQUEST_METHOD"] != "POST"){
    header("Location: login.html");
    exit();
}

// Get email
$email = trim($_POST['email'] ?? '');

if(empty($email)){
    die("Email is required");
}

// Check user exists
$stmt = $conn->prepare("SELECT user_id, name, role FROM users WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0){
    die("Email not found in database");
}

$user = $result->fetch_assoc();

// Generate OTP
$otp = rand(100000, 999999);
$expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));

// Save OTP in database
$update = $conn->prepare("UPDATE users SET otp=?, otp_expiry=? WHERE email=?");
$update->bind_param("sss", $otp, $expiry, $email);

if(!$update->execute()){
    die("Failed to save OTP: " . $conn->error);
}

// Save temporary session
$_SESSION['temp_user'] = [
    'user_id' => $user['user_id'],
    'name'    => $user['name'],
    'role'    => $user['role'],
    'email'   => $email
];

// Create PHPMailer object
$mail = new PHPMailer(true);

try {

    // SMTP settings
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;

    // Your Gmail
    $mail->Username   = 'praveenk3139@gmail.com';

    // App Password (NO SPACES)
    $mail->Password   = 'vpmuqlbthdbcsczb';

    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Sender
    $mail->setFrom('praveenk3139@gmail.com', 'Cop Friendly e-Seva');

    // Receiver
    $mail->addAddress($email);

    // Email content
    $mail->isHTML(true);
    $mail->Subject = 'Your OTP Code';

    $mail->Body = "
    <div style='font-family:Arial,sans-serif;padding:20px'>
        <h2 style='color:#0ea5e9'>Cop Friendly e-Seva</h2>
        <p>Hello,</p>
        <p>Your OTP verification code is:</p>

        <div style='font-size:32px;font-weight:bold;color:#111;
                    background:#f3f4f6;padding:15px 25px;
                    display:inline-block;border-radius:10px'>
            $otp
        </div>

        <p style='margin-top:20px'>
            This OTP is valid for <b>5 minutes</b>.
        </p>

        <p>Do not share this OTP with anyone.</p>

        <hr>
        <p style='font-size:12px;color:#666'>
            Cop Friendly e-Seva System
        </p>
    </div>
    ";

    // Send email
    $mail->send();

    // Redirect to OTP verification page
    header("Location: verify_otp.php");
    exit();

} catch (Exception $e) {

    echo "<h3>OTP sending failed</h3>";
    echo "<b>Mailer Error:</b> " . htmlspecialchars($mail->ErrorInfo);
}
?>