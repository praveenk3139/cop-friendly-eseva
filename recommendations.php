<?php
session_start();
include("db.php");

if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit();
}

function getRecommendation($type){
    switch(strtolower(trim($type))){
        case "theft":         return ["icon"=>"🚓","action"=>"Alert Traffic Police",    "dept"=>"Traffic Division",    "badge"=>"badge-blue",   "urgency"=>"High"];
        case "cyber crime":   return ["icon"=>"💻","action"=>"Send to Cyber Cell",       "dept"=>"Cyber Crime Unit",    "badge"=>"badge-purple", "urgency"=>"High"];
        case "harassment":    return ["icon"=>"👮","action"=>"Assign Local Patrol",      "dept"=>"Local Station",       "badge"=>"badge-orange", "urgency"=>"Medium"];
        case "fraud":         return ["icon"=>"📊","action"=>"Investigate Financials",   "dept"=>"Economic Crimes",     "badge"=>"badge-red",    "urgency"=>"High"];
        case "accident":      return ["icon"=>"🚑","action"=>"Alert Emergency Response", "dept"=>"Emergency Services",  "badge"=>"badge-red",    "urgency"=>"Critical"];
        case "missing person":return ["icon"=>"🔍","action"=>"Start Search Operation",  "dept"=>"Search & Rescue",     "badge"=>"badge-yellow", "urgency"=>"Critical"];
        default:              return ["icon"=>"📌","action"=>"General Investigation",    "dept"=>"General Crimes",      "badge"=>"badge-gray",   "urgency"=>"Low"];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AI Recommendations — e-Seva</title>
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
.hero-label{ font-size:0.75rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; color:var(--accent2); margin-bottom:10px; }
.hero-title{ font-family:'Syne',sans-serif; font-size:2.4rem; font-weight:800; background:linear-gradient(135deg,#fff 40%,var(--accent2)); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; margin-bottom:8px; }
.hero-sub{ color:var(--muted); max-width:500px; line-height:1.6; }

.table-wrap{ margin:0 40px 40px; background:var(--card); border:1px solid var(--border); border-radius:14px; overflow:hidden; }
.table-toolbar{ padding:16px 20px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; }
.search-box{ display:flex; align-items:center; gap:8px; background:var(--surface); border:1px solid var(--border); border-radius:8px; padding:8px 14px; width:260px; }
.search-box input{ background:none; border:none; outline:none; color:var(--text); font-size:0.85rem; width:100%; font-family:'DM Sans',sans-serif; }
.search-box input::placeholder{ color:var(--muted); }

table{ width:100%; border-collapse:collapse; }
thead tr{ background:var(--surface); }
th{ padding:14px 20px; text-align:left; font-family:'Syne',sans-serif; font-size:0.75rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--muted); border-bottom:1px solid var(--border); }
td{ padding:16px 20px; border-bottom:1px solid var(--border); font-size:0.875rem; vertical-align:middle; }
tbody tr:last-child td{ border-bottom:none; }
tbody tr{ transition:background .15s; }
tbody tr:hover{ background:rgba(124,58,237,.04); }

.type-cell{ display:flex; align-items:center; gap:10px; }
.type-icon{ width:36px; height:36px; border-radius:8px; background:var(--surface); border:1px solid var(--border); display:grid; place-items:center; font-size:1.1rem; flex-shrink:0; }
.type-name{ font-weight:500; }

.action-cell{ display:flex; flex-direction:column; gap:3px; }
.action-primary{ font-weight:500; }
.action-dept{ font-size:0.78rem; color:var(--muted); }

.urgency-badge{ display:inline-flex; align-items:center; gap:4px; padding:4px 10px; border-radius:20px; font-size:0.75rem; font-weight:600; }
.u-critical{ background:rgba(239,68,68,.15); color:#f87171; }
.u-high    { background:rgba(245,158,11,.15); color:#fbbf24; }
.u-medium  { background:rgba(79,110,247,.15); color:#6b8cff; }
.u-low     { background:rgba(107,114,128,.15); color:#9ca3af; }

.badge-blue  { background:rgba(79,110,247,.12); border:1px solid rgba(79,110,247,.25); color:#6b8cff; padding:6px 12px; border-radius:8px; font-size:0.82rem; display:inline-flex; align-items:center; gap:6px; }
.badge-purple{ background:rgba(124,58,237,.12); border:1px solid rgba(124,58,237,.25); color:#a78bfa; padding:6px 12px; border-radius:8px; font-size:0.82rem; display:inline-flex; align-items:center; gap:6px; }
.badge-orange{ background:rgba(245,158,11,.12); border:1px solid rgba(245,158,11,.25); color:#fbbf24; padding:6px 12px; border-radius:8px; font-size:0.82rem; display:inline-flex; align-items:center; gap:6px; }
.badge-red   { background:rgba(239,68,68,.12); border:1px solid rgba(239,68,68,.25); color:#f87171; padding:6px 12px; border-radius:8px; font-size:0.82rem; display:inline-flex; align-items:center; gap:6px; }
.badge-yellow{ background:rgba(250,204,21,.12); border:1px solid rgba(250,204,21,.25); color:#fde047; padding:6px 12px; border-radius:8px; font-size:0.82rem; display:inline-flex; align-items:center; gap:6px; }
.badge-gray  { background:rgba(107,114,128,.12); border:1px solid rgba(107,114,128,.25); color:#9ca3af; padding:6px 12px; border-radius:8px; font-size:0.82rem; display:inline-flex; align-items:center; gap:6px; }

.footer{ background:var(--surface); border-top:1px solid var(--border); text-align:center; padding:20px; color:var(--muted); font-size:0.82rem; }
::-webkit-scrollbar{ width:5px; } ::-webkit-scrollbar-track{ background:var(--bg); } ::-webkit-scrollbar-thumb{ background:var(--border); border-radius:4px; }
</style>
</head>
<body>

<div class="topbar">
    <div class="brand"><div class="brand-icon">🚔</div>e-Seva Admin</div>
    <a href="admin_dashboard.php" class="btn">← Dashboard</a>
</div>

<div class="hero">
    <div class="hero-label">🤖 AI Engine</div>
    <div class="hero-title">Smart Police Recommendations</div>
    <div class="hero-sub">Automated department routing and urgency scoring based on complaint classification.</div>
</div>

<div class="table-wrap">
    <div class="table-toolbar">
        <div class="search-box">
            <span style="color:var(--muted)">⌕</span>
            <input type="text" id="searchInput" placeholder="Search complaints…">
        </div>
    </div>

    <table id="recTable">
    <thead>
    <tr>
        <th>Complaint Type</th>
        <th>Recommended Action</th>
        <th>Routing</th>
        <th>Urgency</th>
    </tr>
    </thead>
    <tbody>
    <?php
    $result = mysqli_query($conn,"SELECT subject FROM complaints");
    while($row = mysqli_fetch_assoc($result)){
        $type = $row['subject'];
        $rec  = getRecommendation($type);
        $uClass = match($rec['urgency']){
            'Critical' => 'u-critical',
            'High'     => 'u-high',
            'Medium'   => 'u-medium',
            default    => 'u-low'
        };
    ?>
    <tr>
        <td>
            <div class="type-cell">
                <div class="type-icon"><?php echo $rec['icon']; ?></div>
                <div class="type-name"><?php echo htmlspecialchars($type); ?></div>
            </div>
        </td>
        <td>
            <div class="action-cell">
                <div class="action-primary"><?php echo $rec['action']; ?></div>
            </div>
        </td>
        <td><span class="<?php echo $rec['badge']; ?>">🏢 <?php echo $rec['dept']; ?></span></td>
        <td><span class="urgency-badge <?php echo $uClass; ?>"><?php echo $rec['urgency']; ?></span></td>
    </tr>
    <?php } ?>
    </tbody>
    </table>
</div>

<div class="footer">© 2026 Cop Friendly e-Seva &nbsp;·&nbsp; AI Recommendations Engine</div>

<script>
document.getElementById('searchInput').addEventListener('input',function(){
    const q=this.value.toLowerCase();
    document.querySelectorAll('#recTable tbody tr').forEach(r=>{
        r.style.display=r.textContent.toLowerCase().includes(q)?'':'none';
    });
});
</script>
</body>
</html>