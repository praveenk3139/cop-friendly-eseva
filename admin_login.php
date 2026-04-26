<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login — e-Seva</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
/* ═══════════════════════════════════
   TOKENS
═══════════════════════════════════ */
:root {
    --bg:       #080a10;
    --surface:  #0e1119;
    --card:     #111520;
    --border:   #1c2133;
    --border2:  #252d44;
    --accent:   #4f6ef7;
    --accent2:  #7c3aed;
    --emerald:  #10b981;
    --text:     #e8eaf2;
    --text2:    #a8adc4;
    --muted:    #4e5472;
    --danger:   #ef4444;
    --radius:   14px;
}

/* ═══════════════════════════════════
   BASE
═══════════════════════════════════ */
*, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }

html, body {
    height: 100%;
    font-family: 'DM Sans', sans-serif;
    background: var(--bg);
    color: var(--text);
    overflow-x: hidden;
}

/* ── Dot-grid texture ── */
body::before {
    content: '';
    position: fixed;
    inset: 0;
    background-image: radial-gradient(rgba(79,110,247,0.12) 1px, transparent 1px);
    background-size: 28px 28px;
    pointer-events: none;
    z-index: 0;
}

/* ── Ambient glows ── */
body::after {
    content: '';
    position: fixed;
    inset: 0;
    background:
        radial-gradient(ellipse 60% 50% at 15% 20%, rgba(79,110,247,0.08) 0%, transparent 60%),
        radial-gradient(ellipse 50% 40% at 85% 80%, rgba(124,58,237,0.07) 0%, transparent 60%);
    pointer-events: none;
    z-index: 0;
}

/* ═══════════════════════════════════
   LAYOUT WRAPPER
═══════════════════════════════════ */
.page {
    min-height: 100vh;
    display: grid;
    grid-template-columns: 1fr 1fr;
    position: relative;
    z-index: 1;
}

/* ═══════════════════════════════════
   LEFT PANEL — BRAND SIDE
═══════════════════════════════════ */
.left-panel {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 44px 52px;
    border-right: 1px solid var(--border);
    position: relative;
    overflow: hidden;
}

/* decorative corner ring */
.left-panel::before {
    content: '';
    position: absolute;
    bottom: -120px;
    left: -120px;
    width: 400px;
    height: 400px;
    border-radius: 50%;
    border: 1px solid rgba(79,110,247,0.12);
}

.left-panel::after {
    content: '';
    position: absolute;
    bottom: -80px;
    left: -80px;
    width: 260px;
    height: 260px;
    border-radius: 50%;
    border: 1px solid rgba(79,110,247,0.08);
}

/* Brand */
.brand {
    display: flex;
    align-items: center;
    gap: 14px;
}

.brand-logo {
    width: 44px; height: 44px;
    background: linear-gradient(135deg, var(--accent), var(--accent2));
    border-radius: 12px;
    display: grid;
    place-items: center;
    font-size: 1.25rem;
    box-shadow: 0 0 20px rgba(79,110,247,0.3);
    flex-shrink: 0;
}

.brand-text { line-height: 1.2; }

.brand-name {
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    font-size: 1.1rem;
    color: var(--text);
}

.brand-sub {
    font-size: 0.7rem;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 0.08em;
    margin-top: 2px;
}

/* Hero text */
.left-hero { flex: 1; display: flex; flex-direction: column; justify-content: center; padding: 48px 0 32px; }

