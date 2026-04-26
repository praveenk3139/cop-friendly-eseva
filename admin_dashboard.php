<?php
session_start();
include "db.php";

if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit();
}

function e($str){
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/* QUICK STATS */
$totalUsers      = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM users"))['c'];
$totalComplaints = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM complaints"))['c'];
$pending         = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM complaints WHERE status='Pending'"))['c'];
$resolved        = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM complaints WHERE status='Resolved'"))['c'];
$highPriority    = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM complaints WHERE priority='High'"))['c'];
$inProgress      = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM complaints WHERE status='In Progress'"))['c'];

/* RESOLUTION RATE */
$resRate = $totalComplaints > 0 ? round(($resolved / $totalComplaints) * 100) : 0;

/* RECENT COMPLAINTS */
$recentRes = mysqli_query($conn,"SELECT complaint_id, subject, priority, status, created_at FROM complaints ORDER BY created_at DESC LIMIT 5");
$recent = [];
while($r = mysqli_fetch_assoc($recentRes)) $recent[] = $r;

/* CITIZEN CHAT LIST */
$citizenList = [];
$clRes = mysqli_query($conn,"
    SELECT DISTINCT u.user_id, u.name
    FROM messages m
    JOIN users u ON u.user_id = m.sender_id
    WHERE m.receiver_id = '1'
    ORDER BY u.name ASC
");
while($r = mysqli_fetch_assoc($clRes)) $citizenList[] = $r;

/* UNREAD COUNT */
$unreadCount = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM messages WHERE receiver_id=1 AND is_read=0"))['c'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Control Panel — e-Seva</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">
<style>
/* ═══════════════════════════════════
   DESIGN TOKENS
═══════════════════════════════════ */
:root {
    --bg:          #080a10;
    --bg2:         #0d1017;
    --surface:     #111520;
    --surface2:    #161b28;
    --border:      #1c2133;
    --border2:     #232840;

    --accent:      #4f6ef7;
    --accent-glow: rgba(79,110,247,0.18);
    --accent2:     #7c3aed;
    --teal:        #0ea5e9;
    --cyan:        #06b6d4;
    --emerald:     #10b981;
    --amber:       #f59e0b;
    --rose:        #ef4444;
    --orange:      #f97316;
    --fuchsia:     #d946ef;

    --text:        #e8eaf2;
    --text2:       #a8adc4;
    --muted:       #5a6080;

    --radius-sm:   8px;
    --radius-md:   12px;
    --radius-lg:   16px;
    --radius-xl:   22px;

    --shadow-sm:   0 2px 8px rgba(0,0,0,0.35);
    --shadow-md:   0 8px 24px rgba(0,0,0,0.45);
    --shadow-lg:   0 16px 48px rgba(0,0,0,0.55);
}

/* ═══════════════════════════════════
   RESET & BASE
═══════════════════════════════════ */
*, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
html { scroll-behavior:smooth; }

body {
    font-family: 'DM Sans', sans-serif;
    background: var(--bg);
    color: var(--text);
    min-height: 100vh;
    overflow-x: hidden;
}

/* Subtle grid texture */
body::before {
    content: '';
    position: fixed;
    inset: 0;
    background-image:
        linear-gradient(rgba(79,110,247,0.03) 1px, transparent 1px),
        linear-gradient(90deg, rgba(79,110,247,0.03) 1px, transparent 1px);
    background-size: 40px 40px;
    pointer-events: none;
    z-index: 0;
}

/* ═══════════════════════════════════
   TOPBAR
═══════════════════════════════════ */
.topbar {
    position: sticky;
    top: 0;
    z-index: 200;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 32px;
    height: 62px;
    background: rgba(8,10,16,0.88);
    border-bottom: 1px solid var(--border);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
}

.brand {
    display: flex;
    align-items: center;
    gap: 12px;
}

.brand-logo {
    width: 38px; height: 38px;
    background: linear-gradient(135deg, var(--accent), var(--accent2));
    border-radius: 10px;
    display: grid;
    place-items: center;
    font-size: 1.1rem;
    box-shadow: 0 0 16px rgba(79,110,247,0.35);
    flex-shrink: 0;
}

.brand-name {
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    font-size: 1.05rem;
    letter-spacing: -0.01em;
    color: var(--text);
}

.brand-sub {
    font-size: 0.7rem;
    color: var(--muted);
    font-weight: 400;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    margin-top: 1px;
}

.topbar-right {
    display: flex;
    align-items: center;
    gap: 12px;
}

.topbar-time {
    font-size: 0.78rem;
    color: var(--muted);
    font-variant-numeric: tabular-nums;
    letter-spacing: 0.02em;
}

.admin-pill {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 14px 6px 8px;
    background: var(--surface);
    border: 1px solid var(--border2);
    border-radius: 30px;
    font-size: 0.82rem;
    font-weight: 500;
}

.admin-avatar {
    width: 26px; height: 26px;
    background: linear-gradient(135deg, var(--accent), var(--fuchsia));
    border-radius: 50%;
    display: grid;
    place-items: center;
    font-size: 0.7rem;
    font-weight: 700;
    color: #fff;
}

.logout-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 14px;
    background: rgba(239,68,68,0.1);
    border: 1px solid rgba(239,68,68,0.25);
    color: #f87171;
    border-radius: var(--radius-sm);
    font-size: 0.8rem;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s;
    font-family: 'DM Sans', sans-serif;
}
.logout-btn:hover {
    background: rgba(239,68,68,0.2);
    border-color: rgba(239,68,68,0.5);
}

/* ═══════════════════════════════════
   LAYOUT
═══════════════════════════════════ */
.page {
    max-width: 1340px;
    margin: 0 auto;
    padding: 36px 32px 80px;
    position: relative;
    z-index: 1;
}

/* ═══════════════════════════════════
   PAGE HEADER
═══════════════════════════════════ */
.page-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 32px;
    flex-wrap: wrap;
}

