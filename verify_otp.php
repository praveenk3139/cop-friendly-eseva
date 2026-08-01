<?php
session_start();
include "db.php";

// Check temporary login session
if(!isset($_SESSION['temp_user'])){
    header("Location: login.html");
    exit();
}

$otp_error = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){

    $entered_otp = trim($_POST['otp'] ?? '');
    $email       = $_SESSION['temp_user']['email'];

    // Get OTP from database
    $stmt = $conn->prepare("SELECT otp, otp_expiry FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0){

        $user = $result->fetch_assoc();

        // Check OTP
        if($entered_otp === $user['otp']){

            // Check expiry
            if(strtotime($user['otp_expiry']) >= time()){

                // Login success
                $_SESSION['user_id'] = $_SESSION['temp_user']['user_id'];
                $_SESSION['name']    = $_SESSION['temp_user']['name'];
                $_SESSION['role']    = $_SESSION['temp_user']['role'];

                // Clear OTP after successful login
                $clear = $conn->prepare("UPDATE users SET otp=NULL, otp_expiry=NULL WHERE email=?");
                $clear->bind_param("s", $email);
                $clear->execute();

                // Remove temporary session
                unset($_SESSION['temp_user']);

                header("Location: dashboard.php");
                exit();

            } else {
                $otp_error = "Your OTP has expired. Please log in again.";
            }

        } else {
            $otp_error = "Invalid OTP. Please try again.";
        }

    } else {
        $otp_error = "User not found.";
    }
}

// Mask email for display
$masked_email = "";
if(isset($_SESSION['temp_user']['email'])){
    $e = $_SESSION['temp_user']['email'];
    $parts = explode("@", $e);
    $masked_email = substr($parts[0], 0, 2) .
                    str_repeat("*", max(0, strlen($parts[0]) - 2)) .
                    "@" . $parts[1];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Verify OTP | Cop Friendly e-Seva</title>

<link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;600;700&family=Sora:wght@300;400;500;600&display=swap" rel="stylesheet">

<style>
:root{
  --navy:#020b18;
  --blue:#0ea5e9;
  --blue-glow:rgba(14,165,233,0.18);
  --text:#e2e8f0;
  --muted:#64748b;
  --border:rgba(14,165,233,0.15);
  --red:#ef4444;
}

*{
  margin:0;
  padding:0;
  box-sizing:border-box;
}

body{
  background:var(--navy);
  color:var(--text);
  font-family:'Sora',sans-serif;
  min-height:100vh;
  display:flex;
  justify-content:center;
  align-items:center;
  padding:20px;
}

body::before{
  content:'';
  position:fixed;
  inset:0;
  background-image:
    linear-gradient(rgba(14,165,233,0.04) 1px, transparent 1px),
    linear-gradient(90deg, rgba(14,165,233,0.04) 1px, transparent 1px);
  background-size:48px 48px;
  pointer-events:none;
}

.card{
  width:100%;
  max-width:430px;
  background:rgba(4,18,37,0.92);
  border:1px solid var(--border);
  border-radius:22px;
  padding:48px 40px;
  text-align:center;
  box-shadow:0 24px 64px rgba(0,0,0,0.55);
  position:relative;
  overflow:hidden;
}

.card::before{
  content:'';
  position:absolute;
  top:0;
  left:0;
  right:0;
  height:2px;
  background:linear-gradient(90deg, transparent, var(--blue), transparent);
}

.shield{
  width:78px;
  height:78px;
  border-radius:50%;
  background:var(--blue-glow);
  border:2px solid var(--border);
  display:flex;
  justify-content:center;
  align-items:center;
  margin:0 auto 26px;
  font-size:34px;
  animation:pulse 3s infinite;
}

@keyframes pulse{
  0%,100%{ box-shadow:0 0 0 0 rgba(14,165,233,0.2); }
  50%{ box-shadow:0 0 0 14px rgba(14,165,233,0); }
}

h1{
  font-family:'Rajdhani',sans-serif;
  font-size:30px;
  color:#fff;
  margin-bottom:10px;
}

.subtitle{
  font-size:14px;
  color:var(--muted);
  margin-bottom:10px;
  line-height:1.6;
}

.email-chip{
  display:inline-flex;
  align-items:center;
  gap:6px;
  background:var(--blue-glow);
  border:1px solid var(--border);
  color:var(--blue);
  padding:6px 14px;
  border-radius:20px;
  font-size:13px;
  margin-bottom:30px;
}

.error{
  background:rgba(239,68,68,0.1);
  border:1px solid rgba(239,68,68,0.25);
  color:#fca5a5;
  padding:12px 15px;
  border-radius:10px;
  font-size:13px;
  text-align:left;
  margin-bottom:20px;
}

.otp-inputs{
  display:flex;
  justify-content:center;
  gap:10px;
  margin-bottom:28px;
}

.otp{
  width:52px;
  height:58px;
  border-radius:12px;
  border:1.5px solid rgba(255,255,255,0.12);
  background:rgba(255,255,255,0.05);
  color:#fff;
  text-align:center;
  font-size:28px;
  font-family:'Rajdhani',sans-serif;
  font-weight:700;
  outline:none;
  transition:0.2s;
  caret-color:var(--blue);
}

.otp:focus{
  border-color:var(--blue);
  background:rgba(14,165,233,0.07);
  box-shadow:0 0 0 3px rgba(14,165,233,0.12);
}

#otpHidden{
  display:none;
}

