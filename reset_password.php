<?php
session_start();
include "db.php";

/* ── Helper: show a styled error page ── */
function showError(string $heading, string $body): void {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Link Invalid — e-Seva</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
    <style>
    :root{--bg:#080a10;--card:#111520;--border:#1c2133;--accent:#4f6ef7;--danger:#ef4444;--text:#e8eaf2;--muted:#4e5472;}
    *{margin:0;padding:0;box-sizing:border-box;}
    body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:grid;place-items:center;padding:20px;}
    body::before{content:'';position:fixed;inset:0;background-image:radial-gradient(rgba(79,110,247,0.08) 1px,transparent 1px);background-size:28px 28px;pointer-events:none;}
    .card{position:relative;z-index:1;background:var(--card);border:1px solid var(--border);border-radius:16px;padding:44px 40px;max-width:420px;width:100%;text-align:center;}
    .icon{font-size:3rem;margin-bottom:20px;}
    h2{font-family:'Syne',sans-serif;font-size:1.6rem;font-weight:800;margin-bottom:10px;letter-spacing:-0.03em;}
    p{color:var(--muted);font-size:0.88rem;line-height:1.7;margin-bottom:24px;}
    a{display:inline-flex;align-items:center;gap:6px;padding:11px 22px;background:var(--accent);color:#fff;border-radius:10px;text-decoration:none;font-size:0.85rem;font-weight:600;transition:opacity 0.2s;}
    a:hover{opacity:0.85;}
    </style>
    </head>
    <body>
    <div class="card">
        <div class="icon">⛔</div>
        <h2><?php echo htmlspecialchars($heading); ?></h2>
        <p><?php echo htmlspecialchars($body); ?></p>
        <a href="forgot_password.html">Request a new link →</a>
    </div>
    </body>
    </html>
    <?php
    exit();
}

/* ═══════════════════════════════════
   STEP 2 — HANDLE FORM SUBMISSION
═══════════════════════════════════ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $token   = trim($_POST['token']            ?? '');
    $newPass = trim($_POST['new_password']     ?? '');
    $confirm = trim($_POST['confirm_password'] ?? '');

    /* Basic checks */
    if (empty($token) || empty($newPass) || empty($confirm)) {
        header("Location: reset_password.php?token=" . urlencode($token) . "&err=missing");
        exit();
    }

    if (strlen($newPass) < 8) {
        header("Location: reset_password.php?token=" . urlencode($token) . "&err=short");
        exit();
    }

    if ($newPass !== $confirm) {
        header("Location: reset_password.php?token=" . urlencode($token) . "&err=mismatch");
        exit();
    }

    /* Strength: at least one number */
    if (!preg_match('/[0-9]/', $newPass)) {
        header("Location: reset_password.php?token=" . urlencode($token) . "&err=weak");
        exit();
    }

    /* Validate token */
    $stmt = mysqli_prepare($conn, "
        SELECT user_id
        FROM users
        WHERE token = ?
        AND token_expiry > NOW()
        LIMIT 1
    ");

    if (!$stmt) { showError("Database error", mysqli_error($conn)); }

    mysqli_stmt_bind_param($stmt, "s", $token);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user   = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$user) {
        showError("Link Expired or Invalid", "This password reset link has expired or has already been used. Please request a new one.");
    }

    /* Update password, clear token */
    $hashed = password_hash($newPass, PASSWORD_DEFAULT);

    $upd = mysqli_prepare($conn, "
        UPDATE users
        SET password = ?, token = NULL, token_expiry = NULL
        WHERE user_id = ?
    ");

    if (!$upd) { showError("Database error", mysqli_error($conn)); }

    mysqli_stmt_bind_param($upd, "si", $hashed, $user['user_id']);
    mysqli_stmt_execute($upd);
    mysqli_stmt_close($upd);

    /* Redirect with success flag */
    header("Location: login.html?reset=success");
    exit();
}

/* ═══════════════════════════════════
   STEP 1 — SHOW THE RESET FORM
═══════════════════════════════════ */
if (empty($_GET['token'])) {
    header("Location: forgot_password.html");
    exit();
}

$token = trim($_GET['token']);

/* Validate token before showing form */
$stmt = mysqli_prepare($conn, "
    SELECT user_id
    FROM users
    WHERE token = ?
    AND token_expiry > NOW()
    LIMIT 1
");

if (!$stmt) { showError("Database error", mysqli_error($conn)); }

mysqli_stmt_bind_param($stmt, "s", $token);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$valid  = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$valid) {
    showError("Link Expired or Invalid", "This password reset link has expired or has already been used. Please request a new one.");
}

$err = $_GET['err'] ?? '';

$errMessages = [
    'mismatch' => 'Passwords do not match. Please try again.',
    'short'    => 'Password must be at least 8 characters long.',
    'weak'     => 'Password must contain at least one number.',
    'missing'  => 'Please fill in all fields.',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reset Password — e-Seva</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
:root {
    --bg:#080a10; --surface:#0e1119; --card:#111520;
    --border:#1c2133; --border2:#252d44;
    --accent:#4f6ef7; --accent2:#7c3aed;
    --emerald:#10b981; --amber:#f59e0b;
    --text:#e8eaf2; --text2:#a8adc4; --muted:#4e5472;
    --danger:#ef4444;
}
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
html,body{height:100%;font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);}

body::before{
    content:'';position:fixed;inset:0;
    background-image:radial-gradient(rgba(79,110,247,0.1) 1px,transparent 1px);
    background-size:28px 28px;pointer-events:none;z-index:0;
}
body::after{
    content:'';position:fixed;inset:0;
    background:
        radial-gradient(ellipse 60% 50% at 20% 10%,rgba(79,110,247,0.07) 0%,transparent 60%),
        radial-gradient(ellipse 50% 40% at 80% 85%,rgba(124,58,237,0.06) 0%,transparent 60%);
    pointer-events:none;z-index:0;
}

.page{min-height:100vh;display:grid;grid-template-columns:1fr 1fr;position:relative;z-index:1;}

/* LEFT */
.left-panel{
    display:flex;flex-direction:column;justify-content:space-between;
    padding:44px 52px;border-right:1px solid var(--border);
    position:relative;overflow:hidden;
}
.left-panel::before{
    content:'';position:absolute;bottom:-140px;left:-140px;
    width:420px;height:420px;border-radius:50%;
    border:1px solid rgba(79,110,247,0.1);
}
.left-panel::after{
    content:'';position:absolute;bottom:-90px;left:-90px;
    width:280px;height:280px;border-radius:50%;
    border:1px solid rgba(79,110,247,0.07);
}
.brand{display:flex;align-items:center;gap:14px;}
.brand-logo{
    width:44px;height:44px;
    background:linear-gradient(135deg,var(--accent),var(--accent2));
    border-radius:12px;display:grid;place-items:center;
    font-size:1.25rem;box-shadow:0 0 20px rgba(79,110,247,0.3);flex-shrink:0;
}
.brand-name{font-family:'Syne',sans-serif;font-weight:800;font-size:1.1rem;}
.brand-sub{font-size:0.7rem;color:var(--muted);text-transform:uppercase;letter-spacing:0.08em;margin-top:2px;}

.left-hero{flex:1;display:flex;flex-direction:column;justify-content:center;padding:48px 0 32px;}
.hero-eyebrow{
    font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.14em;
    color:var(--emerald);margin-bottom:14px;display:flex;align-items:center;gap:8px;
}
.hero-eyebrow::before{content:'';width:24px;height:2px;background:var(--emerald);border-radius:1px;}
.hero-title{
    font-family:'Syne',sans-serif;font-size:2.7rem;font-weight:800;
    line-height:1.1;letter-spacing:-0.04em;
    background:linear-gradient(145deg,#ffffff 30%,rgba(255,255,255,0.38));
    -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
    margin-bottom:18px;
}
.hero-desc{font-size:0.9rem;color:var(--text2);line-height:1.75;max-width:380px;}

.requirements{margin-top:32px;display:flex;flex-direction:column;gap:10px;}
.req{
    display:flex;align-items:center;gap:10px;
    font-size:0.82rem;color:var(--muted);
}
.req-icon{
    width:26px;height:26px;border-radius:6px;
    background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.2);
    display:grid;place-items:center;font-size:0.8rem;flex-shrink:0;
}

.status-bar{display:flex;align-items:center;gap:8px;font-size:0.75rem;color:var(--muted);}
.status-dot{width:7px;height:7px;border-radius:50%;background:var(--emerald);animation:pulse 2s ease-in-out infinite;}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:0.3}}