.page-eyebrow {
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    color: var(--accent);
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.page-eyebrow::before {
    content: '';
    width: 20px; height: 2px;
    background: var(--accent);
    border-radius: 1px;
}

.page-title {
    font-family: 'Syne', sans-serif;
    font-size: 2.15rem;
    font-weight: 800;
    letter-spacing: -0.03em;
    line-height: 1.1;
    background: linear-gradient(135deg, #fff 30%, rgba(255,255,255,0.5));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.page-sub {
    margin-top: 6px;
    font-size: 0.88rem;
    color: var(--text2);
    font-weight: 400;
}

.live-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    background: rgba(16,185,129,0.1);
    border: 1px solid rgba(16,185,129,0.25);
    border-radius: 30px;
    font-size: 0.75rem;
    font-weight: 600;
    color: #6ee7b7;
    letter-spacing: 0.03em;
}

.live-dot {
    width: 7px; height: 7px;
    border-radius: 50%;
    background: #10b981;
    animation: livePulse 1.8s ease-in-out infinite;
}

@keyframes livePulse {
    0%,100% { opacity: 1; transform: scale(1); }
    50%     { opacity: 0.4; transform: scale(0.85); }
}

/* ═══════════════════════════════════
   STATS GRID
═══════════════════════════════════ */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
    gap: 16px;
    margin-bottom: 32px;
}

.stat-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 22px 20px 18px;
    position: relative;
    overflow: hidden;
    transition: transform 0.22s, border-color 0.22s;
    animation: fadeUp 0.5s ease both;
}

.stat-card:hover {
    transform: translateY(-3px);
    border-color: var(--border2);
}

/* Glow accent top bar */
.stat-card::before {
    content: '';
    position: absolute;
    top: 0; left: 16px; right: 16px;
    height: 2px;
    border-radius: 0 0 2px 2px;
    background: var(--c, var(--accent));
    opacity: 0.7;
}

.stat-card::after {
    content: '';
    position: absolute;
    top: -30px; right: -20px;
    width: 90px; height: 90px;
    border-radius: 50%;
    background: radial-gradient(circle, var(--c, var(--accent)) 0%, transparent 70%);
    opacity: 0.07;
}

.stat-icon {
    font-size: 1.4rem;
    margin-bottom: 12px;
    display: block;
}

.stat-value {
    font-family: 'Syne', sans-serif;
    font-size: 2.1rem;
    font-weight: 800;
    color: var(--text);
    line-height: 1;
    letter-spacing: -0.03em;
}

.stat-label {
    margin-top: 6px;
    font-size: 0.76rem;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 0.07em;
    font-weight: 500;
}

.stat-sub {
    margin-top: 10px;
    font-size: 0.72rem;
    color: var(--muted);
}

.stat-bar {
    margin-top: 10px;
    height: 3px;
    background: var(--border);
    border-radius: 2px;
    overflow: hidden;
}

.stat-bar-fill {
    height: 100%;
    background: var(--c, var(--accent));
    border-radius: 2px;
    transition: width 1s ease;
}

/* ═══════════════════════════════════
   SECTION LABEL
═══════════════════════════════════ */
.section-label {
    font-family: 'Syne', sans-serif;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    color: var(--muted);
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-label::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--border);
}

/* ═══════════════════════════════════
   FEATURE CARDS GRID
═══════════════════════════════════ */
.cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 18px;
    margin-bottom: 36px;
}

.feature-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 24px;
    display: flex;
    flex-direction: column;
    gap: 0;
    position: relative;
    overflow: hidden;
    transition: transform 0.25s cubic-bezier(.34,1.56,.64,1), border-color 0.2s, box-shadow 0.2s;
    animation: fadeUp 0.5s ease both;
    text-decoration: none;
    color: inherit;
}

.feature-card:hover {
    transform: translateY(-5px);
    border-color: var(--c, var(--border2));
    box-shadow: 0 16px 40px rgba(0,0,0,0.4), 0 0 0 1px var(--c, transparent) inset;
}

/* Colored left stripe */
.feature-card::before {
    content: '';
    position: absolute;
    top: 20px; bottom: 20px;
    left: 0;
    width: 3px;
    background: var(--c, var(--accent));
    border-radius: 0 3px 3px 0;
    opacity: 0;
    transition: opacity 0.2s;
}

