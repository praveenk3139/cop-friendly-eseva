<?php
session_start();
include "db.php";

if(!isset($_SESSION['temp_user'])){
    header("Location: login.html");
    exit();
}

$otp_error = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){

    $entered_otp = trim($_POST['otp'] ?? '');
    $email       = $_SESSION['temp_user']['email'];

    $stmt = $conn->prepare("SELECT otp, otp_expiry FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user   = $result->fetch_assoc();

    if($entered_otp == $user['otp']){
        if(strtotime($user['otp_expiry']) >= time()){
            $_SESSION['user_id'] = $_SESSION['temp_user']['user_id'];
            $_SESSION['name']    = $_SESSION['temp_user']['name'];
            $_SESSION['role']    = $_SESSION['temp_user']['role'];
            unset($_SESSION['temp_user']);
            header("Location: dashboard.php");
            exit();
        } else {
            $otp_error = "Your OTP has expired. Please log in again to receive a new one.";
        }
    } else {
        $otp_error = "Invalid OTP. Please check your email and try again.";
    }
}

$masked_email = "";
if(isset($_SESSION['temp_user']['email'])){
    $e = $_SESSION['temp_user']['email'];
    $parts = explode("@", $e);
    $masked_email = substr($parts[0], 0, 2) . str_repeat("*", max(0, strlen($parts[0])-2)) . "@" . $parts[1];
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
:root {
  --navy:    #020b18;
  --blue:    #0ea5e9;
  --blue-glow: rgba(14,165,233,0.18);
  --text:    #e2e8f0;
  --muted:   #64748b;
  --border:  rgba(14,165,233,0.15);
  --red:     #ef4444;
  --green:   #10b981;
}
*, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
body {
  background: var(--navy);
  color: var(--text);
  font-family: 'Sora', sans-serif;
  min-height: 100vh;
  display: flex; flex-direction: column; align-items: center; justify-content: center;
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
.bg-glow {
  position: fixed; border-radius: 50%;
  filter: blur(140px); pointer-events: none;
}
.glow-1 { width:500px; height:500px; background:rgba(14,165,233,0.07); top:-100px; left:-100px; }

.card {
  position: relative; z-index: 1;
  background: rgba(4,18,37,0.92);
  border: 1px solid var(--border);
  border-radius: 20px;
  padding: 52px 48px;
  max-width: 420px; width: 100%;
  text-align: center;
  box-shadow: 0 24px 64px rgba(0,0,0,0.5);
  animation: fadeUp 0.5s ease both;
  overflow: hidden;
}
.card::before {
  content: '';
  position: absolute; top: 0; left: 0; right: 0;
  height: 2px;
  background: linear-gradient(90deg, transparent, var(--blue), transparent);
}

.otp-shield {
  width: 76px; height: 76px;
  background: var(--blue-glow);
  border: 2px solid var(--border);
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 32px;
  margin: 0 auto 28px;
  animation: shieldPulse 3s ease-in-out infinite;
}

@keyframes shieldPulse {
  0%,100% { box-shadow: 0 0 0 0 rgba(14,165,233,0.2); }
  50%      { box-shadow: 0 0 0 14px rgba(14,165,233,0); }
}

h1 {
  font-family: 'Rajdhani', sans-serif;
  font-size: 28px; font-weight: 700;
  color: white; letter-spacing: 0.4px;
  margin-bottom: 8px;
}

.subtitle {
  font-size: 13.5px; color: var(--muted);
  line-height: 1.65; margin-bottom: 8px;
}

.email-chip {
  display: inline-flex; align-items: center; gap: 6px;
  background: var(--blue-glow); border: 1px solid var(--border);
  border-radius: 20px; padding: 5px 14px;
  font-size: 13px; color: var(--blue); font-weight: 500;
  margin-bottom: 32px;
}

.error-msg {
  background: rgba(239,68,68,0.1);
  border: 1px solid rgba(239,68,68,0.25);
  border-radius: 9px; padding: 12px 16px;
  font-size: 13px; color: #fca5a5;
  margin-bottom: 20px;
  text-align: left;
  display: flex; align-items: flex-start; gap: 8px;
}

/* OTP input */
.otp-inputs {
  display: flex; gap: 10px; justify-content: center;
  margin-bottom: 28px;
}

.otp-digit {
  width: 52px; height: 58px;
  background: rgba(255,255,255,0.05);
  border: 1.5px solid rgba(255,255,255,0.12);
  border-radius: 10px;
  color: white;
  font-family: 'Rajdhani', sans-serif;
  font-size: 26px; font-weight: 700;
  text-align: center;
  outline: none;
  transition: all 0.2s;
  caret-color: var(--blue);
}

.otp-digit:focus {
  border-color: var(--blue);
  background: rgba(14,165,233,0.07);
  box-shadow: 0 0 0 3px rgba(14,165,233,0.12);
}

/* Hidden combined input for form submission */
#otpHidden { display: none; }

.submit-btn {
  width: 100%;
  padding: 13px;
  background: linear-gradient(135deg, var(--blue), #0284c7);
  color: white; border: none; border-radius: 10px;
  font-family: 'Rajdhani', sans-serif;
  font-size: 16px; font-weight: 700; letter-spacing: 0.8px;
  text-transform: uppercase; cursor: pointer;
  transition: all 0.2s;
  box-shadow: 0 0 20px rgba(14,165,233,0.25);
  margin-bottom: 20px;
}
.submit-btn:hover { transform:translateY(-2px); box-shadow:0 0 32px rgba(14,165,233,0.45); }
.submit-btn:active { transform:translateY(0); }

.back-link {
  font-size: 13px; color: var(--muted);
}
.back-link a { color: var(--blue); text-decoration: none; font-weight: 500; }
.back-link a:hover { text-decoration: underline; }

.timer-wrap {
  font-size: 13px; color: var(--muted); margin-bottom: 22px;
}
#timerDisplay { color: var(--blue); font-weight: 600; }

@keyframes fadeUp {
  from { opacity:0; transform:translateY(20px); }
  to   { opacity:1; transform:translateY(0); }
}
</style>
</head>
<body>
<div class="bg-glow glow-1"></div>

<div class="card">
  <div class="otp-shield">🔐</div>
  <h1>Verify OTP</h1>
  <p class="subtitle">We've sent a 6-digit verification code to</p>
  <div class="email-chip">✉️ <?php echo htmlspecialchars($masked_email, ENT_QUOTES, 'UTF-8'); ?></div>

  <?php if($otp_error){ ?>
  <div class="error-msg">
    <span>⚠️</span>
    <span><?php echo htmlspecialchars($otp_error, ENT_QUOTES, 'UTF-8'); ?></span>
  </div>
  <?php } ?>

  <form method="POST" onsubmit="combineOTP()">
    <div class="otp-inputs">
      <input class="otp-digit" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" id="d1" autofocus>
      <input class="otp-digit" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" id="d2">
      <input class="otp-digit" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" id="d3">
      <input class="otp-digit" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" id="d4">
      <input class="otp-digit" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" id="d5">
      <input class="otp-digit" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" id="d6">
    </div>
    <input type="hidden" name="otp" id="otpHidden">
    <div class="timer-wrap">Code expires in <span id="timerDisplay">10:00</span></div>
    <button type="submit" class="submit-btn">Verify &amp; Login →</button>
  </form>

  <div class="back-link">
    Wrong email? <a href="login.html">Go back to login</a>
  </div>
</div>

<script>
// Auto-advance OTP inputs
const digits = ['d1','d2','d3','d4','d5','d6'];
digits.forEach((id, i) => {
  const el = document.getElementById(id);

  el.addEventListener('input', function(){
    this.value = this.value.replace(/[^0-9]/g,'');
    if(this.value && i < digits.length - 1){
      document.getElementById(digits[i+1]).focus();
    }
  });

  el.addEventListener('keydown', function(e){
    if(e.key === 'Backspace' && !this.value && i > 0){
      document.getElementById(digits[i-1]).focus();
    }
  });

  el.addEventListener('paste', function(e){
    const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g,'');
    if(pasted.length >= 6){
      e.preventDefault();
      digits.forEach((did, j) => {
        document.getElementById(did).value = pasted[j] || '';
      });
      document.getElementById(digits[5]).focus();
    }
  });
});

function combineOTP(){
  const otp = digits.map(id => document.getElementById(id).value).join('');
  document.getElementById('otpHidden').value = otp;
}

// Countdown timer (10 minutes)
let timeLeft = 600;
const timerEl = document.getElementById('timerDisplay');
const interval = setInterval(function(){
  timeLeft--;
  const m = Math.floor(timeLeft / 60);
  const s = timeLeft % 60;
  timerEl.textContent = m + ':' + String(s).padStart(2,'0');
  if(timeLeft <= 60) timerEl.style.color = '#ef4444';
  if(timeLeft <= 0){
    clearInterval(interval);
    timerEl.textContent = 'Expired';
    document.querySelector('.submit-btn').disabled = true;
    document.querySelector('.submit-btn').style.opacity = '0.5';
  }
}, 1000);
</script>
</body>
</html>