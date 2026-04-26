<?php
session_start();
include "db.php";

if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit();
}

$threshold = 3;

$sql = "
SELECT user_id, COUNT(*) as total, DATE(created_at) as date
FROM complaints
GROUP BY user_id, DATE(created_at)
HAVING total > $threshold
ORDER BY total DESC
";
$result = mysqli_query($conn, $sql);
$totalSuspicious = mysqli_num_rows($result);
$rows = [];
while($r = mysqli_fetch_assoc($result)) $rows[] = $r;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Fraud Detection — e-Seva</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
:root{
    --bg:#0a0c12; --surface:#12151f; --border:#1e2235;
    --accent:#4f6ef7; --accent2:#7c3aed;
    --danger:#ef4444; --warning:#f59e0b; --success:#10b981;
    --text:#e8eaf0; --muted:#6b7280; --card:#161929;
}
*{ margin:0; padding:0; box-sizing:border-box; }
body{ font-family:'DM Sans',sans-serif; background:var(--bg); color:var(--text); min-height:100vh; }

.topbar{ display:flex; align-items:center; justify-content:space-between; padding:18px 40px; background:var(--surface); border-bottom:1px solid var(--border); }
.brand{ display:flex; align-items:center; gap:12px; font-family:'Syne',sans-serif; font-weight:800; font-size:1.2rem; }
.brand-icon{ width:36px; height:36px; background:linear-gradient(135deg,var(--accent),var(--accent2)); border-radius:8px; display:grid; place-items:center; }
.btn{ display:inline-flex; align-items:center; gap:6px; padding:8px 18px; border-radius:8px; font-size:0.85rem; font-weight:500; cursor:pointer; text-decoration:none; transition:all .2s; border:1px solid var(--border); background:transparent; color:var(--text); }
.btn:hover{ border-color:var(--accent); color:var(--accent); }