.feature-card:hover::before { opacity: 1; }

/* Corner glow */
.feature-card::after {
    content: '';
    position: absolute;
    top: -40px; right: -30px;
    width: 110px; height: 110px;
    border-radius: 50%;
    background: radial-gradient(circle, var(--c, var(--accent)) 0%, transparent 70%);
    opacity: 0;
    transition: opacity 0.3s;
}

.feature-card:hover::after { opacity: 0.12; }

.fc-icon-wrap {
    width: 46px; height: 46px;
    border-radius: 12px;
    background: rgba(255,255,255,0.05);
    border: 1px solid var(--border2);
    display: grid;
    place-items: center;
    font-size: 1.3rem;
    margin-bottom: 16px;
    transition: background 0.2s, border-color 0.2s;
    flex-shrink: 0;
}

.feature-card:hover .fc-icon-wrap {
    background: rgba(var(--cr,79,110,247), 0.12);
    border-color: var(--c, var(--border2));
}

.fc-title {
    font-family: 'Syne', sans-serif;
    font-size: 1rem;
    font-weight: 700;
    color: var(--text);
    margin-bottom: 6px;
    letter-spacing: -0.01em;
}

.fc-desc {
    font-size: 0.82rem;
    color: var(--text2);
    line-height: 1.6;
    flex: 1;
    margin-bottom: 18px;
}

.fc-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: var(--radius-sm);
    background: rgba(255,255,255,0.05);
    border: 1px solid var(--border2);
    color: var(--text2);
    font-size: 0.8rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s;
    align-self: flex-start;
    font-family: 'DM Sans', sans-serif;
}

.feature-card:hover .fc-link {
    background: var(--c, var(--accent));
    border-color: var(--c, var(--accent));
    color: #fff;
}

.fc-link svg {
    width: 14px; height: 14px;
    transition: transform 0.2s;
}
.feature-card:hover .fc-link svg { transform: translateX(3px); }

/* ═══════════════════════════════════
   TWO-COL BOTTOM SECTION
═══════════════════════════════════ */
.bottom-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
}

@media (max-width: 800px) { .bottom-grid { grid-template-columns: 1fr; } }

.panel {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    overflow: hidden;
}

.panel-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid var(--border);
}

.panel-title {
    font-family: 'Syne', sans-serif;
    font-size: 0.85rem;
    font-weight: 700;
    color: var(--text);
    display: flex;
    align-items: center;
    gap: 8px;
}

.panel-link {
    font-size: 0.75rem;
    color: var(--accent);
    text-decoration: none;
    font-weight: 500;
    transition: opacity 0.2s;
}
.panel-link:hover { opacity: 0.7; }

/* RECENT COMPLAINTS TABLE */
.recent-table { width: 100%; border-collapse: collapse; }

.recent-table td {
    padding: 12px 20px;
    font-size: 0.82rem;
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
}

.recent-table tr:last-child td { border-bottom: none; }
.recent-table tr { transition: background 0.15s; }
.recent-table tr:hover td { background: rgba(255,255,255,0.02); }

.rt-id {
    font-family: 'Syne', sans-serif;
    font-size: 0.7rem;
    font-weight: 700;
    color: var(--muted);
    background: var(--surface2);
    padding: 2px 7px;
    border-radius: 4px;
    border: 1px solid var(--border);
    white-space: nowrap;
}

