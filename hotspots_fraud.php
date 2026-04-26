<?php
include "db.php";
include "kmeans.php";

$result = mysqli_query($conn,"SELECT user_id, latitude, longitude FROM complaints WHERE latitude IS NOT NULL");
$points = [];
$userCounts = [];

while($row = mysqli_fetch_assoc($result)){
    $points[] = ['lat'=>$row['latitude'],'lng'=>$row['longitude'],'user_id'=>$row['user_id']];
    $userCounts[$row['user_id']] = ($userCounts[$row['user_id']] ?? 0) + 1;
}

$suspiciousUsers = [];
foreach($userCounts as $uid => $count){
    if($count > 5) $suspiciousUsers[] = $uid;
}

$cleanPoints = array_map(fn($p)=>['lat'=>$p['lat'],'lng'=>$p['lng']], $points);
$clusters    = kmeans($cleanPoints, 3);

$fraudZones = [];
foreach($clusters as $i => $cluster){
    $count = count($cluster);
    $fraudCount = count(array_filter($points, fn($p)=>in_array($p['user_id'],$suspiciousUsers)));
    if($count > 5 && $fraudCount > 2) $fraudZones[$i] = true;
}

function getCentroid($cluster){
    $lat=$lng=0; $c=count($cluster);
    foreach($cluster as $p){ $lat+=$p['lat']; $lng+=$p['lng']; }
    return ['lat'=>$c?$lat/$c:0,'lng'=>$c?$lng/$c:0];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Crime & Fraud Zones — e-Seva</title>
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

.summary-row{ display:flex; gap:16px; padding:0 40px 30px; flex-wrap:wrap; }
.summary-card{ background:var(--card); border:1px solid var(--border); border-radius:12px; padding:20px 24px; flex:1; min-width:160px; }
.s-label{ font-size:0.75rem; text-transform:uppercase; letter-spacing:0.08em; color:var(--muted); margin-bottom:6px; }
.s-value{ font-family:'Syne',sans-serif; font-size:2rem; font-weight:800; }

.grid{ display:grid; grid-template-columns:repeat(auto-fill,minmax(310px,1fr)); gap:24px; padding:0 40px 60px; }

.zone-card{
    background:var(--card);
    border:1px solid var(--border);
    border-radius:16px;
    padding:28px;
    position:relative;
    overflow:hidden;
    transition:transform .25s, box-shadow .25s;
}
.zone-card:hover{ transform:translateY(-4px); box-shadow:0 20px 40px rgba(0,0,0,.4); }
.zone-card::before{ content:''; position:absolute; top:0; left:0; right:0; height:3px; }
.zone-fraud  ::before{ background:linear-gradient(90deg,var(--danger),#ff8c00); }
.zone-normal ::before{ background:linear-gradient(90deg,var(--success),#10b98188); }
.zone-fraud  { border-color:rgba(239,68,68,.3); }
.zone-normal { border-color:rgba(16,185,129,.2); }

/* FRAUD PULSE RING */
.zone-fraud .pulse-ring{
    position:absolute; top:20px; right:20px;
    width:40px; height:40px; border-radius:50%;
    border:2px solid rgba(239,68,68,.6);
    animation:pulse 2s ease-in-out infinite;
}
.zone-fraud .pulse-ring::after{
    content:''; position:absolute; inset:-6px;
    border-radius:50%; border:2px solid rgba(239,68,68,.3);
    animation:pulse 2s ease-in-out infinite .4s;
}
@keyframes pulse{
    0%,100%{ transform:scale(1); opacity:1; }
    50%{ transform:scale(1.15); opacity:0.6; }
}
.pulse-inner{ position:absolute; inset:0; display:grid; place-items:center; font-size:1.1rem; }

.zone-label{ font-size:0.72rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; color:var(--muted); margin-bottom:14px; }
.zone-count{ font-family:'Syne',sans-serif; font-size:3rem; font-weight:800; line-height:1; margin-bottom:4px; }
.zone-count-sub{ font-size:0.85rem; color:var(--muted); margin-bottom:20px; }
.fraud-count{ color:var(--danger); }
.normal-count{ color:var(--success); }

.zone-status{
    display:inline-flex; align-items:center; gap:8px;
    padding:8px 16px; border-radius:20px; font-size:0.85rem; font-weight:600; margin-bottom:20px;
}
.status-fraud  { background:rgba(239,68,68,.12); color:#f87171; border:1px solid rgba(239,68,68,.25); }
.status-normal { background:rgba(16,185,129,.12); color:#6ee7b7; border:1px solid rgba(16,185,129,.25); }

.coords{ display:flex; gap:8px; flex-wrap:wrap; }
.coord-chip{ background:var(--surface); border:1px solid var(--border); padding:4px 10px; border-radius:6px; font-size:0.72rem; color:var(--muted); font-family:monospace; }

.suspicious-list{ margin-top:16px; padding-top:16px; border-top:1px solid var(--border); }
.sus-label{ font-size:0.75rem; text-transform:uppercase; letter-spacing:0.08em; color:var(--muted); margin-bottom:8px; }
.sus-pill{ display:inline-block; background:rgba(239,68,68,.1); border:1px solid rgba(239,68,68,.2); color:#f87171; padding:3px 8px; border-radius:4px; font-size:0.75rem; margin:2px; font-family:monospace; }

.footer{ background:var(--surface); border-top:1px solid var(--border); text-align:center; padding:20px; color:var(--muted); font-size:0.82rem; }
</style>
</head>
<body>

<div class="topbar">
    <div class="brand"><div class="brand-icon">🚔</div>e-Seva Admin</div>
    <a href="admin_dashboard.php" class="btn">← Dashboard</a>
</div>

<div class="hero">
    <div class="hero-label">🔥 Geospatial + Anomaly Detection</div>
    <div class="hero-title">Crime & Fraud Zone Analysis</div>
    <div class="hero-sub">K-means clustering combined with user behaviour analysis to detect fraud zones — areas with both high complaint density and suspicious user activity.</div>
</div>

<div class="summary-row">
    <div class="summary-card">
        <div class="s-label">Total Clusters</div>
        <div class="s-value"><?php echo count($clusters); ?></div>
    </div>
    <div class="summary-card" style="border-color:rgba(239,68,68,.3)">
        <div class="s-label">Fraud Zones</div>
        <div class="s-value" style="color:var(--danger)"><?php echo count($fraudZones); ?></div>
    </div>
    <div class="summary-card" style="border-color:rgba(16,185,129,.3)">
        <div class="s-label">Normal Zones</div>
        <div class="s-value" style="color:var(--success)"><?php echo count($clusters)-count($fraudZones); ?></div>
    </div>
    <div class="summary-card" style="border-color:rgba(239,68,68,.3)">
        <div class="s-label">Suspicious Users</div>
        <div class="s-value" style="color:var(--warning)"><?php echo count($suspiciousUsers); ?></div>
    </div>
    <div class="summary-card">
        <div class="s-label">Points Clustered</div>
        <div class="s-value"><?php echo count($points); ?></div>
    </div>
</div>

<div class="grid">
<?php foreach($clusters as $i => $cluster):
    $isFraud = isset($fraudZones[$i]);
    $count   = count($cluster);
    $center  = getCentroid($cluster);
    $cls     = $isFraud ? 'zone-fraud' : 'zone-normal';
    $cntCls  = $isFraud ? 'fraud-count' : 'normal-count';
    $stCls   = $isFraud ? 'status-fraud' : 'status-normal';
    $statusTxt = $isFraud ? '🚨 Fraud Zone Detected' : '✅ Normal Zone';
?>
<div class="zone-card <?php echo $cls; ?>">
    <?php if($isFraud): ?>
    <div class="pulse-ring"><div class="pulse-inner">⚠</div></div>
    <?php endif; ?>

    <div class="zone-label">⬡ Cluster <?php echo $i+1; ?></div>
    <div class="zone-count <?php echo $cntCls; ?>"><?php echo $count; ?></div>
    <div class="zone-count-sub">complaints in cluster</div>

    <div class="zone-status <?php echo $stCls; ?>"><?php echo $statusTxt; ?></div>

    <div class="coords">
        <div class="coord-chip">📍 <?php echo round($center['lat'],5); ?></div>
        <div class="coord-chip">📍 <?php echo round($center['lng'],5); ?></div>
    </div>

    <?php if($isFraud && !empty($suspiciousUsers)): ?>
    <div class="suspicious-list">
        <div class="sus-label">Flagged User IDs</div>
        <?php foreach(array_slice($suspiciousUsers,0,5) as $uid): ?>
        <span class="sus-pill"><?php echo htmlspecialchars($uid); ?></span>
        <?php endforeach; ?>
        <?php if(count($suspiciousUsers)>5): ?>
        <span style="font-size:0.75rem;color:var(--muted)">+<?php echo count($suspiciousUsers)-5; ?> more</span>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>
<?php endforeach; ?>
</div>

<div class="footer">© 2026 Cop Friendly e-Seva &nbsp;·&nbsp; Geospatial Fraud Detection</div>
</body>
</html>