/* RIGHT */
.right-panel{
    display:flex;flex-direction:column;align-items:center;justify-content:center;
    padding:44px 52px;position:relative;
}
.right-nav{position:absolute;top:32px;right:44px;display:flex;gap:20px;}
.right-nav a{font-size:0.8rem;color:var(--muted);text-decoration:none;font-weight:500;transition:color 0.2s;}
.right-nav a:hover{color:var(--text2);}

.form-card{width:100%;max-width:400px;animation:slideUp 0.55s cubic-bezier(.34,1.2,.64,1) both;}
@keyframes slideUp{from{opacity:0;transform:translateY(28px)}to{opacity:1;transform:translateY(0)}}

.form-tag{
    display:inline-flex;align-items:center;gap:6px;padding:5px 12px;
    background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.22);
    border-radius:20px;font-size:0.72rem;font-weight:600;color:#6ee7b7;
    text-transform:uppercase;letter-spacing:0.08em;margin-bottom:14px;
}
.form-title{font-family:'Syne',sans-serif;font-size:1.75rem;font-weight:800;color:var(--text);letter-spacing:-0.03em;margin-bottom:6px;}
.form-sub{font-size:0.84rem;color:var(--muted);margin-bottom:28px;line-height:1.6;}

.alert{
    display:flex;align-items:flex-start;gap:10px;padding:12px 16px;
    border-radius:10px;font-size:0.82rem;margin-bottom:20px;
    background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.25);color:#fca5a5;
    animation:shakeX 0.4s ease;
}
@keyframes shakeX{0%,100%{transform:translateX(0)}20%{transform:translateX(-5px)}40%{transform:translateX(5px)}60%{transform:translateX(-3px)}80%{transform:translateX(3px)}}