.rt-subject {
    font-weight: 500;
    color: var(--text);
    max-width: 140px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.rt-date {
    color: var(--muted);
    font-size: 0.75rem;
    white-space: nowrap;
}

/* PRIORITY & STATUS BADGES */
.badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 9px;
    border-radius: 20px;
    font-size: 0.72rem;
    font-weight: 600;
    white-space: nowrap;
}
.b-high     { background: rgba(239,68,68,.12);  color: #f87171; }
.b-medium   { background: rgba(245,158,11,.12); color: #fbbf24; }
.b-low      { background: rgba(16,185,129,.12); color: #6ee7b7; }
.b-pending  { background: rgba(90,96,128,.15);  color: #9ca3af; }
.b-progress { background: rgba(245,158,11,.12); color: #fbbf24; }
.b-resolved { background: rgba(16,185,129,.12); color: #6ee7b7; }

/* QUICK ACTIONS PANEL */
.quick-actions { padding: 16px; display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }

.qa-btn {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 14px;
    border-radius: var(--radius-md);
    border: 1px solid var(--border2);
    background: var(--surface2);
    color: var(--text2);
    text-decoration: none;
    font-size: 0.82rem;
    font-weight: 500;
    transition: all 0.2s;
    font-family: 'DM Sans', sans-serif;
}

.qa-btn:hover {
    background: var(--surface);
    border-color: var(--c, var(--border2));
    color: var(--text);
    transform: translateY(-2px);
    box-shadow: var(--shadow-sm);
}

.qa-icon {
    width: 32px; height: 32px;
    border-radius: 8px;
    display: grid;
    place-items: center;
    font-size: 1rem;
    background: rgba(255,255,255,0.05);
    flex-shrink: 0;
    transition: background 0.2s;
}

.qa-btn:hover .qa-icon {
    background: rgba(var(--cr,79,110,247), 0.15);
}

/* ═══════════════════════════════════
   ANIMATIONS
═══════════════════════════════════ */
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(18px); }
    to   { opacity: 1; transform: translateY(0); }
}

.stat-card:nth-child(1){ animation-delay: 0.05s; }
.stat-card:nth-child(2){ animation-delay: 0.10s; }
.stat-card:nth-child(3){ animation-delay: 0.15s; }
.stat-card:nth-child(4){ animation-delay: 0.20s; }
.stat-card:nth-child(5){ animation-delay: 0.25s; }
.stat-card:nth-child(6){ animation-delay: 0.30s; }
.feature-card:nth-child(1){ animation-delay: 0.08s; }
.feature-card:nth-child(2){ animation-delay: 0.13s; }
.feature-card:nth-child(3){ animation-delay: 0.18s; }
.feature-card:nth-child(4){ animation-delay: 0.23s; }
.feature-card:nth-child(5){ animation-delay: 0.28s; }
.feature-card:nth-child(6){ animation-delay: 0.33s; }
.feature-card:nth-child(7){ animation-delay: 0.38s; }
.feature-card:nth-child(8){ animation-delay: 0.43s; }

/* ═══════════════════════════════════
   FOOTER
═══════════════════════════════════ */
.footer {
    margin-top: 60px;
    padding: 20px 32px;
    border-top: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 0.75rem;
    color: var(--muted);
    position: relative;
    z-index: 1;
}

/* ═══════════════════════════════════
   CHAT FAB
═══════════════════════════════════ */
.chat-fab {
    position: fixed;
    bottom: 28px;
    right: 28px;
    z-index: 900;
    width: 56px; height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--emerald), #059669);
    border: none;
    color: #fff;
    font-size: 1.3rem;
    cursor: pointer;
    box-shadow: 0 6px 24px rgba(16,185,129,0.45);
    display: grid;
    place-items: center;
    transition: transform 0.2s, box-shadow 0.2s;
    position: relative;
}

.chat-fab:hover {
    transform: scale(1.08);
    box-shadow: 0 8px 30px rgba(16,185,129,0.55);
}

.chat-badge {
    position: absolute;
    top: -4px; right: -4px;
    background: var(--rose);
    color: #fff;
    font-size: 0.65rem;
    font-weight: 700;
    min-width: 20px; height: 20px;
    border-radius: 10px;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 0 5px;
    border: 2px solid var(--bg);
    font-family: 'Syne', sans-serif;
}

.chat-badge.show { display: flex; }

/* ═══════════════════════════════════
   CHAT WINDOW
═══════════════════════════════════ */
.chat-window {
    position: fixed;
    bottom: 98px;
    right: 28px;
    width: 360px;
    height: 520px;
    background: var(--surface);
    border: 1px solid var(--border2);
    border-radius: var(--radius-xl);
    display: none;
    flex-direction: column;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0,0,0,0.7), 0 0 0 1px rgba(255,255,255,0.04) inset;
    z-index: 899;
    transform-origin: bottom right;
    animation: chatOpen 0.25s cubic-bezier(.34,1.56,.64,1) both;
}

@keyframes chatOpen {
    from { opacity: 0; transform: scale(0.88); }
    to   { opacity: 1; transform: scale(1); }
}

.chat-window.open { display: flex; }

/* Chat header */
.cw-header {
    background: linear-gradient(135deg, #059669, var(--emerald));
    padding: 14px 18px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-shrink: 0;
}

.cw-header-left {
    display: flex;
    align-items: center;
    gap: 10px;
}

.cw-avatar {
    width: 34px; height: 34px;
    background: rgba(255,255,255,0.2);
    border-radius: 50%;
    display: grid;
    place-items: center;
    font-size: 1rem;
}

.cw-title { font-family: 'Syne', sans-serif; font-weight: 700; font-size: 0.92rem; }
.cw-subtitle {
    font-size: 0.7rem;
    opacity: 0.75;
    display: flex;
    align-items: center;
    gap: 5px;
    margin-top: 2px;
}

.cw-online-dot {
    width: 6px; height: 6px;
    background: #fff;
    border-radius: 50%;
    animation: livePulse 1.8s ease-in-out infinite;
}

.cw-close {
    background: rgba(255,255,255,0.15);
    border: none;
    color: #fff;
    width: 28px; height: 28px;
    border-radius: 50%;
    cursor: pointer;
    display: grid;
    place-items: center;
    font-size: 1rem;
    transition: background 0.2s;
    flex-shrink: 0;
}
.cw-close:hover { background: rgba(255,255,255,0.28); }

/* User selector */
.cw-selector {
    padding: 10px 14px;
    background: var(--surface2);
    border-bottom: 1px solid var(--border);
    flex-shrink: 0;
}

.cw-selector label {
    font-size: 0.65rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--muted);
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
}

.cw-select {
    width: 100%;
    padding: 8px 12px;
    background: var(--surface);
    border: 1px solid var(--border2);
    border-radius: var(--radius-sm);
    color: var(--text);
    font-size: 0.82rem;
    outline: none;
    cursor: pointer;
    transition: border-color 0.2s;
    font-family: 'DM Sans', sans-serif;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='7' viewBox='0 0 12 7'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%235a6080' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    padding-right: 32px;
}

.cw-select:focus { border-color: var(--emerald); }
.cw-select option { background: var(--surface); }

.cw-no-users {
    font-size: 0.78rem;
    color: var(--muted);
    text-align: center;
    padding: 4px;
}

/* Messages area */
.cw-messages {
    flex: 1;
    overflow-y: auto;
    padding: 14px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    scroll-behavior: smooth;
}

.cw-messages::-webkit-scrollbar { width: 4px; }
.cw-messages::-webkit-scrollbar-track { background: transparent; }
.cw-messages::-webkit-scrollbar-thumb { background: var(--border2); border-radius: 2px; }

.cw-empty-state {
    margin: auto;
    text-align: center;
    color: var(--muted);
    font-size: 0.82rem;
    line-height: 1.7;
}

.cw-empty-icon { font-size: 2rem; margin-bottom: 10px; display: block; }

/* Bubbles */
.bubble {
    max-width: 82%;
    padding: 9px 13px;
    border-radius: 12px;
    font-size: 0.82rem;
    line-height: 1.5;
    word-break: break-word;
    position: relative;
    animation: bubbleIn 0.18s ease both;
}

@keyframes bubbleIn {
    from { opacity: 0; transform: translateY(6px); }
    to   { opacity: 1; transform: translateY(0); }
}

.bubble.admin-msg {
    background: linear-gradient(135deg, rgba(16,185,129,0.2), rgba(5,150,105,0.15));
    border: 1px solid rgba(16,185,129,0.25);
    align-self: flex-end;
    border-bottom-right-radius: 3px;
    color: var(--text);
}

.bubble.user-msg {
    background: var(--surface2);
    border: 1px solid var(--border2);
    align-self: flex-start;
    border-bottom-left-radius: 3px;
    color: var(--text2);
}

.b-sender {
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 3px;
}
.admin-msg .b-sender { color: rgba(110,231,183,0.7); text-align: right; }
.user-msg  .b-sender { color: rgba(147,197,253,0.7); }

.b-time {
    font-size: 0.65rem;
    color: var(--muted);
    margin-top: 4px;
}
.admin-msg .b-time { text-align: right; }

/* Input bar */
.cw-input-bar {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 14px;
    border-top: 1px solid var(--border);
    background: var(--surface2);
    flex-shrink: 0;
}

.cw-input {
    flex: 1;
    padding: 9px 14px;
    background: var(--surface);
    border: 1px solid var(--border2);
    border-radius: 22px;
    color: var(--text);
    font-size: 0.82rem;
    outline: none;
    transition: border-color 0.2s;
    min-width: 0;
    font-family: 'DM Sans', sans-serif;
}
.cw-input:focus { border-color: rgba(16,185,129,0.5); }
.cw-input::placeholder { color: var(--muted); }

.cw-send {
    width: 36px; height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--emerald), #059669);
    border: none;
    color: #fff;
    cursor: pointer;
    display: grid;
    place-items: center;
    font-size: 0.9rem;
    flex-shrink: 0;
    transition: transform 0.15s, box-shadow 0.15s;
    box-shadow: 0 2px 8px rgba(16,185,129,0.3);
}
.cw-send:hover { transform: scale(1.1); box-shadow: 0 4px 12px rgba(16,185,129,0.45); }
.cw-send:active { transform: scale(0.93); }
.cw-send:disabled { opacity: 0.4; cursor: not-allowed; transform: none; }

/* SCROLLBAR GLOBAL */
::-webkit-scrollbar { width: 5px; height: 5px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: var(--border2); border-radius: 3px; }

/* ═══════════════════════════════════
   RESPONSIVE
═══════════════════════════════════ */
@media (max-width: 640px) {
    .page { padding: 20px 16px 60px; }
    .topbar { padding: 0 16px; }
    .page-title { font-size: 1.6rem; }
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
    .chat-window { width: calc(100vw - 32px); right: 16px; }
    .brand-sub { display: none; }
}
</style>
</head>
<body>

<!-- ═══════════ TOPBAR ═══════════ -->
<header class="topbar">
    <div class="brand">
        <div class="brand-logo">🚔</div>
        <div>
            <div class="brand-name">e-Seva</div>
            <div class="brand-sub">Admin Control Panel</div>
        </div>
    </div>
    <div class="topbar-right">
        <span class="topbar-time" id="topbarClock"></span>
        <div class="admin-pill">
            <div class="admin-avatar">A</div>
            Administrator
        </div>
        <a href="logout.php" class="logout-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
            Logout
        </a>
    </div>
</header>

<!-- ═══════════ PAGE ═══════════ -->
<main class="page">

    <!-- Header -->
    <div class="page-header">
        <div>
            <div class="page-eyebrow">Smart Police Command</div>
            <h1 class="page-title">Control Panel</h1>
            <p class="page-sub">Monitor complaints, detect threats, and dispatch resources in real-time.</p>
        </div>
        <div class="live-badge"><div class="live-dot"></div>System Online</div>
    </div>

    <!-- ═══ STATS ═══ -->
    <div class="stats-grid">

        <div class="stat-card" style="--c:var(--accent)">
            <span class="stat-icon">👥</span>
            <div class="stat-value"><?php echo $totalUsers; ?></div>
            <div class="stat-label">Registered Users</div>
        </div>

        <div class="stat-card" style="--c:var(--teal)">
            <span class="stat-icon">📋</span>
            <div class="stat-value"><?php echo $totalComplaints; ?></div>
            <div class="stat-label">Total Complaints</div>
        </div>

        <div class="stat-card" style="--c:var(--amber)">
            <span class="stat-icon">⏳</span>
            <div class="stat-value"><?php echo $pending; ?></div>
            <div class="stat-label">Pending Review</div>
        </div>

        <div class="stat-card" style="--c:var(--orange)">
            <span class="stat-icon">🔄</span>
            <div class="stat-value"><?php echo $inProgress; ?></div>
            <div class="stat-label">In Progress</div>
        </div>

        <div class="stat-card" style="--c:var(--emerald)">
            <span class="stat-icon">✅</span>
            <div class="stat-value"><?php echo $resolved; ?></div>
            <div class="stat-label">Resolved</div>
        </div>

        <div class="stat-card" style="--c:var(--rose)">
            <span class="stat-icon">🔴</span>
            <div class="stat-value"><?php echo $highPriority; ?></div>
            <div class="stat-label">High Priority</div>
            <div class="stat-bar"><div class="stat-bar-fill" style="width:<?php echo $totalComplaints?round(($highPriority/$totalComplaints)*100):0; ?>%"></div></div>
        </div>

    </div>

    <!-- ═══ FEATURE CARDS ═══ -->
    <div class="section-label">Platform Modules</div>

    <div class="cards-grid">

        <div class="feature-card" style="--c:var(--accent)">
            <div class="fc-icon-wrap">👥</div>
            <div class="fc-title">User Management</div>
            <div class="fc-desc">View all registered citizens and officers. Monitor account roles, activity, and access levels.</div>
            <a href="view_users.php" class="fc-link">
                Open Registry
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        <div class="feature-card" style="--c:var(--teal)">
            <div class="fc-icon-wrap">📋</div>
            <div class="fc-title">Complaint Registry</div>
            <div class="fc-desc">Track, filter, and update all incoming complaints. Update statuses and download PDF reports.</div>
            <a href="view_complaints.php" class="fc-link">
                View Complaints
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        <div class="feature-card" style="--c:var(--rose)">
            <div class="fc-icon-wrap">🔥</div>
            <div class="fc-title">Crime Hotspots</div>
            <div class="fc-desc">K-Means clustering applied to geolocated complaints to surface the highest-density crime zones.</div>
            <a href="hotspots.php" class="fc-link">
                View Hotspots
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        <div class="feature-card" style="--c:var(--cyan)">
            <div class="fc-icon-wrap">📈</div>
            <div class="fc-title">Crime Trend Prediction</div>
            <div class="fc-desc">Linear regression model forecasts complaint volume to help pre-position resources before spikes.</div>
            <a href="trend.php" class="fc-link">
                View Trends
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        <div class="feature-card" style="--c:var(--emerald)">
            <div class="fc-icon-wrap">🤖</div>
            <div class="fc-title">AI Recommendations</div>
            <div class="fc-desc">Smart rule-based engine routes each complaint type to the correct department with urgency scoring.</div>
            <a href="recommendations.php" class="fc-link">
                View Suggestions
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        <div class="feature-card" style="--c:var(--fuchsia)">
            <div class="fc-icon-wrap">🚨</div>
            <div class="fc-title">Fraud Detection</div>
            <div class="fc-desc">Flags users submitting abnormal complaint volumes per day. Scores each flagged account by risk level.</div>
            <a href="fraud.php" class="fc-link">
                View Fraud
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        <div class="feature-card" style="--c:var(--orange)">
            <div class="fc-icon-wrap">🧠</div>
            <div class="fc-title">Smart Risk Zones</div>
            <div class="fc-desc">Combines geospatial clustering with user anomaly detection to pinpoint fraud-embedded crime zones.</div>
            <a href="hotspots_fraud.php" class="fc-link">
                View Risk Zones
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        <div class="feature-card" style="--c:var(--teal)">
            <div class="fc-icon-wrap">🗺️</div>
            <div class="fc-title">Live Crime Map</div>
            <div class="fc-desc">Interactive Google Maps view with priority markers, heatmap overlay, fraud zone circles, and auto-refresh.</div>
            <a href="map_view.php" class="fc-link">
                Open Map
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

    </div>

    <!-- ═══ BOTTOM TWO-COL ═══ -->
    <div class="bottom-grid">

        <!-- Recent Complaints -->
        <div class="panel" style="animation:fadeUp 0.5s 0.35s ease both">
            <div class="panel-header">
                <div class="panel-title">⏱ Recent Complaints</div>
                <a href="view_complaints.php" class="panel-link">View all →</a>
            </div>
            <?php if(!empty($recent)): ?>
            <table class="recent-table">
                <?php foreach($recent as $r):
                    $pc = $r['priority']=='High' ? 'b-high' : ($r['priority']=='Medium' ? 'b-medium' : 'b-low');
                    $sc = $r['status']=='Pending' ? 'b-pending' : ($r['status']=='In Progress' ? 'b-progress' : 'b-resolved');
                ?>
                <tr>
                    <td><span class="rt-id">#<?php echo $r['complaint_id']; ?></span></td>
                    <td><span class="rt-subject"><?php echo e($r['subject']); ?></span></td>
                    <td><span class="badge <?php echo $pc; ?>"><?php echo e($r['priority']); ?></span></td>
                    <td><span class="badge <?php echo $sc; ?>"><?php echo e($r['status']); ?></span></td>
                    <td class="rt-date"><?php echo date('d M', strtotime($r['created_at'])); ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php else: ?>
            <div style="padding:30px;text-align:center;color:var(--muted);font-size:0.82rem">No complaints yet.</div>
            <?php endif; ?>
        </div>

        <!-- Quick Actions -->
        <div class="panel" style="animation:fadeUp 0.5s 0.42s ease both">
            <div class="panel-header">
                <div class="panel-title">⚡ Quick Actions</div>
            </div>
            <div class="quick-actions">
                <a href="view_complaints.php" class="qa-btn" style="--c:var(--teal)">
                    <div class="qa-icon">📋</div>All Complaints
                </a>
                <a href="view_users.php" class="qa-btn" style="--c:var(--accent)">
                    <div class="qa-icon">👥</div>User Registry
                </a>
                <a href="map_view.php" class="qa-btn" style="--c:var(--cyan)">
                    <div class="qa-icon">🗺️</div>Live Map
                </a>
                <a href="fraud.php" class="qa-btn" style="--c:var(--fuchsia)">
                    <div class="qa-icon">🚨</div>Fraud Alerts
                </a>
                <a href="hotspots.php" class="qa-btn" style="--c:var(--rose)">
                    <div class="qa-icon">🔥</div>Hotspot View
                </a>
                <a href="trend.php" class="qa-btn" style="--c:var(--cyan)">
                    <div class="qa-icon">📈</div>Crime Trends
                </a>
                <a href="recommendations.php" class="qa-btn" style="--c:var(--emerald)">
                    <div class="qa-icon">🤖</div>AI Actions
                </a>
                <a href="logout.php" class="qa-btn" style="--c:var(--rose)">
                    <div class="qa-icon">🚪</div>Logout
                </a>
            </div>
        </div>

    </div>

</main>

<!-- ═══════════ FOOTER ═══════════ -->
<footer class="footer">
    <span>© 2026 Cop Friendly e-Seva · Smart Policing System</span>
    <span>Version 2.0 · All systems operational</span>
</footer>

<!-- ═══════════ CHAT FAB ═══════════ -->
<button class="chat-fab" id="chatFab" onclick="toggleChat()" title="Citizen Chat" style="position:fixed;bottom:28px;right:28px;z-index:900">
    💬
    <span class="chat-badge <?php echo $unreadCount>0 ? 'show' : ''; ?>" id="chatBadge"><?php echo $unreadCount>0 ? $unreadCount : ''; ?></span>
</button>

<!-- ═══════════ CHAT WINDOW ═══════════ -->
<div class="chat-window" id="chatWindow">

    <div class="cw-header">
        <div class="cw-header-left">
            <div class="cw-avatar">💬</div>
            <div>
                <div class="cw-title">Citizen Chat</div>
                <div class="cw-subtitle"><div class="cw-online-dot"></div>Live · Admin Portal</div>
            </div>
        </div>
        <button class="cw-close" onclick="toggleChat()">✕</button>
    </div>

    <div class="cw-selector">
        <?php if(!empty($citizenList)): ?>
        <label>Conversation</label>
        <select class="cw-select" id="citizenSelect" onchange="onCitizenChange()">
            <option value="">— Select a citizen —</option>
            <?php foreach($citizenList as $c): ?>
            <option value="<?php echo (int)$c['user_id']; ?>"><?php echo e($c['name']); ?></option>
            <?php endforeach; ?>
        </select>
        <?php else: ?>
        <div class="cw-no-users">📭 No messages from citizens yet</div>
        <?php endif; ?>
    </div>

    <div class="cw-messages" id="cwMessages">
        <div class="cw-empty-state" id="cwEmpty">
            <span class="cw-empty-icon">👆</span>
            Select a citizen above<br>to view their conversation
        </div>
    </div>

    <div class="cw-input-bar">
        <input
            class="cw-input"
            type="text"
            id="cwMsgInput"
            placeholder="Type a reply…"
            maxlength="500"
            autocomplete="off"
            onkeydown="if(event.key==='Enter') cwSend()"
        >
        <button class="cw-send" id="cwSendBtn" onclick="cwSend()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
        </button>
    </div>

</div>

<!-- ═══════════ SCRIPTS ═══════════ -->
<script>
/* ── Clock ── */
(function tickClock(){
    const el = document.getElementById('topbarClock');
    if(el){
        const now = new Date();
        el.textContent = now.toLocaleTimeString([], {hour:'2-digit',minute:'2-digit',second:'2-digit'});
    }
    setTimeout(tickClock, 1000);
})();

/* ── Chat logic ── */
(function(){
    const ADMIN_ID = 1;
    const POLL_MS  = 3000;

    let isOpen      = false;
    let currentUser = null;
    let lastMsgId   = 0;
    let pollTimer   = null;

    const chatWindow = document.getElementById('chatWindow');
    const cwMessages = document.getElementById('cwMessages');
    const cwEmpty    = document.getElementById('cwEmpty');
    const cwInput    = document.getElementById('cwMsgInput');
    const cwSendBtn  = document.getElementById('cwSendBtn');
    const chatBadge  = document.getElementById('chatBadge');

    window.toggleChat = function(){
        isOpen = !isOpen;
        if(isOpen){
            chatWindow.classList.add('open');
        } else {
            chatWindow.classList.remove('open');
        }
        if(isOpen && currentUser) fetchMessages();
    };

    window.onCitizenChange = function(){
        const sel = document.getElementById('citizenSelect');
        if(!sel) return;
        const uid = parseInt(sel.value);
        if(!uid){ currentUser = null; return; }
        currentUser = uid;
        lastMsgId   = 0;
        clearMessages();
        fetchMessages();
        startPolling();
    };

    window.cwSend = function(){
        const msg = cwInput.value.trim();
        if(!msg || !currentUser) return;
        cwSendBtn.disabled = true;
        cwInput.disabled   = true;

        const fd = new FormData();
        fd.append('message',     msg);
        fd.append('receiver_id', currentUser);

        fetch('chat_api.php?action=send', { method:'POST', body:fd })
        .then(r => r.json())
        .then(data => {
            if(data.success){ cwInput.value = ''; fetchMessages(); }
            else { alert('Failed to send.'); }
        })
        .catch(console.error)
        .finally(() => {
            cwSendBtn.disabled = false;
            cwInput.disabled   = false;
            cwInput.focus();
        });
    };

    function fetchMessages(){
        if(!currentUser) return;
        fetch(`chat_api.php?action=fetch&last_id=${lastMsgId}&target_id=${currentUser}`)
        .then(r => r.json())
        .then(data => {
            if(data.messages && data.messages.length > 0){
                cwEmpty.style.display = 'none';
                data.messages.forEach(m => appendBubble(m));
                lastMsgId = data.messages[data.messages.length - 1].id;
                scrollBottom();
            }
        })
        .catch(console.error);
    }

    function appendBubble(m){
        const isAdmin = parseInt(m.sender_id) === ADMIN_ID;
        const div = document.createElement('div');
        div.className = 'bubble ' + (isAdmin ? 'admin-msg' : 'user-msg');
        div.innerHTML =
            `<div class="b-sender">${esc(m.sender_name)}</div>` +
            `<div>${esc(m.message)}</div>` +
            `<div class="b-time">${fmtTime(m.sent_at)}</div>`;
        cwMessages.appendChild(div);
    }

    function clearMessages(){
        cwMessages.innerHTML = '';
        cwEmpty.style.display = 'block';
        cwMessages.appendChild(cwEmpty);
    }

    function scrollBottom(){
        cwMessages.scrollTop = cwMessages.scrollHeight;
    }

    function startPolling(){
        clearInterval(pollTimer);
        pollTimer = setInterval(() => {
            if(isOpen && currentUser) fetchMessages();
        }, POLL_MS);
    }

    function fmtTime(ts){
        if(!ts) return '';
        const d   = new Date(ts.replace(' ','T'));
        if(isNaN(d)) return ts;
        const now = new Date();
        if(d.toDateString() === now.toDateString())
            return d.toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'});
        return d.toLocaleDateString([],{month:'short',day:'numeric'}) + ' ' +
               d.toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'});
    }

    function esc(str){
        const d = document.createElement('div');
        d.appendChild(document.createTextNode(str || ''));
        return d.innerHTML;
    }

    /* Unread badge polling */
    function pollUnread(){
        fetch('chat_api.php?action=unread_count')
        .then(r => r.json())
        .then(data => {
            if(data.count > 0){
                chatBadge.textContent = data.count;
                chatBadge.classList.add('show');
            } else {
                chatBadge.classList.remove('show');
            }
        })
        .catch(()=>{});
    }
    setInterval(pollUnread, 8000);

})();
</script>

</body>
</html>