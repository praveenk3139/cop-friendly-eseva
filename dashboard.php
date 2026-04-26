<?php
session_start();
include "db.php";

/* Protect page */
if(!isset($_SESSION['user_id'])){
    header("Location: login.html");
    exit();
}

function e($str){
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/* Chart Data */
$chartData = [];
$res = mysqli_query($conn,"
    SELECT DATE(created_at) as d, COUNT(*) as total 
    FROM complaints 
    GROUP BY DATE(created_at)
    ORDER BY d ASC
    LIMIT 14
");
while($row = mysqli_fetch_assoc($res)){
    $chartData[] = $row;
}

/* For police: get list of users who have sent messages */
$chatUsers = [];
if($_SESSION['role'] == "police"){
    $cu = mysqli_query($conn,"
        SELECT DISTINCT u.user_id, u.name
        FROM messages m
        JOIN users u ON u.user_id = m.sender_id
        WHERE m.receiver_id = '1'
    ");
    while($r = mysqli_fetch_assoc($cu)){
        $chatUsers[] = $r;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Dashboard - Cop Friendly e-Seva</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;600;700&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

:root{
    --bg-deep:     #050d1a;
    --bg-panel:    rgba(8,20,45,0.85);
    --bg-card:     rgba(255,255,255,0.04);
    --border:      rgba(255,255,255,0.08);
    --accent:      #00d4ff;
    --accent2:     #f59e0b;
    --danger:      #ef4444;
    --success:     #22c55e;
    --blue:        #3b82f6;
    --text:        #e2e8f0;
    --muted:       #94a3b8;
    --glow:        0 0 20px rgba(0,212,255,0.15);
}

body{
    background: var(--bg-deep);
    background-image:
        radial-gradient(ellipse at 10% 20%, rgba(0,60,120,0.4) 0%, transparent 60%),
        radial-gradient(ellipse at 90% 80%, rgba(0,100,80,0.2) 0%, transparent 60%);
    color: var(--text);
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    min-height: 100vh;
}

/* NAVBAR */
.navbar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding: 14px 30px;
    background: rgba(0,0,0,0.6);
    border-bottom: 1px solid var(--border);
    backdrop-filter: blur(16px);
    position: sticky;
    top:0;
    z-index: 100;
}

.navbar-brand{
    display:flex;
    align-items:center;
    gap: 10px;
    font-family: 'Rajdhani', sans-serif;
    font-size: 20px;
    font-weight: 700;
    letter-spacing: 1px;
    color: var(--accent);
    text-shadow: 0 0 20px rgba(0,212,255,0.4);
}

.navbar-right{
    display:flex;
    align-items:center;
    gap:16px;
    font-size:13px;
    color: var(--muted);
}

.logout{
    background: rgba(239,68,68,0.15);
    border: 1px solid rgba(239,68,68,0.4);
    padding: 6px 14px;
    border-radius: 6px;
    color: #fca5a5;
    text-decoration:none;
    font-size:13px;
    transition: all 0.2s;
}
.logout:hover{
    background: rgba(239,68,68,0.3);
    color: white;
}

/* LAYOUT */
.layout{
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 20px;
    width: 95%;
    max-width: 1400px;
    margin: 24px auto;
}

/* MAIN CONTENT */
.main-content{}

/* SIDEBAR */
.sidebar{
    display: flex;
    flex-direction: column;
    gap: 16px;
}

/* PAGE TITLE */
.page-title{
    font-family: 'Rajdhani', sans-serif;
    font-size: 22px;
    font-weight: 700;
    letter-spacing: 0.5px;
    margin-bottom: 16px;
    color: white;
    display: flex;
    align-items: center;
    gap: 10px;
}

/* STATS GRID */
.stats-grid{
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-bottom: 20px;
}

.stat-card{
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 16px;
    text-align: center;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.stat-card:hover{
    border-color: rgba(0,212,255,0.3);
    box-shadow: var(--glow);
}

.stat-card .label{
    font-size: 11px;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 6px;
}

.stat-card .value{
    font-family: 'Rajdhani', sans-serif;
    font-size: 28px;
    font-weight: 700;
    color: var(--accent);
}

/* CARD */
.card{
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 18px;
    margin-bottom: 14px;
    backdrop-filter: blur(10px);
    transition: border-color 0.2s;
}

.card:hover{
    border-color: rgba(255,255,255,0.14);
}

.card h3{
    font-family: 'Rajdhani', sans-serif;
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 14px;
    color: white;
    letter-spacing: 0.3px;
}

/* SECTION HEADING */
.section-heading{
    font-family: 'Rajdhani', sans-serif;
    font-size: 16px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--muted);
    margin: 20px 0 12px;
    padding-bottom: 8px;
    border-bottom: 1px solid var(--border);
}

/* COMPLAINT CARD */
.complaint-card{
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 16px;
    margin-bottom: 12px;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.complaint-card:hover{
    border-color: rgba(0,212,255,0.2);
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
}

.complaint-card .row{
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 8px;
    gap: 10px;
}

.complaint-card .id-badge{
    font-family: 'Rajdhani', sans-serif;
    font-size: 13px;
    color: var(--accent);
    font-weight: 600;
}

.complaint-card .meta{
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 8px;
    font-size: 12px;
    color: var(--muted);
}

.complaint-card .desc{
    color: #cbd5e1;
    line-height: 1.5;
    font-size: 13px;
    margin: 6px 0;
}

/* BADGES */
.badge{
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.pending   { background: rgba(245,158,11,0.2); color:#fbbf24; border:1px solid rgba(245,158,11,0.4); }
.inprogress{ background: rgba(59,130,246,0.2); color:#60a5fa; border:1px solid rgba(59,130,246,0.4); }
.resolved  { background: rgba(34,197,94,0.2);  color:#4ade80; border:1px solid rgba(34,197,94,0.4); }
.escalated { background: rgba(239,68,68,0.2);  color:#f87171; border:1px solid rgba(239,68,68,0.4); }

.priority-high   { color:#f87171; font-weight:600; }
.priority-medium { color:#fbbf24; font-weight:600; }
.priority-low    { color:#4ade80; font-weight:600; }

/* WARNING */
.warning{
    display: inline-flex;
    align-items: center;
    gap: 4px;
    color: #fbbf24;
    font-size: 12px;
    font-weight: 600;
    background: rgba(245,158,11,0.1);
    padding: 3px 8px;
    border-radius: 4px;
    border: 1px solid rgba(245,158,11,0.3);
}

/* FORM */
.form-group{
    margin-bottom: 10px;
}

input, textarea, select{
    width:100%;
    padding: 10px 12px;
    border-radius: 7px;
    border: 1px solid var(--border);
    background: rgba(255,255,255,0.05);
    color: white;
    font-size: 13px;
    font-family: 'Inter', sans-serif;
    transition: border-color 0.2s;
    outline: none;
}

input::placeholder, textarea::placeholder{
    color: var(--muted);
}

input:focus, textarea:focus, select:focus{
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(0,212,255,0.1);
}

select option{
    background: #0f2a4a;
}

textarea{
    resize: vertical;
    min-height: 80px;
}

button[type="submit"]{
    background: linear-gradient(135deg, #0ea5e9, #00d4ff);
    color: white;
    border: none;
    padding: 11px;
    border-radius: 7px;
    cursor: pointer;
    width: 100%;
    font-size: 14px;
    font-weight: 600;
    font-family: 'Rajdhani', sans-serif;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    transition: opacity 0.2s, box-shadow 0.2s;
    margin-top: 4px;
}

button[type="submit"]:hover{
    opacity: 0.9;
    box-shadow: 0 0 20px rgba(0,212,255,0.3);
}

/* LINKS */
a{
    color: var(--accent);
    text-decoration: none;
    font-size: 13px;
    transition: color 0.2s;
}
a:hover{ color: white; }

.action-links{
    display: flex;
    gap: 12px;
    margin-top: 10px;
    flex-wrap: wrap;
}

.action-link{
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 12px;
    padding: 4px 10px;
    background: rgba(255,255,255,0.05);
    border: 1px solid var(--border);
    border-radius: 5px;
    color: var(--muted);
    transition: all 0.2s;
}
.action-link:hover{
    background: rgba(0,212,255,0.1);
    border-color: rgba(0,212,255,0.4);
    color: var(--accent);
}

hr{
    margin: 20px 0;
    border:0;
    height:1px;
    background: var(--border);
}

/* SIDEBAR - MAP LINK CARD */
.map-link-card{
    background: linear-gradient(135deg, rgba(0,60,100,0.6), rgba(0,30,60,0.8));
    border: 1px solid rgba(0,212,255,0.25);
    border-radius: 10px;
    padding: 18px;
    text-align: center;
    text-decoration: none;
    display: block;
    transition: all 0.25s;
    position: relative;
    overflow: hidden;
}
.map-link-card::before{
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at 50% 0%, rgba(0,212,255,0.08), transparent 70%);
}
.map-link-card:hover{
    border-color: rgba(0,212,255,0.6);
    box-shadow: 0 0 30px rgba(0,212,255,0.15);
    transform: translateY(-2px);
}
.map-link-card .map-icon{
    font-size: 38px;
    display: block;
    margin-bottom: 10px;
}
.map-link-card .map-title{
    font-family: 'Rajdhani', sans-serif;
    font-size: 17px;
    font-weight: 700;
    color: white;
    margin-bottom: 4px;
    letter-spacing: 0.5px;
}
.map-link-card .map-desc{
    font-size: 12px;
    color: var(--muted);
    line-height: 1.4;
}
.map-link-card .map-arrow{
    display: inline-block;
    margin-top: 12px;
    background: rgba(0,212,255,0.15);
    border: 1px solid rgba(0,212,255,0.3);
    color: var(--accent);
    font-size: 12px;
    padding: 5px 14px;
    border-radius: 20px;
    font-weight: 600;
}

/* CHART CARD */
.chart-card{
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 18px;
}
.chart-card h3{
    font-family: 'Rajdhani', sans-serif;
    font-size: 16px;
    font-weight: 600;
    color: white;
    margin-bottom: 14px;
    letter-spacing: 0.3px;
}

/* SIDEBAR QUICK STATS */
.sidebar-stat{
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid var(--border);
}
.sidebar-stat:last-child{ border-bottom: none; }
.sidebar-stat .s-label{ font-size: 13px; color: var(--muted); }
.sidebar-stat .s-value{
    font-family: 'Rajdhani', sans-serif;
    font-size: 18px;
    font-weight: 700;
}

/* =====================
   CHAT WIDGET
===================== */
.chat-widget{
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 10px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.chat-header{
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    background: rgba(0,212,255,0.06);
    border-bottom: 1px solid var(--border);
    cursor: pointer;
    user-select: none;
}

.chat-header-left{
    display: flex;
    align-items: center;
    gap: 8px;
    font-family: 'Rajdhani', sans-serif;
    font-size: 15px;
    font-weight: 700;
    color: white;
    letter-spacing: 0.3px;
}

.chat-online-dot{
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--success);
    box-shadow: 0 0 6px rgba(34,197,94,0.6);
    animation: pulse-dot 2s infinite;
}

@keyframes pulse-dot{
    0%,100%{ opacity:1; }
    50%{ opacity:0.4; }
}

.chat-toggle-icon{
    color: var(--muted);
    font-size: 12px;
    transition: transform 0.2s;
}

.chat-toggle-icon.open{
    transform: rotate(180deg);
}

/* Police: user selector tabs */
.chat-user-tabs{
    display: flex;
    gap: 0;
    overflow-x: auto;
    border-bottom: 1px solid var(--border);
    background: rgba(0,0,0,0.2);
    scrollbar-width: none;
}
.chat-user-tabs::-webkit-scrollbar{ display:none; }

.chat-user-tab{
    flex-shrink: 0;
    padding: 7px 12px;
    font-size: 12px;
    color: var(--muted);
    cursor: pointer;
    border-bottom: 2px solid transparent;
    white-space: nowrap;
    transition: all 0.2s;
    background: none;
    border-left: none;
    border-right: none;
    border-top: none;
}
.chat-user-tab:hover{
    color: white;
    background: rgba(255,255,255,0.04);
}
.chat-user-tab.active{
    color: var(--accent);
    border-bottom-color: var(--accent);
}

.chat-body{
    display: flex;
    flex-direction: column;
    max-height: 340px;
}

.chat-messages{
    flex: 1;
    overflow-y: auto;
    padding: 12px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    min-height: 160px;
    max-height: 240px;
    scroll-behavior: smooth;
}

.chat-messages::-webkit-scrollbar{
    width: 4px;
}
.chat-messages::-webkit-scrollbar-track{
    background: transparent;
}
.chat-messages::-webkit-scrollbar-thumb{
    background: rgba(255,255,255,0.1);
    border-radius: 2px;
}

.chat-bubble{
    max-width: 85%;
    padding: 8px 12px;
    border-radius: 10px;
    font-size: 12.5px;
    line-height: 1.45;
    word-break: break-word;
    position: relative;
}

.chat-bubble.mine{
    background: linear-gradient(135deg, rgba(0,180,220,0.25), rgba(0,120,180,0.2));
    border: 1px solid rgba(0,212,255,0.2);
    align-self: flex-end;
    border-bottom-right-radius: 3px;
    color: #e2e8f0;
}

.chat-bubble.theirs{
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.1);
    align-self: flex-start;
    border-bottom-left-radius: 3px;
    color: #cbd5e1;
}

.chat-bubble .bubble-sender{
    font-size: 10px;
    color: var(--accent);
    font-weight: 600;
    margin-bottom: 2px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.chat-bubble.mine .bubble-sender{
    text-align: right;
    color: rgba(0,212,255,0.7);
}

.chat-bubble .bubble-time{
    font-size: 10px;
    color: var(--muted);
    margin-top: 3px;
    opacity: 0.7;
}

.chat-bubble.mine .bubble-time{
    text-align: right;
}

.chat-empty{
    text-align: center;
    color: var(--muted);
    font-size: 12px;
    padding: 30px 0;
    line-height: 1.6;
}

.chat-footer{
    display: flex;
    gap: 8px;
    padding: 10px 12px;
    border-top: 1px solid var(--border);
    background: rgba(0,0,0,0.15);
}

.chat-footer input{
    flex: 1;
    padding: 8px 12px;
    border-radius: 20px;
    border: 1px solid var(--border);
    background: rgba(255,255,255,0.05);
    color: white;
    font-size: 12.5px;
    outline: none;
    transition: border-color 0.2s;
    min-width: 0;
}

.chat-footer input:focus{
    border-color: rgba(0,212,255,0.4);
    box-shadow: 0 0 0 2px rgba(0,212,255,0.08);
}

.chat-footer input::placeholder{
    color: var(--muted);
}

.chat-send-btn{
    background: linear-gradient(135deg, #0ea5e9, #00d4ff);
    border: none;
    color: white;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 14px;
    transition: opacity 0.2s, box-shadow 0.2s;
}
.chat-send-btn:hover{
    opacity: 0.85;
    box-shadow: 0 0 12px rgba(0,212,255,0.35);
}
.chat-send-btn:disabled{
    opacity: 0.4;
    cursor: not-allowed;
}

.chat-typing{
    font-size: 11px;
    color: var(--muted);
    padding: 2px 12px 6px;
    height: 18px;
    display: none;
}

/* Collapsed state */
.chat-collapsible{
    overflow: hidden;
    transition: max-height 0.3s ease;
    max-height: 400px;
}
.chat-collapsible.collapsed{
    max-height: 0;
}

/* RESPONSIVE */
@media(max-width: 900px){
    .layout{
        grid-template-columns: 1fr;
    }
    .sidebar{
        order: -1;
    }
    .stats-grid{
        grid-template-columns: repeat(2,1fr);
    }
}

@media(max-width: 500px){
    .stats-grid{
        grid-template-columns: 1fr 1fr;
    }
}
</style>
</head>

<body>

<!-- NAVBAR -->
<div class="navbar">
    <div class="navbar-brand">
        🚔 Cop Friendly e-Seva
    </div>
    <div class="navbar-right">
        <span>Welcome, <strong style="color:white;"><?php echo e($_SESSION['name']); ?></strong></span>
        <a href="logout.php" class="logout">Logout</a>
    </div>
</div>

<!-- MAIN LAYOUT -->
<div class="layout">

    <!-- ===================== MAIN CONTENT ===================== -->
    <div class="main-content">

    <?php if ($_SESSION['role'] == "police") { ?>

        <?php
        $total     = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM complaints"))['c'];
        $pending   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM complaints WHERE status='Pending'"))['c'];
        $resolved  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM complaints WHERE status='Resolved'"))['c'];
        $escalated = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM complaints WHERE status='Escalated'"))['c'];
        ?>

        <div class="page-title">🛡️ Police Dashboard</div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="label">Total</div>
                <div class="value"><?php echo $total; ?></div>
            </div>
            <div class="stat-card">
                <div class="label">Pending</div>
                <div class="value" style="color:#fbbf24;"><?php echo $pending; ?></div>
            </div>
            <div class="stat-card">
                <div class="label">Resolved</div>
                <div class="value" style="color:#4ade80;"><?php echo $resolved; ?></div>
            </div>
            <div class="stat-card">
                <div class="label">Escalated</div>
                <div class="value" style="color:#f87171;"><?php echo $escalated; ?></div>
            </div>
        </div>

        <div class="section-heading">All Complaints</div>

        <?php
        $result = mysqli_query($conn,"SELECT *, TIMESTAMPDIFF(HOUR, created_at, NOW()) as hours_passed FROM complaints ORDER BY complaint_id DESC");
        while($row = mysqli_fetch_assoc($result)){
            $statusClass = strtolower(str_replace(' ','',$row['status']));
            $p = $row['priority'] ?? 'Low';
            $pClass = 'priority-'.strtolower($p);
        ?>

        <div class="complaint-card">
            <div class="row">
                <div>
                    <span class="id-badge">#<?php echo $row['complaint_id']; ?></span>
                    &nbsp;
                    <strong><?php echo e($row['subject']); ?></strong>
                </div>
                <div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;">
                    <span class="badge <?php echo $statusClass; ?>"><?php echo e($row['status']); ?></span>
                    <span class="badge" style="background:rgba(255,255,255,0.05);color:var(--muted);border:1px solid var(--border);">⏱ <?php echo $row['hours_passed']; ?>h</span>
                    <?php if($row['hours_passed'] >= 48 && $row['status'] != "Resolved"){ ?>
                        <span class="warning">⚠ Overdue</span>
                    <?php } ?>
                </div>
            </div>

            <div class="desc"><?php echo e($row['description']); ?></div>

            <div class="meta">
                <span>📍 <?php echo e($row['location']); ?></span>
                <span>Priority: <span class="<?php echo $pClass; ?>"><?php echo $p; ?></span></span>
                <span>🗓 <?php echo $row['created_at']; ?></span>
            </div>

            <div class="action-links">
                <?php if($row['file']){ ?>
                    <a href="uploads/<?php echo $row['file']; ?>" download class="action-link">📎 Evidence</a>
                <?php } ?>
                <a href="download_complaint.php?id=<?php echo $row['complaint_id']; ?>" class="action-link">📄 Download PDF</a>
            </div>
        </div>

        <?php } ?>

    <?php } else { ?>

        <div class="page-title">👤 Citizen Dashboard</div>

        <div class="section-heading">Register Complaint</div>

        <div class="card">
            <form action="add_complaint.php" method="post" enctype="multipart/form-data">

                <div class="form-group">
                    <select name="type">
                        <option value="">🤖 Auto Detect (AI)</option>
                        <option>Theft</option>
                        <option>Cyber Crime</option>
                        <option>Harassment</option>
                        <option>Fraud</option>
                        <option>Accident</option>
                        <option>Missing Person</option>
                    </select>
                </div>

                <div class="form-group">
                    <input type="text" name="location" placeholder="📍 Enter location" required>
                </div>

                <div class="form-group">
                    <textarea name="description" placeholder="Describe your complaint in detail..." required></textarea>
                </div>

                <div class="form-group">
                    <input type="file" name="file" style="color:var(--muted);">
                </div>

                <input type="hidden" name="latitude"  id="lat">
                <input type="hidden" name="longitude" id="lng">

                <button type="submit">Submit Complaint</button>

            </form>
        </div>

        <div class="section-heading">Your Complaints</div>

        <?php
        $user_id = $_SESSION['user_id'];
        $result  = mysqli_query($conn,"SELECT * FROM complaints WHERE user_id='$user_id' ORDER BY complaint_id DESC");
        while($row = mysqli_fetch_assoc($result)){
            $statusClass = strtolower(str_replace(' ','',$row['status']));
        ?>

        <div class="complaint-card">
            <div class="row">
                <div>
                    <span class="id-badge">#<?php echo $row['complaint_id']; ?></span>
                    &nbsp;<strong><?php echo e($row['subject']); ?></strong>
                </div>
                <span class="badge <?php echo $statusClass; ?>"><?php echo e($row['status']); ?></span>
            </div>
            <div class="meta">
                <span>🗓 <?php echo $row['created_at']; ?></span>
            </div>
            <div class="action-links">
                <?php if($row['file']){ ?>
                    <a href="uploads/<?php echo $row['file']; ?>" download class="action-link">📎 Download</a>
                <?php } ?>
                <a href="download_complaint.php?id=<?php echo $row['complaint_id']; ?>" class="action-link">📄 PDF</a>
            </div>
        </div>

        <?php } ?>

        <script>
        navigator.geolocation.getCurrentPosition(function(pos){
            document.getElementById("lat").value = pos.coords.latitude;
            document.getElementById("lng").value = pos.coords.longitude;
        });
        </script>

    <?php } ?>

    </div><!-- /main-content -->


    <!-- ===================== SIDEBAR ===================== -->
    <div class="sidebar">

        <!-- MAP VIEW LINK -->
        <a href="map_view.php" class="map-link-card">
            <span class="map-icon">🗺️</span>
            <div class="map-title">Live Map View</div>
            <div class="map-desc">Visualise all complaint locations plotted on an interactive map</div>
            <span class="map-arrow">Open Map →</span>
        </a>

        <!-- COMPLAINT TRENDS CHART -->
        <div class="chart-card">
            <h3>📊 Complaint Trends</h3>
            <canvas id="barChart" height="200"></canvas>
        </div>

        <!-- ===================== CHAT WIDGET ===================== -->
        <div class="chat-widget">

            <div class="chat-header" id="chatToggleBtn">
                <div class="chat-header-left">
                    <span class="chat-online-dot"></span>
                    💬
                    <?php if($_SESSION['role'] == "police"){ ?>
                        Citizen Messages
                    <?php } else { ?>
                        Chat with Police
                    <?php } ?>
                </div>
                <span class="chat-toggle-icon open" id="chatToggleIcon">▼</span>
            </div>

            <div class="chat-collapsible" id="chatCollapsible">

                <?php if($_SESSION['role'] == "police" && !empty($chatUsers)){ ?>
                <!-- POLICE: user tabs -->
                <div class="chat-user-tabs" id="chatUserTabs">
                    <?php foreach($chatUsers as $i => $cu){ ?>
                    <button
                        class="chat-user-tab <?php echo $i===0?'active':''; ?>"
                        data-uid="<?php echo $cu['user_id']; ?>"
                        onclick="selectChatUser(this, <?php echo $cu['user_id']; ?>)"
                    ><?php echo e($cu['name']); ?></button>
                    <?php } ?>
                </div>
                <?php } elseif($_SESSION['role'] == "police" && empty($chatUsers)){ ?>
                <div style="padding:12px;font-size:12px;color:var(--muted);text-align:center;">No citizen messages yet.</div>
                <?php } ?>

                <div class="chat-body">
                    <div class="chat-messages" id="chatMessages">
                        <div class="chat-empty" id="chatEmpty">
                            <?php if($_SESSION['role'] == "police"){ ?>
                                📭 Select a citizen above<br>to view their messages
                            <?php } else { ?>
                                👋 Send a message to the<br>police station
                            <?php } ?>
                        </div>
                    </div>
                    <div class="chat-typing" id="chatTyping">● Typing...</div>
                </div>

                <div class="chat-footer">
                    <input
                        type="text"
                        id="chatInput"
                        placeholder="Type a message..."
                        autocomplete="off"
                        maxlength="500"
                    >
                    <button class="chat-send-btn" id="chatSendBtn" title="Send">
                        ➤
                    </button>
                </div>

            </div><!-- /chat-collapsible -->
        </div><!-- /chat-widget -->

        <!-- QUICK SUMMARY (police only) -->
        <?php if ($_SESSION['role'] == "police") { ?>
        <div class="card" style="margin-bottom:0;">
            <h3>⚡ Quick Summary</h3>
            <div class="sidebar-stat">
                <span class="s-label">Total Complaints</span>
                <span class="s-value" style="color:var(--accent);"><?php echo $total; ?></span>
            </div>
            <div class="sidebar-stat">
                <span class="s-label">Pending</span>
                <span class="s-value" style="color:#fbbf24;"><?php echo $pending; ?></span>
            </div>
            <div class="sidebar-stat">
                <span class="s-label">Resolved</span>
                <span class="s-value" style="color:#4ade80;"><?php echo $resolved; ?></span>
            </div>
            <div class="sidebar-stat">
                <span class="s-label">Escalated</span>
                <span class="s-value" style="color:#f87171;"><?php echo $escalated; ?></span>
            </div>
        </div>
        <?php } ?>

    </div><!-- /sidebar -->

</div><!-- /layout -->

<!-- CHART.JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const chartLabels = <?php echo json_encode(array_column($chartData,'d')); ?>;
const chartValues = <?php echo json_encode(array_column($chartData,'total')); ?>;

new Chart(document.getElementById("barChart"), {
    type: 'line',
    data: {
        labels: chartLabels,
        datasets: [{
            label: 'Complaints',
            data: chartValues,
            borderColor: '#00d4ff',
            backgroundColor: 'rgba(0,212,255,0.08)',
            borderWidth: 2,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#00d4ff',
            pointRadius: 4,
            pointHoverRadius: 6
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: 'rgba(5,13,26,0.95)',
                borderColor: 'rgba(0,212,255,0.3)',
                borderWidth: 1,
                titleColor: '#00d4ff',
                bodyColor: '#e2e8f0',
                padding: 10
            }
        },
        scales: {
            x: {
                ticks: { color: '#64748b', font: { size: 10 }, maxRotation: 45 },
                grid: { color: 'rgba(255,255,255,0.04)' }
            },
            y: {
                beginAtZero: true,
                ticks: { color: '#64748b', font: { size: 10 }, stepSize: 1 },
                grid: { color: 'rgba(255,255,255,0.04)' }
            }
        }
    }
});
</script>

<!-- ===================== CHAT JAVASCRIPT ===================== -->
<script>
(function(){

    /* ---- Config ---- */
    const ROLE       = "<?php echo $_SESSION['role']; ?>";
    const MY_ID      = <?php echo (int)$_SESSION['user_id']; ?>;
    const ADMIN_ID   = 1;
    const POLL_MS    = 3000;   // fetch new messages every 3s

    /* ---- State ---- */
    let lastMsgId      = 0;
    let currentTarget  = (ROLE === "police") ? null : ADMIN_ID;  // citizen always talks to admin
    let pollTimer      = null;
    let chatOpen       = true;

    /* ---- Elements ---- */
    const chatMessages = document.getElementById("chatMessages");
    const chatInput    = document.getElementById("chatInput");
    const chatSendBtn  = document.getElementById("chatSendBtn");
    const chatEmpty    = document.getElementById("chatEmpty");
    const toggleBtn    = document.getElementById("chatToggleBtn");
    const toggleIcon   = document.getElementById("chatToggleIcon");
    const collapsible  = document.getElementById("chatCollapsible");

    /* ---- Collapse toggle ---- */
    toggleBtn.addEventListener("click", function(){
        chatOpen = !chatOpen;
        collapsible.classList.toggle("collapsed", !chatOpen);
        toggleIcon.classList.toggle("open", chatOpen);
    });

    /* ---- Police: select user tab ---- */
    window.selectChatUser = function(btn, uid){
        document.querySelectorAll(".chat-user-tab").forEach(t => t.classList.remove("active"));
        btn.classList.add("active");
        currentTarget = uid;
        lastMsgId = 0;
        clearMessages();
        fetchMessages();
    };

    /* ---- Auto-start for citizen ---- */
    if(ROLE !== "police"){
        fetchMessages();
        startPolling();
    } else {
        // Police: auto-select first tab if exists
        const firstTab = document.querySelector(".chat-user-tab");
        if(firstTab){
            currentTarget = parseInt(firstTab.dataset.uid);
            fetchMessages();
            startPolling();
        }
    }

    /* ---- Send on button click ---- */
    chatSendBtn.addEventListener("click", sendMessage);

    /* ---- Send on Enter ---- */
    chatInput.addEventListener("keydown", function(e){
        if(e.key === "Enter" && !e.shiftKey){
            e.preventDefault();
            sendMessage();
        }
    });

    /* =====================
       SEND MESSAGE
    ===================== */
    function sendMessage(){
        const msg = chatInput.value.trim();
        if(!msg || !currentTarget) return;

        chatSendBtn.disabled = true;
        chatInput.disabled   = true;

        const fd = new FormData();
        fd.append("message", msg);
        if(ROLE === "police") fd.append("receiver_id", currentTarget);

        fetch("chat_api.php?action=send", {
            method: "POST",
            body: fd
        })
        .then(r => r.json())
        .then(data => {
            if(data.success){
                chatInput.value = "";
                fetchMessages(); // instant refresh
            }
        })
        .catch(err => console.error("Send error:", err))
        .finally(() => {
            chatSendBtn.disabled = false;
            chatInput.disabled   = false;
            chatInput.focus();
        });
    }

    /* =====================
       FETCH MESSAGES
    ===================== */
    function fetchMessages(){
        if(!currentTarget) return;

        const url = `chat_api.php?action=fetch&last_id=${lastMsgId}&target_id=${currentTarget}`;

        fetch(url)
        .then(r => r.json())
        .then(data => {
            if(data.messages && data.messages.length > 0){
                chatEmpty.style.display = "none";
                data.messages.forEach(m => appendBubble(m));
                lastMsgId = data.messages[data.messages.length - 1].id;
                scrollToBottom();
            }
        })
        .catch(err => console.error("Fetch error:", err));
    }

    /* =====================
       RENDER BUBBLE
    ===================== */
    function appendBubble(m){
        const isMine = parseInt(m.sender_id) === MY_ID;
        const div    = document.createElement("div");
        div.className = "chat-bubble " + (isMine ? "mine" : "theirs");

        const time = formatTime(m.sent_at);

        div.innerHTML =
            `<div class="bubble-sender">${escapeHtml(m.sender_name)}</div>` +
            `<div class="bubble-text">${escapeHtml(m.message)}</div>` +
            `<div class="bubble-time">${time}</div>`;

        chatMessages.appendChild(div);
    }

    /* =====================
       HELPERS
    ===================== */
    function clearMessages(){
        chatMessages.innerHTML = '';
        chatEmpty.style.display = "block";
        chatMessages.appendChild(chatEmpty);
    }

    function scrollToBottom(){
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function startPolling(){
        clearInterval(pollTimer);
        pollTimer = setInterval(function(){
            if(chatOpen && currentTarget) fetchMessages();
        }, POLL_MS);
    }

    function formatTime(ts){
        if(!ts) return "";
        const d = new Date(ts.replace(" ", "T"));
        if(isNaN(d)) return ts;
        const now = new Date();
        const sameDay = d.toDateString() === now.toDateString();
        if(sameDay){
            return d.toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'});
        }
        return d.toLocaleDateString([], {month:'short', day:'numeric'}) + " " +
               d.toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'});
    }

    function escapeHtml(str){
        const div = document.createElement("div");
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

})();
</script>

</body>
</html>