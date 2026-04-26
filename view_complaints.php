<?php
session_start();
include("db.php");

if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit();
}

function getRecommendation($type){
    switch(strtolower(trim($type))){
        case "theft":         return ["icon"=>"🚓","text"=>"Alert Traffic Police",    "badge"=>"badge-blue"];
        case "cyber crime":   return ["icon"=>"💻","text"=>"Send to Cyber Cell",       "badge"=>"badge-purple"];
        case "harassment":    return ["icon"=>"👮","text"=>"Assign Local Patrol",      "badge"=>"badge-orange"];
        case "fraud":         return ["icon"=>"📊","text"=>"Investigate Financials",   "badge"=>"badge-red"];
        case "accident":      return ["icon"=>"🚑","text"=>"Alert Emergency Response", "badge"=>"badge-red"];
        case "missing person":return ["icon"=>"🔍","text"=>"Start Search Operation",  "badge"=>"badge-yellow"];
        default:              return ["icon"=>"📌","text"=>"General Investigation",    "badge"=>"badge-gray"];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Complaint Registry — e-Seva</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
:root{
    --bg:       #0a0c12;
    --surface:  #12151f;
    --border:   #1e2235;
    --accent:   #4f6ef7;
    --accent2:  #7c3aed;
    --danger:   #ef4444;
    --warning:  #f59e0b;
    --success:  #10b981;
    --text:     #e8eaf0;
    --muted:    #6b7280;
    --card:     #161929;
}

*{ margin:0; padding:0; box-sizing:border-box; }

body{
    font-family:'DM Sans',sans-serif;
    background:var(--bg);
    color:var(--text);
    min-height:100vh;
}

/* ── TOPBAR ── */
.topbar{
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:18px 40px;
    background:var(--surface);
    border-bottom:1px solid var(--border);
    position:sticky;
    top:0;
    z-index:100;
    backdrop-filter:blur(12px);
}

.brand{
    display:flex;
    align-items:center;
    gap:12px;
    font-family:'Syne',sans-serif;
    font-weight:800;
    font-size:1.2rem;
    letter-spacing:0.02em;
}

.brand-icon{
    width:36px; height:36px;
    background:linear-gradient(135deg,var(--accent),var(--accent2));
    border-radius:8px;
    display:grid;
    place-items:center;
    font-size:1rem;
}

.nav-actions{ display:flex; gap:12px; }

.btn{
    display:inline-flex;
    align-items:center;
    gap:6px;
    padding:8px 18px;
    border-radius:8px;
    font-family:'DM Sans',sans-serif;
    font-size:0.85rem;
    font-weight:500;
    cursor:pointer;
    text-decoration:none;
    transition:all .2s;
    border:none;
}

.btn-ghost{
    background:transparent;
    border:1px solid var(--border);
    color:var(--text);
}
.btn-ghost:hover{ border-color:var(--accent); color:var(--accent); }

.btn-primary{
    background:var(--accent);
    color:#fff;
}
.btn-primary:hover{ background:#3b55e0; transform:translateY(-1px); }

/* ── PAGE HEADER ── */
.page-header{
    padding:40px 40px 0;
    display:flex;
    align-items:flex-end;
    justify-content:space-between;
    flex-wrap:wrap;
    gap:20px;
}

.page-title{
    font-family:'Syne',sans-serif;
    font-size:2rem;
    font-weight:800;
    background:linear-gradient(135deg,#fff 40%,var(--accent));
    -webkit-background-clip:text;
    -webkit-text-fill-color:transparent;
    background-clip:text;
}

.page-sub{
    color:var(--muted);
    font-size:0.9rem;
    margin-top:4px;
}

/* ── STATS ROW ── */
.stats{
    display:flex;
    gap:16px;
    padding:30px 40px 0;
    flex-wrap:wrap;
}

.stat-chip{
    background:var(--card);
    border:1px solid var(--border);
    padding:12px 20px;
    border-radius:10px;
    display:flex;
    align-items:center;
    gap:10px;
    font-size:0.85rem;
}

.stat-chip span{ color:var(--muted); }
.stat-chip strong{ font-size:1.1rem; }

/* ── TABLE WRAP ── */
.table-wrap{
    margin:30px 40px 40px;
    background:var(--card);
    border:1px solid var(--border);
    border-radius:14px;
    overflow:hidden;
}

.table-toolbar{
    padding:16px 20px;
    border-bottom:1px solid var(--border);
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    flex-wrap:wrap;
}

.search-box{
    display:flex;
    align-items:center;
    gap:8px;
    background:var(--surface);
    border:1px solid var(--border);
    border-radius:8px;
    padding:8px 14px;
    width:260px;
}

.search-box input{
    background:none;
    border:none;
    outline:none;
    color:var(--text);
    font-family:'DM Sans',sans-serif;
    font-size:0.85rem;
    width:100%;
}

.search-box input::placeholder{ color:var(--muted); }

.filter-group{ display:flex; gap:8px; flex-wrap:wrap; }

.filter-btn{
    padding:6px 14px;
    border-radius:6px;
    font-size:0.8rem;
    font-weight:500;
    cursor:pointer;
    border:1px solid var(--border);
    background:var(--surface);
    color:var(--muted);
    transition:all .2s;
}
.filter-btn:hover,.filter-btn.active{ background:var(--accent); color:#fff; border-color:var(--accent); }

table{
    width:100%;
    border-collapse:collapse;
}

thead tr{
    background:var(--surface);
}

th{
    padding:14px 16px;
    text-align:left;
    font-family:'Syne',sans-serif;
    font-size:0.75rem;
    font-weight:700;
    text-transform:uppercase;
    letter-spacing:0.08em;
    color:var(--muted);
    border-bottom:1px solid var(--border);
    white-space:nowrap;
}

td{
    padding:14px 16px;
    border-bottom:1px solid var(--border);
    font-size:0.875rem;
    vertical-align:middle;
}

tbody tr:last-child td{ border-bottom:none; }

tbody tr{
    transition:background .15s;
}
tbody tr:hover{ background:rgba(79,110,247,0.04); }

/* ── PRIORITY ── */
.priority{
    display:inline-flex;
    align-items:center;
    gap:5px;
    padding:4px 10px;
    border-radius:20px;
    font-size:0.78rem;
    font-weight:600;
}
.p-high   { background:rgba(239,68,68,.15);  color:#ef4444; }
.p-medium { background:rgba(245,158,11,.15); color:#f59e0b; }
.p-low    { background:rgba(16,185,129,.15); color:#10b981; }

/* ── STATUS ── */
.status{
    display:inline-flex;
    align-items:center;
    gap:5px;
    padding:4px 10px;
    border-radius:6px;
    font-size:0.78rem;
    font-weight:500;
}
.s-pending     { background:rgba(107,114,128,.15); color:#9ca3af; }
.s-inprogress  { background:rgba(245,158,11,.15);  color:#f59e0b; }
.s-resolved    { background:rgba(16,185,129,.15);  color:#10b981; }

/* ── RECOMMENDATION BADGE ── */
.rec-badge{
    display:inline-flex;
    align-items:center;
    gap:5px;
    padding:5px 10px;
    border-radius:7px;
    font-size:0.78rem;
    font-weight:500;
    white-space:nowrap;
}
.badge-blue   { background:rgba(79,110,247,.15);  color:#6b8cff; border:1px solid rgba(79,110,247,.2); }
.badge-purple { background:rgba(124,58,237,.15);  color:#a78bfa; border:1px solid rgba(124,58,237,.2); }
.badge-orange { background:rgba(245,158,11,.15);  color:#fbbf24; border:1px solid rgba(245,158,11,.2); }
.badge-red    { background:rgba(239,68,68,.15);   color:#f87171; border:1px solid rgba(239,68,68,.2); }
.badge-yellow { background:rgba(250,204,21,.15);  color:#fde047; border:1px solid rgba(250,204,21,.2); }
.badge-gray   { background:rgba(107,114,128,.15); color:#9ca3af; border:1px solid rgba(107,114,128,.2); }

/* ── EVIDENCE ── */
.evidence-thumb{
    width:52px; height:52px;
    object-fit:cover;
    border-radius:6px;
    border:1px solid var(--border);
    cursor:pointer;
    transition:transform .2s;
}
.evidence-thumb:hover{ transform:scale(1.08); }

.no-file{
    color:var(--muted);
    font-size:0.78rem;
}

/* ── STATUS FORM ── */
.status-form{
    display:flex;
    align-items:center;
    gap:6px;
}

.status-select{
    background:var(--surface);
    border:1px solid var(--border);
    color:var(--text);
    border-radius:7px;
    padding:6px 10px;
    font-size:0.8rem;
    font-family:'DM Sans',sans-serif;
    cursor:pointer;
    outline:none;
    transition:border-color .2s;
}
.status-select:focus{ border-color:var(--accent); }

.update-btn{
    padding:6px 12px;
    background:var(--accent);
    color:#fff;
    border:none;
    border-radius:7px;
    font-size:0.8rem;
    cursor:pointer;
    transition:background .2s,transform .1s;
}
.update-btn:hover{ background:#3b55e0; transform:translateY(-1px); }

/* ── ACTION BUTTONS ── */
.action-group{ display:flex; gap:6px; }

.act-btn{
    padding:5px 10px;
    border-radius:6px;
    font-size:0.75rem;
    font-weight:500;
    text-decoration:none;
    transition:all .2s;
    display:inline-flex;
    align-items:center;
    gap:4px;
    border:none;
    cursor:pointer;
}

.act-delete{ background:rgba(239,68,68,.15); color:#f87171; }
.act-delete:hover{ background:#ef4444; color:#fff; }
.act-pdf   { background:rgba(16,185,129,.15); color:#10b981; }
.act-pdf:hover{ background:#10b981; color:#fff; }
.act-dl    { background:rgba(79,110,247,.15); color:#6b8cff; }
.act-dl:hover{ background:var(--accent); color:#fff; }

/* ── ID CHIP ── */
.id-chip{
    font-family:'Syne',sans-serif;
    font-size:0.75rem;
    font-weight:700;
    color:var(--muted);
    background:var(--surface);
    padding:3px 8px;
    border-radius:5px;
    border:1px solid var(--border);
}

/* ── EMPTY STATE ── */
.empty{
    text-align:center;
    padding:60px 20px;
    color:var(--muted);
}
.empty-icon{ font-size:3rem; margin-bottom:16px; }

/* ── IMAGE MODAL ── */
.modal-overlay{
    display:none;
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.85);
    z-index:999;
    place-items:center;
}
.modal-overlay.open{ display:grid; }
.modal-overlay img{
    max-width:90vw;
    max-height:85vh;
    border-radius:10px;
    box-shadow:0 20px 60px rgba(0,0,0,.6);
}
.modal-close{
    position:absolute;
    top:20px; right:20px;
    background:#fff1;
    color:#fff;
    border:none;
    border-radius:50%;
    width:38px; height:38px;
    font-size:1.2rem;
    cursor:pointer;
    display:grid;
    place-items:center;
    backdrop-filter:blur(4px);
}

/* ── SCROLLBAR ── */
::-webkit-scrollbar{ width:5px; height:5px; }
::-webkit-scrollbar-track{ background:var(--bg); }
::-webkit-scrollbar-thumb{ background:var(--border); border-radius:4px; }
</style>
</head>
<body>

<!-- TOPBAR -->
<div class="topbar">
    <div class="brand">
        <div class="brand-icon">🚔</div>
        e-Seva Admin
    </div>
    <div class="nav-actions">
        <a href="admin_dashboard.php" class="btn btn-ghost">← Dashboard</a>
    </div>
</div>

<!-- PAGE HEADER -->
<div class="page-header">
    <div>
        <div class="page-title">Complaint Registry</div>
        <div class="page-sub">All complaints sorted by priority · Smart recommendations enabled</div>
    </div>
</div>

<!-- STATS -->
<?php
$sql = "SELECT COUNT(*) as total,
    SUM(priority='High') as high,
    SUM(priority='Medium') as medium,
    SUM(priority='Low') as low,
    SUM(status='Resolved') as resolved
FROM complaints";
$stats = mysqli_fetch_assoc(mysqli_query($conn,$sql));
?>
<div class="stats">
    <div class="stat-chip"><span>Total</span><strong><?php echo $stats['total']; ?></strong></div>
    <div class="stat-chip" style="border-color:rgba(239,68,68,.3)"><span>High</span><strong style="color:#ef4444"><?php echo $stats['high']; ?></strong></div>
    <div class="stat-chip" style="border-color:rgba(245,158,11,.3)"><span>Medium</span><strong style="color:#f59e0b"><?php echo $stats['medium']; ?></strong></div>
    <div class="stat-chip" style="border-color:rgba(16,185,129,.3)"><span>Low</span><strong style="color:#10b981"><?php echo $stats['low']; ?></strong></div>
    <div class="stat-chip"><span>Resolved</span><strong style="color:#10b981"><?php echo $stats['resolved']; ?></strong></div>
</div>

<!-- TABLE -->
<div class="table-wrap">
    <div class="table-toolbar">
        <div class="search-box">
            <span style="color:var(--muted)">⌕</span>
            <input type="text" id="searchInput" placeholder="Search complaints…">
        </div>
        <div class="filter-group">
            <button class="filter-btn active" onclick="filterTable('all',this)">All</button>
            <button class="filter-btn" onclick="filterTable('High',this)">🔴 High</button>
            <button class="filter-btn" onclick="filterTable('Medium',this)">🟠 Medium</button>
            <button class="filter-btn" onclick="filterTable('Low',this)">🟢 Low</button>
        </div>
    </div>

    <div style="overflow-x:auto;">
    <table id="mainTable">
    <thead>
    <tr>
        <th>ID</th>
        <th>User</th>
        <th>Type</th>
        <th>Location</th>
        <th>Description</th>
        <th>Priority</th>
        <th>Status</th>
        <th>Date</th>
        <th>Evidence</th>
        <th>AI Recommendation</th>
        <th>Update Status</th>
        <th>Actions</th>
    </tr>
    </thead>
    <tbody>
    <?php
    $sql = "SELECT * FROM complaints 
            ORDER BY CASE priority
                WHEN 'High' THEN 1
                WHEN 'Medium' THEN 2
                WHEN 'Low' THEN 3
            END, created_at DESC";
    $result = mysqli_query($conn,$sql);

    if(mysqli_num_rows($result) > 0){
        while($row = mysqli_fetch_assoc($result)){
            $type = htmlspecialchars($row['subject']);
            $rec  = getRecommendation($type);
            $prio = $row['priority'];
            $stat = $row['status'];
            $statusClass = $stat=="Pending" ? "s-pending" : ($stat=="In Progress" ? "s-inprogress" : "s-resolved");
            $prioClass   = $prio=="High"    ? "p-high"    : ($prio=="Medium"     ? "p-medium"    : "p-low");
            $prioIcon    = $prio=="High"    ? "🔴"        : ($prio=="Medium"     ? "🟠"          : "🟢");
    ?>
    <tr data-priority="<?php echo $prio; ?>">
        <td><span class="id-chip">#<?php echo $row['complaint_id']; ?></span></td>
        <td style="font-weight:500"><?php echo htmlspecialchars($row['user_id']); ?></td>
        <td><?php echo $type; ?></td>
        <td style="color:var(--muted);font-size:0.82rem"><?php echo htmlspecialchars($row['location']); ?></td>
        <td style="max-width:180px;font-size:0.82rem;color:var(--muted)"><?php echo mb_strimwidth(htmlspecialchars($row['description']),0,80,"…"); ?></td>
        <td><span class="priority <?php echo $prioClass; ?>"><?php echo $prioIcon." ".$prio; ?></span></td>
        <td><span class="status <?php echo $statusClass; ?>"><?php echo htmlspecialchars($stat); ?></span></td>
        <td style="font-size:0.8rem;color:var(--muted);white-space:nowrap"><?php echo date("d M Y",strtotime($row['created_at'])); ?></td>
        <td>
        <?php if($row['file']!=""): ?>
            <img class="evidence-thumb" src="uploads/<?php echo $row['file']; ?>"
                 onclick="openModal('uploads/<?php echo $row['file']; ?>')">
            <br><a class="act-btn act-dl" style="margin-top:5px" href="uploads/<?php echo $row['file']; ?>" download>⬇ Save</a>
        <?php else: ?>
            <span class="no-file">— None</span>
        <?php endif; ?>
        </td>
        <td><span class="rec-badge <?php echo $rec['badge']; ?>"><?php echo $rec['icon']." ".$rec['text']; ?></span></td>
        <td>
            <form class="status-form" action="update_status.php" method="POST">
                <input type="hidden" name="id" value="<?php echo $row['complaint_id']; ?>">
                <select name="status" class="status-select">
                    <option value="Pending"     <?php if($stat=="Pending")     echo "selected"; ?>>Pending</option>
                    <option value="In Progress" <?php if($stat=="In Progress") echo "selected"; ?>>In Progress</option>
                    <option value="Resolved"    <?php if($stat=="Resolved")    echo "selected"; ?>>Resolved</option>
                </select>
                <button type="submit" class="update-btn">Save</button>
            </form>
        </td>
        <td>
            <div class="action-group">
                <a class="act-btn act-delete" href="delete_complaint.php?id=<?php echo $row['complaint_id']; ?>"
                   onclick="return confirm('Delete complaint #<?php echo $row['complaint_id']; ?>?')">🗑</a>
                <a class="act-btn act-pdf" href="download_complaint.php?id=<?php echo $row['complaint_id']; ?>">PDF</a>
            </div>
        </td>
    </tr>
    <?php }} else { ?>
    <tr><td colspan="12">
        <div class="empty">
            <div class="empty-icon">📭</div>
            <div>No complaints found in the system.</div>
        </div>
    </td></tr>
    <?php } ?>
    </tbody>
    </table>
    </div>
</div>

<!-- IMAGE MODAL -->
<div class="modal-overlay" id="imgModal" onclick="closeModal(event)">
    <button class="modal-close" onclick="document.getElementById('imgModal').classList.remove('open')">✕</button>
    <img id="modalImg" src="" alt="Evidence">
</div>

<script>
function openModal(src){
    document.getElementById('modalImg').src = src;
    document.getElementById('imgModal').classList.add('open');
}
function closeModal(e){
    if(e.target===e.currentTarget) e.currentTarget.classList.remove('open');
}

// Filter by priority
function filterTable(val, btn){
    document.querySelectorAll('.filter-btn').forEach(b=>b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('#mainTable tbody tr').forEach(r=>{
        r.style.display = (val==='all'||r.dataset.priority===val) ? '' : 'none';
    });
}

// Live search
document.getElementById('searchInput').addEventListener('input', function(){
    const q = this.value.toLowerCase();
    document.querySelectorAll('#mainTable tbody tr').forEach(r=>{
        r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});
</script>
</body>
</html>