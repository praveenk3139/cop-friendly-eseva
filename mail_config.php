<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ✅ FIXED PATH (VERY IMPORTANT)
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';
require __DIR__ . '/PHPMailer/src/Exception.php';

function sendMail($to, $subject, $message) {

    $mail = new PHPMailer(true);

    try {
        // ✅ SMTP CONFIG
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'cop.friendly.eseva@gmail.com';     // 🔁 replace
        $mail->Password   = 'ijisfizlxbogfztw';        // 🔁 replace
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // ✅ EMAIL SETTINGS
        $mail->setFrom('cop.friendly.eseva@gmail.com', 'Cop Friendly e-Seva');
        $mail->addAddress($to);

        // ✅ CONTENT
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;

        // ✅ SEND
        $mail->send();
        return true;

    } catch (Exception $e) {
        // 🔍 SHOW ERROR FOR DEBUG
        echo "Mailer Error: " . $mail->ErrorInfo;
        return false;
    }
}
?>