.hero-eyebrow {
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.14em;
    color: var(--accent);
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.hero-eyebrow::before {
    content: '';
    width: 24px; height: 2px;
    background: var(--accent);
    border-radius: 1px;
}

.hero-title {
    font-family: 'Syne', sans-serif;
    font-size: 2.9rem;
    font-weight: 800;
    line-height: 1.1;
    letter-spacing: -0.04em;
    background: linear-gradient(145deg, #ffffff 30%, rgba(255,255,255,0.38));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 18px;
}

.hero-desc {
    font-size: 0.9rem;
    color: var(--text2);
    line-height: 1.75;
    max-width: 380px;
}

/* Feature pills */
.features {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-top: 36px;
}

.feat {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 0.82rem;
    color: var(--text2);
}

.feat-dot {
    width: 32px; height: 32px;
    border-radius: 8px;
    border: 1px solid var(--border2);
    display: grid;
    place-items: center;
    font-size: 0.9rem;
    background: rgba(255,255,255,0.03);
    flex-shrink: 0;
}

/* Status bar */
.status-bar {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.75rem;
    color: var(--muted);
}

.status-dot {
    width: 7px; height: 7px;
    border-radius: 50%;
    background: var(--emerald);
    animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
    0%,100% { opacity: 1; }
    50%      { opacity: 0.3; }
}

/* ═══════════════════════════════════
   RIGHT PANEL — LOGIN FORM
═══════════════════════════════════ */
.right-panel {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 44px 52px;
    position: relative;
}

.form-card {
    width: 100%;
    max-width: 400px;
    animation: slideUp 0.55s cubic-bezier(0.34,1.2,0.64,1) both;
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(28px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* Heading */
.form-heading { margin-bottom: 32px; }

.form-tag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 12px;
    background: rgba(79,110,247,0.1);
    border: 1px solid rgba(79,110,247,0.22);
    border-radius: 20px;
    font-size: 0.72rem;
    font-weight: 600;
    color: #7fa3ff;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    margin-bottom: 14px;
}

.form-title {
    font-family: 'Syne', sans-serif;
    font-size: 1.75rem;
    font-weight: 800;
    color: var(--text);
    letter-spacing: -0.03em;
    margin-bottom: 6px;
}

.form-sub {
    font-size: 0.84rem;
    color: var(--muted);
}

/* Error message */
.error-box {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 16px;
    background: rgba(239,68,68,0.1);
    border: 1px solid rgba(239,68,68,0.25);
    border-radius: 10px;
    font-size: 0.82rem;
    color: #fca5a5;
    margin-bottom: 20px;
    animation: shakeX 0.4s ease;
}

@keyframes shakeX {
    0%,100% { transform: translateX(0); }
    20%      { transform: translateX(-6px); }
    40%      { transform: translateX(6px); }
    60%      { transform: translateX(-4px); }
    80%      { transform: translateX(4px); }
}

/* Field */
.field { margin-bottom: 16px; }

.field-label {
    display: block;
    font-size: 0.72rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--text2);
    margin-bottom: 8px;
}

.field-wrap {
    position: relative;
}

.field-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 0.95rem;
    pointer-events: none;
    opacity: 0.55;
}

.field-wrap input {
    width: 100%;
    padding: 12px 14px 12px 42px;
    background: var(--card);
    border: 1px solid var(--border2);
    border-radius: 10px;
    color: var(--text);
    font-size: 0.88rem;
    font-family: 'DM Sans', sans-serif;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
    caret-color: var(--accent);
}

.field-wrap input::placeholder { color: var(--muted); }

.field-wrap input:focus {
    border-color: rgba(79,110,247,0.55);
    background: rgba(79,110,247,0.04);
    box-shadow: 0 0 0 3px rgba(79,110,247,0.1);
}

.field-wrap input.has-toggle { padding-right: 46px; }

.toggle-eye {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    font-size: 0.95rem;
    opacity: 0.45;
    transition: opacity 0.2s;
    user-select: none;
    background: none;
    border: none;
    color: inherit;
    line-height: 1;
    padding: 2px;
}

.toggle-eye:hover { opacity: 0.9; }

/* Submit button */
.submit-btn {
    width: 100%;
    padding: 13px;
    background: linear-gradient(135deg, var(--accent), #3b55e0);
    border: none;
    border-radius: 10px;
    color: #fff;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s, opacity 0.2s;
    margin-top: 6px;
    box-shadow: 0 4px 18px rgba(79,110,247,0.35);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    letter-spacing: 0.01em;
}

.submit-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 26px rgba(79,110,247,0.45);
}

.submit-btn:active { transform: translateY(0); opacity: 0.88; }

.btn-arrow {
    transition: transform 0.2s;
}
.submit-btn:hover .btn-arrow { transform: translateX(4px); }

/* Divider */
.divider {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 20px 0;
    font-size: 0.72rem;
    color: var(--muted);
}

.divider::before, .divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--border);
}

/* Back link */
.back-link {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 11px;
    background: transparent;
    border: 1px solid var(--border2);
    border-radius: 10px;
    color: var(--text2);
    text-decoration: none;
    font-size: 0.84rem;
    font-weight: 500;
    transition: all 0.2s;
}

.back-link:hover {
    background: var(--card);
    border-color: var(--border2);
    color: var(--text);
}

/* Footer note */
.form-footer {
    margin-top: 28px;
    text-align: center;
    font-size: 0.72rem;
    color: var(--muted);
    line-height: 1.6;
}

.form-footer a {
    color: var(--text2);
    text-decoration: none;
}

.form-footer a:hover { color: var(--accent); }

/* Nav links (top-right corner of right panel) */
.right-nav {
    position: absolute;
    top: 32px;
    right: 44px;
    display: flex;
    gap: 20px;
}

.right-nav a {
    font-size: 0.8rem;
    color: var(--muted);
    text-decoration: none;
    font-weight: 500;
    transition: color 0.2s;
}

.right-nav a:hover { color: var(--text2); }

/* ═══════════════════════════════════
   RESPONSIVE
═══════════════════════════════════ */
@media (max-width: 860px) {
    .page { grid-template-columns: 1fr; }
    .left-panel { display: none; }
    .right-panel { padding: 40px 24px; justify-content: flex-start; padding-top: 80px; }
    .right-nav { right: 24px; }
    .form-card { max-width: 100%; }
}

