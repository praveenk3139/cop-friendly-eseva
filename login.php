<?php
session_start();
include "db.php";
include "mail_config.php";

if($_SERVER["REQUEST_METHOD"] == "POST"){

    // ✅ Validate input
    if(empty($_POST['email']) || empty($_POST['password'])){
        echo "<script>alert('Please fill all fields'); window.location.href='login.html';</script>";
        exit();
    }

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // ✅ Prepared statement (secure)
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result && $result->num_rows === 1){

        $user = $result->fetch_assoc();

        // ✅ Verify password
        if(password_verify($password, $user['password'])){

            // ✅ Generate OTP
            $otp = rand(100000, 999999);
            $expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));

            // ✅ Store OTP in DB
            $update = $conn->prepare("UPDATE users SET otp=?, otp_expiry=? WHERE email=?");
            $update->bind_param("sss", $otp, $expiry, $email);
            $update->execute();

            // ✅ Store temporary session
            $_SESSION['temp_user'] = [
                'user_id' => $user['user_id'],
                'name'    => $user['name'],
                'role'    => $user['role'],
                'email'   => $user['email']
            ];

            // ✅ Send OTP email
            $subject = "Your OTP - Cop Friendly e-Seva";

            $message = "
            <h3>OTP Verification</h3>
            <p>Hello ".$user['name'].",</p>
            <p>Your OTP is: <b>$otp</b></p>
            <p>This OTP is valid for 5 minutes.</p>
            <br>
            <p>If you did not request this, ignore this email.</p>
            ";

            if(!sendMail($user['email'], $subject, $message)){
                error_log("OTP Mail failed for ".$user['email']);
            }

            header("Location: verify_otp.php");
            exit();

        } else {
            echo "<script>alert('Incorrect Password'); window.location.href='login.html';</script>";
        }

    } else {
        echo "<script>alert('Email not registered'); window.location.href='login.html';</script>";
    }

    $stmt->close();
    $conn->close();

} else {
    header("Location: login.html");
    exit();
}
?>