.hero{ padding:50px 40px 30px; }
.hero-label{ font-size:0.75rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; color:var(--danger); margin-bottom:10px; }
.hero-title{ font-family:'Syne',sans-serif; font-size:2.4rem; font-weight:800; background:linear-gradient(135deg,#fff 40%,var(--danger)); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; margin-bottom:8px; }
.hero-sub{ color:var(--muted); max-width:520px; line-height:1.6; }

.overview{ display:flex; gap:20px; padding:0 40px 30px; flex-wrap:wrap; }
.overview-card{
    flex:1; min-width:180px; background:var(--card); border:1px solid var(--border);
    border-radius:14px; padding:24px; position:relative; overflow:hidden;
}
.overview-card.danger{ border-color:rgba(239,68,68,.3); }
.overview-card.warn  { border-color:rgba(245,158,11,.3); }

.ov-label{ font-size:0.78rem; text-transform:uppercase; letter-spacing:0.08em; color:var(--muted); margin-bottom:8px; }
.ov-value{ font-family:'Syne',sans-serif; font-size:2.2rem; font-weight:800; }
.ov-sub{ font-size:0.8rem; color:var(--muted); margin-top:4px; }

.alert-banner{
    margin:0 40px 30px;
    padding:16px 20px;
    border-radius:12px;
    display:flex; align-items:center; gap:12px;
    font-size:0.88rem;
}
.alert-danger{ background:rgba(239,68,68,.1); border:1px solid rgba(239,68,68,.25); color:#fca5a5; }
.alert-success{ background:rgba(16,185,129,.1); border:1px solid rgba(16,185,129,.25); color:#6ee7b7; }

.table-wrap{ margin:0 40px 40px; background:var(--card); border:1px solid var(--border); border-radius:14px; overflow:hidden; }
.table-toolbar{ padding:16px 20px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; }
.search-box{ display:flex; align-items:center; gap:8px; background:var(--surface); border:1px solid var(--border); border-radius:8px; padding:8px 14px; width:240px; }
.search-box input{ background:none; border:none; outline:none; color:var(--text); font-size:0.85rem; width:100%; font-family:'DM Sans',sans-serif; }
.search-box input::placeholder{ color:var(--muted); }
.toolbar-right{ font-size:0.82rem; color:var(--muted); }

table{ width:100%; border-collapse:collapse; }
thead tr{ background:var(--surface); }
th{ padding:14px 20px; text-align:left; font-family:'Syne',sans-serif; font-size:0.75rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--muted); border-bottom:1px solid var(--border); }
td{ padding:16px 20px; border-bottom:1px solid var(--border); font-size:0.875rem; vertical-align:middle; }
tbody tr:last-child td{ border-bottom:none; }
tbody tr{ transition:background .15s; }
tbody tr:hover{ background:rgba(239,68,68,.03); }

.id-chip{ font-family:'Syne',sans-serif; font-size:0.75rem; font-weight:700; color:var(--muted); background:var(--surface); padding:3px 8px; border-radius:5px; border:1px solid var(--border); }
.count-pill{ font-family:'Syne',sans-serif; font-weight:700; font-size:1rem; }
.high-count{ color:var(--danger); }
.med-count { color:var(--warning); }

.risk-badge{ display:inline-flex; align-items:center; gap:6px; padding:5px 12px; border-radius:20px; font-size:0.78rem; font-weight:600; }
.risk-high{ background:rgba(239,68,68,.15); color:#f87171; }
.risk-med { background:rgba(245,158,11,.15); color:#fbbf24; }

.date-chip{ color:var(--muted); font-size:0.82rem; }

.bar-wrap{ width:100%; height:6px; background:var(--surface); border-radius:3px; margin-top:4px; overflow:hidden; }
.bar-fill{ height:100%; border-radius:3px; }
.fill-high{ background:var(--danger); }
.fill-med { background:var(--warning); }

.empty{ text-align:center; padding:60px 20px; color:var(--muted); }
.empty-icon{ font-size:3rem; margin-bottom:16px; }
.empty-title{ font-family:'Syne',sans-serif; font-size:1.1rem; color:var(--success); margin-bottom:8px; }

.footer{ background:var(--surface); border-top:1px solid var(--border); text-align:center; padding:20px; color:var(--muted); font-size:0.82rem; }

::-webkit-scrollbar{ width:5px; height:5px; }
::-webkit-scrollbar-track{ background:var(--bg); }
::-webkit-scrollbar-thumb{ background:var(--border); border-radius:4px; }
</style>
</head>
<body>

<div class="topbar">
    <div class="brand"><div class="brand-icon">🚔</div>e-Seva Admin</div>
    <a href="admin_dashboard.php" class="btn">← Dashboard</a>
</div>

<div class="hero">
    <div class="hero-label">🚨 Anomaly Detection Engine</div>
    <div class="hero-title">Fraud Detection</div>
    <div class="hero-sub">Users flagged for submitting more than <?php echo $threshold; ?> complaints per day are automatically surfaced for review.</div>
</div>

<?php $maxComplaints = !empty($rows) ? max(array_column($rows,'total')) : 1; ?>

<div class="overview">
    <div class="overview-card danger">
        <div class="ov-label">Suspicious Users</div>
        <div class="ov-value" style="color:var(--danger)"><?php echo $totalSuspicious; ?></div>
        <div class="ov-sub">Flagged accounts</div>
    </div>
    <div class="overview-card warn">
        <div class="ov-label">Daily Threshold</div>
        <div class="ov-value" style="color:var(--warning)"><?php echo $threshold; ?>+</div>
        <div class="ov-sub">Complaints / day triggers alert</div>
    </div>
    <div class="overview-card">
        <div class="ov-label">High Risk</div>
        <div class="ov-value" style="color:var(--danger)"><?php echo count(array_filter($rows,fn($r)=>$r['total']>=6)); ?></div>
        <div class="ov-sub">≥ 6 complaints / day</div>
    </div>
    <div class="overview-card">
        <div class="ov-label">Medium Risk</div>
        <div class="ov-value" style="color:var(--warning)"><?php echo count(array_filter($rows,fn($r)=>$r['total']<6)); ?></div>
        <div class="ov-sub">4–5 complaints / day</div>
    </div>
</div>

<?php if($totalSuspicious>0): ?>
<div class="alert-banner alert-danger">
    ⚠️ &nbsp;<strong><?php echo $totalSuspicious; ?> account(s)</strong> detected with abnormal complaint submission frequency. Review and take action.
</div>
<?php else: ?>
<div class="alert-banner alert-success">
    ✅ &nbsp;No suspicious users detected. All complaint patterns appear normal.
</div>
<?php endif; ?>

<div class="table-wrap">
    <div class="table-toolbar">
        <div class="search-box">
            <span style="color:var(--muted)">⌕</span>
            <input type="text" id="searchInput" placeholder="Search by User ID or date…">
        </div>
        <div class="toolbar-right"><?php echo $totalSuspicious; ?> flagged record(s)</div>
    </div>

    <div style="overflow-x:auto;">
    <table id="fraudTable">
    <thead>
    <tr>
        <th>User ID</th>
        <th>Complaint Count</th>
        <th>Volume</th>
        <th>Date</th>
        <th>Risk Level</th>
    </tr>
    </thead>
    <tbody>
    <?php if(!empty($rows)): foreach($rows as $row):
        $isHigh   = $row['total'] >= 6;
        $countCls = $isHigh ? 'high-count' : 'med-count';
        $riskCls  = $isHigh ? 'risk-high' : 'risk-med';
        $riskLbl  = $isHigh ? '🔴 High Risk' : '🟠 Medium Risk';
        $fillCls  = $isHigh ? 'fill-high' : 'fill-med';
        $pct      = round(($row['total']/$maxComplaints)*100);
    ?>
    <tr>
        <td><span class="id-chip"><?php echo htmlspecialchars($row['user_id']); ?></span></td>
        <td><span class="count-pill <?php echo $countCls; ?>"><?php echo $row['total']; ?></span></td>
        <td style="width:140px">
            <div class="bar-wrap"><div class="bar-fill <?php echo $fillCls; ?>" style="width:<?php echo $pct; ?>%"></div></div>
            <div style="font-size:0.72rem;color:var(--muted);margin-top:3px"><?php echo $pct; ?>% of peak</div>
        </td>
        <td><span class="date-chip">📅 <?php echo $row['date']; ?></span></td>
        <td><span class="risk-badge <?php echo $riskCls; ?>"><?php echo $riskLbl; ?></span></td>
    </tr>
    <?php endforeach; else: ?>
    <tr><td colspan="5">
        <div class="empty">
            <div class="empty-icon">🛡️</div>
            <div class="empty-title">All Clear</div>
            <div>No suspicious activity detected.</div>
        </div>
    </td></tr>
    <?php endif; ?>
    </tbody>
    </table>
    </div>
</div>

<div class="footer">© 2026 Cop Friendly e-Seva &nbsp;·&nbsp; Smart Fraud Detection</div>

<script>
document.getElementById('searchInput').addEventListener('input',function(){
    const q=this.value.toLowerCase();
    document.querySelectorAll('#fraudTable tbody tr').forEach(r=>{
        r.style.display=r.textContent.toLowerCase().includes(q)?'':'none';
    });
});
</script>
</body>
</html>