/* scrollbar */
::-webkit-scrollbar { width: 5px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: var(--border2); border-radius: 3px; }
</style>
</head>
<body>

<div class="page">

    <!-- ══════════════════ LEFT PANEL ══════════════════ -->
    <div class="left-panel">

        <!-- Brand -->
        <div class="brand">
            <div class="brand-logo">🚔</div>
            <div class="brand-text">
                <div class="brand-name">e-Seva</div>
                <div class="brand-sub">Cop Friendly Portal</div>
            </div>
        </div>

        <!-- Hero -->
        <div class="left-hero">
            <div class="hero-eyebrow">Restricted Access</div>
            <h1 class="hero-title">Police Admin<br>Command Centre</h1>
            <p class="hero-desc">Secure access to the e-Seva administration portal. Monitor complaints, analyse crime patterns, and dispatch resources from a unified dashboard.</p>

            <div class="features">
                <div class="feat">
                    <div class="feat-dot">🗺️</div>
                    Real-time crime mapping & heatmaps
                </div>
                <div class="feat">
                    <div class="feat-dot">🤖</div>
                    AI-powered department routing
                </div>
                <div class="feat">
                    <div class="feat-dot">🚨</div>
                    Fraud detection & anomaly alerts
                </div>
                <div class="feat">
                    <div class="feat-dot">📈</div>
                    Predictive crime trend analysis
                </div>
            </div>
        </div>

        <!-- Status -->
        <div class="status-bar">
            <div class="status-dot"></div>
            All systems operational · © 2026 e-Seva
        </div>

    </div>

    <!-- ══════════════════ RIGHT PANEL ══════════════════ -->
    <div class="right-panel">

        <!-- Nav links -->
        <nav class="right-nav">
            <a href="index.html">Home</a>
            <a href="register.html">Register</a>
            <a href="login.html">User Login</a>
        </nav>

        <div class="form-card">

            <!-- Heading -->
            <div class="form-heading">
                <div class="form-tag">🛡️ Admin Portal</div>
                <h2 class="form-title">Welcome back</h2>
                <p class="form-sub">Sign in with your administrator credentials to continue.</p>
            </div>

            <!-- Error message (shown when login fails) -->
            <?php if(isset($_GET['error']) && $_GET['error'] === '1'): ?>
            <div class="error-box">
                ⚠️ Invalid username or password. Please try again.
            </div>
            <?php endif; ?>

            <!-- Login form -->
            <form action="admin_check.php" method="POST" id="loginForm">

                <div class="field">
                    <label class="field-label" for="username">Username</label>
                    <div class="field-wrap">
                        <span class="field-icon">👤</span>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            placeholder="Enter admin username"
                            required
                            autocomplete="username"
                            spellcheck="false"
                        >
                    </div>
                </div>

                <div class="field">
                    <label class="field-label" for="password">Password</label>
                    <div class="field-wrap">
                        <span class="field-icon">🔑</span>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Enter your password"
                            required
                            autocomplete="current-password"
                            class="has-toggle"
                        >
                        <button type="button" class="toggle-eye" id="eyeBtn" onclick="togglePwd()" title="Toggle password">
                            👁️
                        </button>
                    </div>
                </div>

                <button type="submit" class="submit-btn" id="submitBtn">
                    Sign In
                    <span class="btn-arrow">→</span>
                </button>

            </form>

            <div class="divider">or</div>

            <a href="index.html" class="back-link">
                ← Back to Home
            </a>

            <div class="form-footer">
                Authorised law enforcement personnel only.<br>
                Unauthorised access is a criminal offence.<br><br>
                <a href="login.html">Not an admin? Sign in as citizen →</a>
            </div>

        </div>

    </div>
</div>

<script>
/* Password toggle */
function togglePwd(){
    const input = document.getElementById('password');
    const btn   = document.getElementById('eyeBtn');
    if(input.type === 'password'){
        input.type = 'text';
        btn.textContent = '🙈';
    } else {
        input.type = 'password';
        btn.textContent = '👁️';
    }
    input.focus();
}

/* Loading state on submit */
document.getElementById('loginForm').addEventListener('submit', function(){
    const btn = document.getElementById('submitBtn');
    btn.innerHTML = '<span style="display:inline-block;width:16px;height:16px;border:2px solid rgba(255,255,255,0.4);border-top-color:#fff;border-radius:50%;animation:spin 0.7s linear infinite"></span> Authenticating…';
    btn.disabled = true;
    btn.style.opacity = '0.8';
});

/* Inject spin keyframe */
const style = document.createElement('style');
style.textContent = '@keyframes spin{to{transform:rotate(360deg)}}';
document.head.appendChild(style);

/* Auto-focus first empty field */
window.addEventListener('DOMContentLoaded', () => {
    const u = document.getElementById('username');
    const p = document.getElementById('password');
    if(!u.value) u.focus();
    else p.focus();
});
</script>

</body>
</html>