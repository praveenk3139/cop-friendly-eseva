<?php
include "db.php";

$apiKey = getenv('GOOGLE_MAPS_API_KEY') ?: "AIzaSyBtWOQiw_Er8RjAfvx6-x9ut4V0bZ7DNJk";

$result = mysqli_query($conn, "
SELECT complaint_id, subject, description, latitude, longitude, status, priority, escalated, is_suspicious
FROM complaints
WHERE latitude IS NOT NULL AND longitude IS NOT NULL
");

$data = [];
while($row = mysqli_fetch_assoc($result)) $data[] = $row;

$total   = count($data);
$high    = count(array_filter($data, fn($r)=>$r['priority']=='High'));
$medium  = count(array_filter($data, fn($r)=>$r['priority']=='Medium'));
$low     = count(array_filter($data, fn($r)=>$r['priority']=='Low'));
$esc     = count(array_filter($data, fn($r)=>$r['escalated']==1));
$susp    = count(array_filter($data, fn($r)=>$r['is_suspicious']==1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Smart Crime Map — e-Seva</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
:root{
    --bg:#0a0c12; --surface:#12151f; --border:#1e2235;
    --accent:#4f6ef7; --accent2:#7c3aed;
    --danger:#ef4444; --warning:#f59e0b; --success:#10b981;
    --text:#e8eaf0; --muted:#6b7280;
}
*{ margin:0; padding:0; box-sizing:border-box; }
html,body{ height:100%; font-family:'DM Sans',sans-serif; background:var(--bg); color:var(--text); overflow:hidden; }

/* TOPBAR */
.topbar{
    position:fixed; top:0; left:0; right:0; z-index:200;
    display:flex; align-items:center; justify-content:space-between;
    padding:14px 24px; height:60px;
    background:rgba(10,12,18,.92);
    border-bottom:1px solid var(--border);
    backdrop-filter:blur(14px);
}
.brand{ display:flex; align-items:center; gap:10px; font-family:'Syne',sans-serif; font-weight:800; font-size:1.05rem; }
.brand-icon{ width:32px; height:32px; background:linear-gradient(135deg,var(--accent),var(--accent2)); border-radius:7px; display:grid; place-items:center; font-size:0.9rem; }
.nav-btn{
    display:inline-flex; align-items:center; gap:6px;
    padding:7px 14px; border-radius:7px; font-size:0.8rem; font-weight:500;
    cursor:pointer; text-decoration:none; transition:all .2s;
    border:1px solid var(--border); background:rgba(22,25,41,.8); color:var(--text);
    font-family:'DM Sans',sans-serif;
}
.nav-btn:hover{ border-color:var(--accent); color:var(--accent); }
.nav-group{ display:flex; gap:8px; align-items:center; }

#map{ height:100vh; padding-top:60px; }

/* SIDEBAR */
.sidebar{
    position:fixed; left:16px; top:76px; z-index:100;
    width:220px; display:flex; flex-direction:column; gap:8px;
}

.panel{
    background:rgba(18,21,31,.92);
    border:1px solid var(--border);
    border-radius:12px;
    backdrop-filter:blur(12px);
    overflow:hidden;
}

.panel-header{
    padding:10px 14px;
    border-bottom:1px solid var(--border);
    font-family:'Syne',sans-serif;
    font-size:0.7rem; font-weight:700;
    text-transform:uppercase; letter-spacing:0.1em;
    color:var(--muted);
}

/* STATS */
.stat-grid{ display:grid; grid-template-columns:1fr 1fr; gap:1px; background:var(--border); }
.stat-cell{ background:rgba(18,21,31,.95); padding:12px; text-align:center; }
.stat-val{ font-family:'Syne',sans-serif; font-size:1.3rem; font-weight:800; }
.stat-lbl{ font-size:0.68rem; color:var(--muted); margin-top:2px; }

/* FILTER BUTTONS */
.filter-list{ padding:10px; display:flex; flex-direction:column; gap:6px; }
.filter-btn{
    width:100%; padding:8px 12px; border-radius:8px; font-size:0.8rem; font-weight:500;
    cursor:pointer; border:1px solid var(--border); background:rgba(22,25,41,.6);
    color:var(--text); display:flex; align-items:center; gap:8px;
    transition:all .2s; font-family:'DM Sans',sans-serif; text-align:left;
}
.filter-btn:hover{ border-color:var(--accent); background:rgba(79,110,247,.12); }
.filter-btn.active{ border-color:var(--accent); background:rgba(79,110,247,.2); color:#fff; }
.filter-dot{ width:8px; height:8px; border-radius:50%; flex-shrink:0; }

/* LEGEND */
.legend-list{ padding:10px 14px; display:flex; flex-direction:column; gap:8px; }
.legend-item{ display:flex; align-items:center; gap:8px; font-size:0.78rem; color:var(--muted); }

/* LIVE BADGE */
.live-badge{
    display:inline-flex; align-items:center; gap:6px;
    padding:4px 10px; border-radius:20px; font-size:0.72rem; font-weight:600;
    background:rgba(16,185,129,.15); color:#6ee7b7; border:1px solid rgba(16,185,129,.25);
}
.live-dot{ width:6px; height:6px; border-radius:50%; background:#10b981; animation:blink 1.5s ease-in-out infinite; }
@keyframes blink{ 0%,100%{opacity:1} 50%{opacity:0.3} }
</style>
</head>
<body>

<!-- TOPBAR -->
<div class="topbar">
    <div class="brand"><div class="brand-icon">🚔</div>Smart Crime Map</div>
    <div class="nav-group">
        <div class="live-badge"><div class="live-dot"></div>Live Updates</div>
        <button class="nav-btn" onclick="toggleHeat()">🔥 Heatmap</button>
        <a href="admin_dashboard.php" class="nav-btn">← Dashboard</a>
    </div>
</div>

<!-- SIDEBAR -->
<div class="sidebar">

    <!-- Stats -->
    <div class="panel">
        <div class="panel-header">Overview</div>
        <div class="stat-grid">
            <div class="stat-cell"><div class="stat-val"><?php echo $total; ?></div><div class="stat-lbl">Total</div></div>
            <div class="stat-cell"><div class="stat-val" style="color:var(--danger)"><?php echo $high; ?></div><div class="stat-lbl">High</div></div>
            <div class="stat-cell"><div class="stat-val" style="color:var(--warning)"><?php echo $esc; ?></div><div class="stat-lbl">Escalated</div></div>
            <div class="stat-cell"><div class="stat-val" style="color:#f87171"><?php echo $susp; ?></div><div class="stat-lbl">Suspicious</div></div>
        </div>
    </div>

    <!-- Filters -->
    <div class="panel">
        <div class="panel-header">Filter Priority</div>
        <div class="filter-list">
            <button class="filter-btn active" id="f-all" onclick="filterMap('all')"><div class="filter-dot" style="background:var(--accent)"></div>All Complaints</button>
            <button class="filter-btn" id="f-high" onclick="filterMap('High')"><div class="filter-dot" style="background:var(--danger)"></div>High Priority <span style="margin-left:auto;color:var(--muted)"><?php echo $high; ?></span></button>
            <button class="filter-btn" id="f-medium" onclick="filterMap('Medium')"><div class="filter-dot" style="background:var(--warning)"></div>Medium <span style="margin-left:auto;color:var(--muted)"><?php echo $medium; ?></span></button>
            <button class="filter-btn" id="f-low" onclick="filterMap('Low')"><div class="filter-dot" style="background:var(--success)"></div>Low Priority <span style="margin-left:auto;color:var(--muted)"><?php echo $low; ?></span></button>
        </div>
    </div>

    <!-- Legend -->
    <div class="panel">
        <div class="panel-header">Legend</div>
        <div class="legend-list">
            <div class="legend-item"><div class="filter-dot" style="background:var(--danger)"></div>High Priority</div>
            <div class="legend-item"><div class="filter-dot" style="background:var(--warning)"></div>Medium Priority</div>
            <div class="legend-item"><div class="filter-dot" style="background:var(--success)"></div>Low Priority</div>
            <div class="legend-item"><div style="width:8px;height:8px;border:1px solid red;border-radius:50%"></div>Suspicious Zone</div>
            <div class="legend-item">⚡ Blinking = Escalated</div>
        </div>
    </div>

</div>

<div id="map"></div>

<script>
let complaints = <?php echo json_encode($data); ?>;
let map, markers=[], heatmap;

function getColor(p){ return p==="High"?"red":p==="Medium"?"orange":"green"; }

function initMap(){
    map = new google.maps.Map(document.getElementById("map"),{
        zoom:5,
        center:{lat:20.5937,lng:78.9629},
        styles:[
            {elementType:"geometry",stylers:[{color:"#0a0c12"}]},
            {elementType:"labels.text.fill",stylers:[{color:"#6b7280"}]},
            {elementType:"labels.text.stroke",stylers:[{color:"#0a0c12"}]},
            {featureType:"road",elementType:"geometry",stylers:[{color:"#1e2235"}]},
            {featureType:"water",elementType:"geometry",stylers:[{color:"#0f1724"}]},
            {featureType:"poi",stylers:[{visibility:"off"}]},
            {featureType:"transit",stylers:[{visibility:"off"}]}
        ],
        disableDefaultUI:true,
        zoomControl:true,
        zoomControlOptions:{ position:google.maps.ControlPosition.RIGHT_CENTER }
    });
    loadMarkers(complaints);
}

function loadMarkers(data){
    clearMarkers();
    const heatData=[];

    data.forEach(c=>{
        const lat=parseFloat(c.latitude), lng=parseFloat(c.longitude);
        if(isNaN(lat)||isNaN(lng)) return;
        const pos={lat,lng};
        heatData.push(new google.maps.LatLng(lat,lng));

        const marker=new google.maps.Marker({
            position:pos, map,
            icon:{ path:google.maps.SymbolPath.CIRCLE, scale:c.priority==="High"?9:7, fillColor:getColor(c.priority), fillOpacity:0.9, strokeColor:"rgba(0,0,0,0.4)", strokeWeight:1.5 }
        });

        if(c.escalated==1){
            let v=true;
            setInterval(()=>{ marker.setVisible(v=!v); },700);
        }

        if(c.is_suspicious==1){
            new google.maps.Circle({ strokeColor:"#ef4444", fillColor:"#ef4444", fillOpacity:0.1, map, center:pos, radius:400 });
        }

        const info=new google.maps.InfoWindow({
            content:`<div style="font-family:'DM Sans',sans-serif;min-width:180px;padding:4px">
                <div style="font-weight:700;font-size:0.9rem;margin-bottom:8px">#${c.complaint_id} — ${c.subject}</div>
                <div style="color:#666;font-size:0.8rem;margin-bottom:4px">📍 ${c.status}</div>
                <div style="font-size:0.78rem;color:#888">${c.description ? c.description.substring(0,80)+'…' : ''}</div>
                <div style="margin-top:8px;display:inline-block;padding:3px 8px;border-radius:4px;font-size:0.75rem;font-weight:600;
                    background:${c.priority==='High'?'#fee2e2':c.priority==='Medium'?'#fef3c7':'#d1fae5'};
                    color:${c.priority==='High'?'#991b1b':c.priority==='Medium'?'#92400e':'#065f46'}">
                    ${c.priority} Priority
                </div>
            </div>`
        });

        marker.addListener("click",()=>info.open(map,marker));
        markers.push(marker);
    });

    heatmap=new google.maps.visualization.HeatmapLayer({ data:heatData, radius:30, opacity:0.6 });
}

function filterMap(type){
    document.querySelectorAll('.filter-btn').forEach(b=>b.classList.remove('active'));
    const btn = type==='all'?'f-all':type==='High'?'f-high':type==='Medium'?'f-medium':'f-low';
    document.getElementById(btn).classList.add('active');
    loadMarkers(type==='all'?complaints:complaints.filter(c=>c.priority===type));
}

function clearMarkers(){ markers.forEach(m=>m.setMap(null)); markers=[]; }

function toggleHeat(){
    if(!heatmap) return;
    heatmap.setMap(heatmap.getMap()?null:map);
}

setInterval(()=>{
    fetch("map_data.php").then(r=>r.json()).then(d=>{ complaints=d; loadMarkers(complaints); }).catch(()=>{});
}, 10000);
</script>

<script async defer
  src="https://maps.googleapis.com/maps/api/js?key=<?php echo $apiKey; ?>&libraries=visualization&callback=initMap">
</script>

</body>
</html>