<?php
include "db.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

if($_SERVER["REQUEST_METHOD"] != "POST"){
    header("Location: register.html");
    exit();
}

$message    = "";
$login_link = false;
$is_success = false;

$name     = isset($_POST['name'])     ? mysqli_real_escape_string($conn, $_POST['name'])     : '';
$email    = isset($_POST['email'])    ? mysqli_real_escape_string($conn, $_POST['email'])    : '';
$mobile   = isset($_POST['mobile'])   ? mysqli_real_escape_string($conn, $_POST['mobile'])   : '';
$role     = isset($_POST['role'])     ? mysqli_real_escape_string($conn, $_POST['role'])     : '';
$password = $_POST['password'] ?? '';

if(empty($name) || empty($email) || empty($mobile) || empty($password) || empty($role)){
    $message = "All fields are required. Please fill in every field to continue.";
} else {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");

    if(mysqli_num_rows($check) > 0){
        $message = "This email is already registered. Please use a different email or sign in.";
    } else {
        $sql = "INSERT INTO users (name, email, mobile, password, role)
                VALUES ('$name', '$email', '$mobile', '$hashed_password', '$role')";
        if(mysqli_query($conn, $sql)){
            $message    = "Account created successfully! You can now log in to access your dashboard.";
            $login_link = true;
            $is_success = true;
        } else {
            $message = "Registration failed: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Registration Status | Cop Friendly e-Seva</title>
<link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;600;700&family=Sora:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
:root {
  --navy:    #020b18;
  --blue:    #0ea5e9;
  --green:   #10b981;
  --red:     #ef4444;
  --text:    #e2e8f0;
  --muted:   #64748b;
  --border:  rgba(14,165,233,0.15);
}
*, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
body {
  background: var(--navy);
  color: var(--text);
  font-family: 'Sora', sans-serif;
  min-height: 100vh;
  display: flex; flex-direction: column;
  align-items: center; justify-content: center;
  padding: 30px 20px;
}
body::before {
  content: '';
  position: fixed; inset: 0;
  background-image:
    linear-gradient(rgba(14,165,233,0.04) 1px, transparent 1px),
    linear-gradient(90deg, rgba(14,165,233,0.04) 1px, transparent 1px);
  background-size: 48px 48px;
  pointer-events: none;
}
.card {
  position: relative; z-index: 1;
  background: rgba(4,18,37,0.92);
  border: 1px solid var(--border);
  border-radius: 20px;
  padding: 52px 48px;
  max-width: 440px;
  width: 100%;
  text-align: center;
  box-shadow: 0 24px 64px rgba(0,0,0,0.5);
  animation: pop 0.4s cubic-bezier(0.34,1.56,0.64,1) both;
}
@keyframes pop {
  from { opacity:0; transform:scale(0.92) translateY(16px); }
  to   { opacity:1; transform:scale(1) translateY(0); }
}
.status-icon {
  width: 72px; height: 72px;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 30px;
  margin: 0 auto 24px;
}
.icon-success { background: rgba(16,185,129,0.15); border: 2px solid rgba(16,185,129,0.35); }
.icon-error   { background: rgba(239,68,68,0.12);  border: 2px solid rgba(239,68,68,0.3); }

.card h2 {
  font-family: 'Rajdhani', sans-serif;
  font-size: 26px; font-weight: 700;
  letter-spacing: 0.4px; margin-bottom: 12px;
}
.h-success { color: #34d399; }
.h-error   { color: #f87171; }

.card p {
  font-size: 14px; color: var(--muted); line-height: 1.7; margin-bottom: 32px;
}

.btn {
  display: inline-flex; align-items: center; gap: 8px;
  padding: 12px 28px;
  border-radius: 10px; text-decoration: none;
  font-family: 'Rajdhani', sans-serif;
  font-size: 15px; font-weight: 700;
  letter-spacing: 0.5px; text-transform: uppercase;
  transition: all 0.2s;
  margin: 4px;
}
.btn-green {
  background: linear-gradient(135deg,#10b981,#059669);
  color: white; box-shadow: 0 0 20px rgba(16,185,129,0.25);
}
.btn-green:hover { transform:translateY(-2px); box-shadow:0 0 30px rgba(16,185,129,0.4); }
.btn-outline {
  background: rgba(255,255,255,0.04); color: var(--text);
  border: 1px solid rgba(255,255,255,0.12);
}
.btn-outline:hover { background: rgba(255,255,255,0.08); transform:translateY(-2px); }
.btn-red {
  background: linear-gradient(135deg,#ef4444,#dc2626);
  color: white; box-shadow: 0 0 20px rgba(239,68,68,0.2);
}
.btn-red:hover { transform:translateY(-2px); box-shadow:0 0 30px rgba(239,68,68,0.35); }
</style>
</head>
<body>
<div class="card">
  <?php if($is_success){ ?>
    <div class="status-icon icon-success">✅</div>
    <h2 class="h-success">Registration Successful</h2>
    <p><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
    <a href="login.html" class="btn btn-green">Sign In Now →</a>
    <a href="index.html" class="btn btn-outline">Back to Home</a>
  <?php } else { ?>
    <div class="status-icon icon-error">❌</div>
    <h2 class="h-error">Registration Failed</h2>
    <p><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
    <a href="register.html" class="btn btn-red">Try Again</a>
    <a href="index.html" class="btn btn-outline">Back to Home</a>
  <?php } ?>
</div>
</body>
</html>