.field{margin-bottom:16px;}
.field-label{display:block;font-size:0.72rem;font-weight:600;text-transform:uppercase;letter-spacing:0.08em;color:var(--text2);margin-bottom:8px;}
.field-wrap{position:relative;}
.field-icon{position:absolute;left:14px;top:50%;transform:translateY(-50%);font-size:0.95rem;pointer-events:none;opacity:0.5;}
.field-wrap input{
    width:100%;padding:12px 46px 12px 42px;
    background:var(--card);border:1px solid var(--border2);border-radius:10px;
    color:var(--text);font-size:0.88rem;font-family:'DM Sans',sans-serif;
    outline:none;transition:border-color 0.2s,box-shadow 0.2s,background 0.2s;
    caret-color:var(--accent);
}
.field-wrap input::placeholder{color:var(--muted);}
.field-wrap input:focus{border-color:rgba(79,110,247,0.55);background:rgba(79,110,247,0.04);box-shadow:0 0 0 3px rgba(79,110,247,0.1);}
.toggle-eye{
    position:absolute;right:14px;top:50%;transform:translateY(-50%);
    cursor:pointer;font-size:0.95rem;opacity:0.45;transition:opacity 0.2s;
    background:none;border:none;color:inherit;line-height:1;padding:2px;
}
.toggle-eye:hover{opacity:0.9;}

/* Strength meter */
.strength-wrap{margin-top:8px;}
.strength-bar{
    height:4px;border-radius:2px;background:var(--border2);overflow:hidden;margin-bottom:5px;
}
.strength-fill{height:100%;border-radius:2px;width:0%;transition:width 0.4s,background 0.4s;}
.strength-label{font-size:0.7rem;color:var(--muted);}

