<?php
session_start();
include "db.php";
include "mail_config.php";

/* ── Only accept POST ── */
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: forgot_password.html");
    exit();
}

/* ── Validate email ── */
$email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) ?? '');

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: forgot_password.html?status=invalid");
    exit();
}

/* ── Check if user exists ── */
$stmt = mysqli_prepare($conn, "SELECT user_id FROM users WHERE email = ? LIMIT 1");
if (!$stmt) {
    error_log("DB prepare error: " . mysqli_error($conn));
    header("Location: forgot_password.html?status=error");
    exit();
}

mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user   = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

/* ── Only proceed if user found (but always show generic response) ── */
if ($user) {
    /* Generate secure token */
    try {
        $token = bin2hex(random_bytes(32));
    } catch (Exception $e) {
        error_log("Token generation failed: " . $e->getMessage());
        header("Location: forgot_password.html?status=error");
        exit();
    }

    $expiry = date("Y-m-d H:i:s", time() + 3600); // 1 hour

    /* Store token */
    $upd = mysqli_prepare($conn, "
        UPDATE users
        SET token = ?, token_expiry = ?
        WHERE user_id = ?
    ");

    if (!$upd) {
        error_log("DB prepare error (update): " . mysqli_error($conn));
        header("Location: forgot_password.html?status=error");
        exit();
    }

    mysqli_stmt_bind_param($upd, "ssi", $token, $expiry, $user['user_id']);
    mysqli_stmt_execute($upd);
    mysqli_stmt_close($upd);

    /* Build reset link */
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    $path = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
    $link = $protocol . $host . $path . "/reset_password.php?token=" . urlencode($token);

    /* Email content */
    $subject = "Reset Your e-Seva Password";
    $message = "
    <!DOCTYPE html>
    <html>
    <head><meta charset='UTF-8'></head>
    <body style='margin:0;padding:0;background:#080a10;font-family:Arial,sans-serif;'>
      <div style='max-width:520px;margin:40px auto;background:#111520;border:1px solid #1c2133;border-radius:14px;overflow:hidden;'>
        <div style='background:linear-gradient(135deg,#4f6ef7,#7c3aed);padding:32px 36px;text-align:center;'>
          <div style='font-size:2rem;margin-bottom:8px;'>🚔</div>
          <h1 style='margin:0;color:#fff;font-size:1.3rem;font-weight:700;'>e-Seva Password Reset</h1>
          <p style='color:rgba(255,255,255,0.7);font-size:0.85rem;margin-top:6px;'>Cop Friendly Police Portal</p>
        </div>
        <div style='padding:36px;'>
          <p style='color:#a8adc4;font-size:0.9rem;line-height:1.7;margin:0 0 24px;'>
            Hello,<br><br>
            We received a request to reset the password for your e-Seva account associated with this email address.
          </p>
          <div style='text-align:center;margin:28px 0;'>
            <a href='{$link}'
               style='display:inline-block;padding:14px 32px;background:linear-gradient(135deg,#4f6ef7,#3b55e0);color:#fff;text-decoration:none;border-radius:10px;font-size:0.95rem;font-weight:600;box-shadow:0 4px 18px rgba(79,110,247,0.4);'>
               Reset My Password →
            </a>
          </div>
          <p style='color:#4e5472;font-size:0.78rem;text-align:center;margin:0 0 16px;'>
            This link expires in <strong style='color:#a8adc4;'>1 hour</strong>.
          </p>
          <div style='background:#0e1119;border:1px solid #1c2133;border-radius:8px;padding:12px 16px;margin-top:20px;'>
            <p style='color:#4e5472;font-size:0.75rem;margin:0;word-break:break-all;'>
              If the button doesn't work, copy this link:<br>
              <a href='{$link}' style='color:#4f6ef7;'>{$link}</a>
            </p>
          </div>
          <p style='color:#4e5472;font-size:0.78rem;margin-top:24px;line-height:1.6;'>
            If you didn't request this, you can safely ignore this email. Your password will not change.
          </p>
        </div>
        <div style='background:#0e1119;border-top:1px solid #1c2133;padding:16px 36px;text-align:center;'>
          <p style='color:#4e5472;font-size:0.72rem;margin:0;'>© 2026 Cop Friendly e-Seva · Smart Policing System</p>
        </div>
      </div>
    </body>
    </html>
    ";

    /* Send email */
    sendMail($email, $subject, $message);
}

/* Always redirect with generic success (prevents user enumeration) */
header("Location: forgot_password.html?status=sent");
exit();
?>