<?php
include "db.php";
include "kmeans.php";

$result = mysqli_query($conn,"SELECT latitude, longitude FROM complaints WHERE latitude IS NOT NULL");
$points = [];
while($row = mysqli_fetch_assoc($result)){
    $points[] = ['lat'=>(float)$row['latitude'], 'lng'=>(float)$row['longitude']];
}

$k = 3;
$clusters = kmeans($points, $k);

function getCentroid($cluster){
    $lat=$lng=0; $count=count($cluster);
    foreach($cluster as $p){ $lat+=$p['lat']; $lng+=$p['lng']; }
    return ['lat'=>$count?$lat/$count:0, 'lng'=>$count?$lng/$count:0];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Crime Hotspots — e-Seva</title>
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
.btn{ display:inline-flex; align-items:center; gap:6px; padding:8px 18px; border-radius:8px; font-size:0.85rem; font-weight:500; cursor:pointer; text-decoration:none; transition:all .2s; border:1px solid var(--border); background:transparent; color:var(--text); font-family:'DM Sans',sans-serif; }
.btn:hover{ border-color:var(--accent); color:var(--accent); }

.hero{ padding:50px 40px 30px; }
.hero-label{ font-size:0.75rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; color:var(--accent); margin-bottom:10px; }
.hero-title{ font-family:'Syne',sans-serif; font-size:2.4rem; font-weight:800; background:linear-gradient(135deg,#fff 40%,var(--danger)); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; margin-bottom:8px; }
.hero-sub{ color:var(--muted); max-width:500px; line-height:1.6; }

.grid{ display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:24px; padding:0 40px 60px; }

.cluster-card{
    background:var(--card);
    border:1px solid var(--border);
    border-radius:16px;
    padding:28px;
    position:relative;
    overflow:hidden;
    transition:transform .25s,box-shadow .25s;
}
.cluster-card:hover{ transform:translateY(-4px); box-shadow:0 20px 40px rgba(0,0,0,.4); }
.cluster-card::before{
    content:'';
    position:absolute;
    top:0; left:0; right:0;
    height:3px;
}
.card-high::before  { background:linear-gradient(90deg,var(--danger),#ff8c00); }
.card-medium::before{ background:linear-gradient(90deg,var(--warning),#f59e0b88); }
.card-low::before   { background:linear-gradient(90deg,var(--success),#10b98188); }

.card-high  { border-color:rgba(239,68,68,.25); }
.card-medium{ border-color:rgba(245,158,11,.25); }
.card-low   { border-color:rgba(16,185,129,.25); }

.cluster-num{
    font-size:0.72rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em;
    color:var(--muted); margin-bottom:16px; display:flex; align-items:center; gap:6px;
}

.big-count{
    font-family:'Syne',sans-serif; font-size:3.2rem; font-weight:800; line-height:1;
    margin-bottom:4px;
}
.count-high  { color:var(--danger); }
.count-medium{ color:var(--warning); }
.count-low   { color:var(--success); }

.count-label{ color:var(--muted); font-size:0.85rem; margin-bottom:20px; }

.risk-badge{
    display:inline-flex; align-items:center; gap:6px;
    padding:6px 14px; border-radius:20px; font-size:0.82rem; font-weight:600; margin-bottom:20px;
}
.risk-high  { background:rgba(239,68,68,.15);  color:#f87171; }
.risk-medium{ background:rgba(245,158,11,.15); color:#fbbf24; }
.risk-low   { background:rgba(16,185,129,.15); color:#6ee7b7; }

.coords{
    display:flex; gap:10px; flex-wrap:wrap;
}
.coord-chip{
    background:var(--surface); border:1px solid var(--border);
    padding:5px 10px; border-radius:6px; font-size:0.75rem; color:var(--muted);
    font-family:monospace; display:flex; align-items:center; gap:5px;
}

.progress-bar{
    width:100%; height:4px; background:var(--surface); border-radius:2px; margin:16px 0 0; overflow:hidden;
}
.progress-fill{ height:100%; border-radius:2px; transition:width 1s ease; }
.fill-high  { background:var(--danger); }
.fill-medium{ background:var(--warning); }
.fill-low   { background:var(--success); }

.footer{ background:var(--surface); border-top:1px solid var(--border); text-align:center; padding:20px; color:var(--muted); font-size:0.82rem; }
</style>
</head>
<body>

<div class="topbar">
    <div class="brand"><div class="brand-icon">🚔</div>e-Seva Admin</div>
    <a href="admin_dashboard.php" class="btn">← Dashboard</a>
</div>

<div class="hero">
    <div class="hero-label">📍 Geospatial Intelligence</div>
    <div class="hero-title">Crime Hotspot Analysis</div>
    <div class="hero-sub">K-means clustering applied across <?php echo array_sum(array_map('count',$clusters)); ?> geo-tagged complaints to surface high-risk zones.</div>
</div>

<div class="grid">
<?php
$maxCount = max(array_map('count',$clusters)) ?: 1;
foreach($clusters as $i => $cluster){
    $count  = count($cluster);
    $score  = $count * 10;
    $center = getCentroid($cluster);
    $pct    = round(($count/$maxCount)*100);

    if($score>60){      $level="HIGH RISK"; $icon="🚨"; $cc="card-high"; $rc="risk-high";   $nc="count-high";   $fc="fill-high";   }
    elseif($score>30){  $level="MEDIUM RISK"; $icon="⚠️"; $cc="card-medium"; $rc="risk-medium"; $nc="count-medium"; $fc="fill-medium"; }
    else{               $level="LOW RISK"; $icon="✅"; $cc="card-low"; $rc="risk-low";    $nc="count-low";    $fc="fill-low";   }
?>
<div class="cluster-card <?php echo $cc; ?>">
    <div class="cluster-num">⬡ Cluster <?php echo $i+1; ?></div>
    <div class="big-count <?php echo $nc; ?>"><?php echo $count; ?></div>
    <div class="count-label">Complaints in cluster</div>
    <div class="risk-badge <?php echo $rc; ?>"><?php echo $icon." ".$level; ?></div>
    <div class="coords">
        <div class="coord-chip">📍 <?php echo round($center['lat'],5); ?></div>
        <div class="coord-chip">📍 <?php echo round($center['lng'],5); ?></div>
    </div>
    <div class="progress-bar"><div class="progress-fill <?php echo $fc; ?>" style="width:<?php echo $pct; ?>%"></div></div>
</div>
<?php } ?>
</div>

<div class="footer">© 2026 Cop Friendly e-Seva &nbsp;·&nbsp; Smart Policing System</div>
</body>
</html>