.timer{
  color:var(--muted);
  font-size:13px;
  margin-bottom:22px;
}

#timerDisplay{
  color:var(--blue);
  font-weight:600;
}

.btn{
  width:100%;
  padding:14px;
  border:none;
  border-radius:12px;
  background:linear-gradient(135deg,var(--blue),#0284c7);
  color:#fff;
  font-family:'Rajdhani',sans-serif;
  font-size:16px;
  font-weight:700;
  letter-spacing:0.8px;
  cursor:pointer;
  box-shadow:0 0 24px rgba(14,165,233,0.25);
  transition:0.2s;
  margin-bottom:18px;
}

.btn:hover{
  transform:translateY(-2px);
  box-shadow:0 0 34px rgba(14,165,233,0.45);
}

.back{
  font-size:13px;
  color:var(--muted);
}

.back a{
  color:var(--blue);
  text-decoration:none;
}

.back a:hover{
  text-decoration:underline;
}

@media(max-width:480px){
  .card{
    padding:40px 24px;
  }

  .otp{
    width:46px;
    height:54px;
    font-size:24px;
  }
}
</style>
</head>

<body>

<div class="card">

  <div class="shield">🔐</div>

  <h1>Verify OTP</h1>

  <p class="subtitle">
    We've sent a 6-digit verification code to
  </p>

  <div class="email-chip">
    ✉️ <?php echo htmlspecialchars($masked_email, ENT_QUOTES, 'UTF-8'); ?>
  </div>

  <?php if($otp_error){ ?>
    <div class="error">
      ⚠️ <?php echo htmlspecialchars($otp_error, ENT_QUOTES, 'UTF-8'); ?>
    </div>
  <?php } ?>

  <form method="POST" onsubmit="combineOTP()">

    <div class="otp-inputs">
      <input class="otp" type="text" maxlength="1" inputmode="numeric" id="d1" autofocus>
      <input class="otp" type="text" maxlength="1" inputmode="numeric" id="d2">
      <input class="otp" type="text" maxlength="1" inputmode="numeric" id="d3">
      <input class="otp" type="text" maxlength="1" inputmode="numeric" id="d4">
      <input class="otp" type="text" maxlength="1" inputmode="numeric" id="d5">
      <input class="otp" type="text" maxlength="1" inputmode="numeric" id="d6">
    </div>

    <input type="hidden" name="otp" id="otpHidden">

    <div class="timer">
      Code expires in <span id="timerDisplay">10:00</span>
    </div>

    <button type="submit" class="btn">
      Verify & Login →
    </button>

  </form>

  <div class="back">
    Wrong email? <a href="login.html">Go back to login</a>
  </div>

</div>

<script>
const ids = ['d1','d2','d3','d4','d5','d6'];

ids.forEach((id, index) => {

  const input = document.getElementById(id);

  input.addEventListener('input', function(){

    this.value = this.value.replace(/[^0-9]/g,'');

    if(this.value && index < ids.length - 1){
      document.getElementById(ids[index + 1]).focus();
    }
  });

  input.addEventListener('keydown', function(e){

    if(e.key === 'Backspace' && !this.value && index > 0){
      document.getElementById(ids[index - 1]).focus();
    }
  });

  input.addEventListener('paste', function(e){

    const pasted = (e.clipboardData || window.clipboardData)
      .getData('text')
      .replace(/[^0-9]/g,'');

    if(pasted.length >= 6){

      e.preventDefault();

      ids.forEach((pid, i) => {
        document.getElementById(pid).value = pasted[i] || '';
      });

      document.getElementById(ids[5]).focus();
    }
  });
});

function combineOTP(){

  const otp = ids.map(id => document.getElementById(id).value).join('');
  document.getElementById('otpHidden').value = otp;
}

// 10 minute countdown
let timeLeft = 600;

const timerEl = document.getElementById('timerDisplay');

const interval = setInterval(() => {

  timeLeft--;

  const minutes = Math.floor(timeLeft / 60);
  const seconds = timeLeft % 60;

  timerEl.textContent =
    minutes + ':' + String(seconds).padStart(2,'0');

  if(timeLeft <= 60){
    timerEl.style.color = '#ef4444';
  }

  if(timeLeft <= 0){

    clearInterval(interval);

    timerEl.textContent = 'Expired';

    const btn = document.querySelector('.btn');
    btn.disabled = true;
    btn.style.opacity = '0.5';
  }

}, 1000);
</script>

</body>
</html>