.submit-btn{
    width:100%;padding:13px;
    background:linear-gradient(135deg,var(--emerald),#059669);
    border:none;border-radius:10px;color:#fff;
    font-family:'DM Sans',sans-serif;font-size:0.9rem;font-weight:600;
    cursor:pointer;transition:transform 0.2s,box-shadow 0.2s;margin-top:6px;
    box-shadow:0 4px 18px rgba(16,185,129,0.35);
    display:flex;align-items:center;justify-content:center;gap:8px;
}
.submit-btn:hover{transform:translateY(-2px);box-shadow:0 8px 26px rgba(16,185,129,0.45);}
.submit-btn:active{transform:translateY(0);opacity:0.88;}
.btn-arrow{transition:transform 0.2s;}
.submit-btn:hover .btn-arrow{transform:translateX(4px);}

.divider{display:flex;align-items:center;gap:12px;margin:20px 0;font-size:0.72rem;color:var(--muted);}
.divider::before,.divider::after{content:'';flex:1;height:1px;background:var(--border);}

.back-link{
    display:flex;align-items:center;justify-content:center;gap:6px;padding:11px;
    background:transparent;border:1px solid var(--border2);border-radius:10px;
    color:var(--text2);text-decoration:none;font-size:0.84rem;font-weight:500;transition:all 0.2s;
}
.back-link:hover{background:var(--card);color:var(--text);}

@media(max-width:860px){
    .page{grid-template-columns:1fr;}
    .left-panel{display:none;}
    .right-panel{padding:40px 24px;justify-content:flex-start;padding-top:80px;}
    .right-nav{right:24px;}
    .form-card{max-width:100%;}
}
::-webkit-scrollbar{width:5px;}
::-webkit-scrollbar-track{background:transparent;}
::-webkit-scrollbar-thumb{background:var(--border2);border-radius:3px;}
</style>
</head>
<body>
<div class="page">

    <!-- LEFT -->
    <div class="left-panel">
        <div class="brand">
            <div class="brand-logo">🚔</div>
            <div>
                <div class="brand-name">e-Seva</div>
                <div class="brand-sub">Cop Friendly Portal</div>
            </div>
        </div>

        <div class="left-hero">
            <div class="hero-eyebrow">Almost There</div>
            <h1 class="hero-title">Create a New<br>Password</h1>
            <p class="hero-desc">Choose a strong password to secure your e-Seva account. Your previous password will be permanently replaced.</p>

            <div class="requirements">
                <div class="req"><div class="req-icon">🔢</div>At least 8 characters long</div>
                <div class="req"><div class="req-icon">🔢</div>Contains at least one number</div>
                <div class="req"><div class="req-icon">✅</div>Both passwords must match</div>
                <div class="req"><div class="req-icon">⏱️</div>Link expires after 1 hour</div>
            </div>
        </div>

        <div class="status-bar">
            <div class="status-dot"></div>
            Secure · Encrypted · © 2026 e-Seva
        </div>
    </div>

    <!-- RIGHT -->
    <div class="right-panel">
        <nav class="right-nav">
            <a href="index.html">Home</a>
            <a href="login.html">Sign In</a>
        </nav>

        <div class="form-card">
            <div class="form-tag">🔐 Set New Password</div>
            <h2 class="form-title">New password</h2>
            <p class="form-sub">Enter and confirm your new password below. Make it strong!</p>

            <?php if ($err && isset($errMessages[$err])): ?>
            <div class="alert">⚠️ <?php echo htmlspecialchars($errMessages[$err]); ?></div>
            <?php endif; ?>

            <form method="POST" action="reset_password.php" id="resetForm">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                <div class="field">
                    <label class="field-label" for="new_password">New Password</label>
                    <div class="field-wrap">
                        <span class="field-icon">🔒</span>
                        <input
                            type="password"
                            id="new_password"
                            name="new_password"
                            placeholder="Minimum 8 characters"
                            required
                            minlength="8"
                            autocomplete="new-password"
                            oninput="updateStrength(this.value)"
                        >
                        <button type="button" class="toggle-eye" onclick="togglePwd('new_password',this)">👁️</button>
                    </div>
                    <div class="strength-wrap">
                        <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
                        <div class="strength-label" id="strengthLabel">Enter a password</div>
                    </div>
                </div>

                <div class="field">
                    <label class="field-label" for="confirm_password">Confirm Password</label>
                    <div class="field-wrap">
                        <span class="field-icon">🔒</span>
                        <input
                            type="password"
                            id="confirm_password"
                            name="confirm_password"
                            placeholder="Repeat your password"
                            required
                            autocomplete="new-password"
                        >
                        <button type="button" class="toggle-eye" onclick="togglePwd('confirm_password',this)">👁️</button>
                    </div>
                </div>

                <button type="submit" class="submit-btn" id="submitBtn">
                    Update Password
                    <span class="btn-arrow">→</span>
                </button>
            </form>

            <div class="divider">or</div>
            <a href="forgot_password.html" class="back-link">← Request a new link</a>
        </div>
    </div>
</div>

<script>
function togglePwd(id, btn){
    const input = document.getElementById(id);
    if(input.type === 'password'){
        input.type = 'text';
        btn.textContent = '🙈';
    } else {
        input.type = 'password';
        btn.textContent = '👁️';
    }
}

function updateStrength(val){
    const fill  = document.getElementById('strengthFill');
    const label = document.getElementById('strengthLabel');
    let score = 0;
    if(val.length >= 8)  score++;
    if(val.length >= 12) score++;
    if(/[0-9]/.test(val))  score++;
    if(/[A-Z]/.test(val))  score++;
    if(/[^a-zA-Z0-9]/.test(val)) score++;

    const levels = [
        { pct:'0%',   color:'transparent', text:'Enter a password' },
        { pct:'25%',  color:'#ef4444',     text:'Weak' },
        { pct:'50%',  color:'#f59e0b',     text:'Fair' },
        { pct:'75%',  color:'#4f6ef7',     text:'Good' },
        { pct:'100%', color:'#10b981',     text:'Strong' },
    ];
    const l = levels[Math.min(score, 4)];
    fill.style.width      = l.pct;
    fill.style.background = l.color;
    label.textContent     = l.text;
    label.style.color     = score > 0 ? l.color : 'var(--muted)';
}

document.getElementById('resetForm').addEventListener('submit', function(){
    const btn = document.getElementById('submitBtn');
    btn.innerHTML = '<span style="display:inline-block;width:16px;height:16px;border:2px solid rgba(255,255,255,0.35);border-top-color:#fff;border-radius:50%;animation:spin .7s linear infinite"></span> Updating…';
    btn.disabled = true;
});

const s = document.createElement('style');
s.textContent = '@keyframes spin{to{transform:rotate(360deg)}}';
document.head.appendChild(s);
</script>
</body>
</html>