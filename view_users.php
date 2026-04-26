<?php
session_start();
include("db.php");

if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Registry — e-Seva</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
:root{
    --bg:#0a0c12; --surface:#12151f; --border:#1e2235;
    --accent:#4f6ef7; --accent2:#7c3aed;
    --success:#10b981; --text:#e8eaf0; --muted:#6b7280; --card:#161929;
}
*{ margin:0; padding:0; box-sizing:border-box; }
body{ font-family:'DM Sans',sans-serif; background:var(--bg); color:var(--text); min-height:100vh; }

.topbar{
    display:flex; align-items:center; justify-content:space-between;
    padding:18px 40px; background:var(--surface); border-bottom:1px solid var(--border);
    position:sticky; top:0; z-index:100;
}
.brand{ display:flex; align-items:center; gap:12px; font-family:'Syne',sans-serif; font-weight:800; font-size:1.2rem; }
.brand-icon{ width:36px; height:36px; background:linear-gradient(135deg,var(--accent),var(--accent2)); border-radius:8px; display:grid; place-items:center; }
.btn{ display:inline-flex; align-items:center; gap:6px; padding:8px 18px; border-radius:8px; font-size:0.85rem; font-weight:500; cursor:pointer; text-decoration:none; transition:all .2s; border:none; font-family:'DM Sans',sans-serif; }
.btn-ghost{ background:transparent; border:1px solid var(--border); color:var(--text); }
.btn-ghost:hover{ border-color:var(--accent); color:var(--accent); }

.page-header{ padding:40px 40px 0; display:flex; align-items:flex-end; justify-content:space-between; flex-wrap:wrap; gap:20px; }
.page-title{ font-family:'Syne',sans-serif; font-size:2rem; font-weight:800; background:linear-gradient(135deg,#fff 40%,var(--accent)); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; }
.page-sub{ color:var(--muted); font-size:0.9rem; margin-top:4px; }

.stats{ display:flex; gap:16px; padding:30px 40px 0; flex-wrap:wrap; }
.stat-chip{ background:var(--card); border:1px solid var(--border); padding:12px 20px; border-radius:10px; display:flex; align-items:center; gap:10px; font-size:0.85rem; }
.stat-chip span{ color:var(--muted); }

.table-wrap{ margin:30px 40px 40px; background:var(--card); border:1px solid var(--border); border-radius:14px; overflow:hidden; }
.table-toolbar{ padding:16px 20px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; }
.search-box{ display:flex; align-items:center; gap:8px; background:var(--surface); border:1px solid var(--border); border-radius:8px; padding:8px 14px; width:260px; }
.search-box input{ background:none; border:none; outline:none; color:var(--text); font-family:'DM Sans',sans-serif; font-size:0.85rem; width:100%; }
.search-box input::placeholder{ color:var(--muted); }

table{ width:100%; border-collapse:collapse; }
thead tr{ background:var(--surface); }
th{ padding:14px 20px; text-align:left; font-family:'Syne',sans-serif; font-size:0.75rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--muted); border-bottom:1px solid var(--border); white-space:nowrap; }
td{ padding:16px 20px; border-bottom:1px solid var(--border); font-size:0.875rem; vertical-align:middle; }
tbody tr:last-child td{ border-bottom:none; }
tbody tr{ transition:background .15s; }
tbody tr:hover{ background:rgba(79,110,247,.04); }

.avatar{
    width:36px; height:36px; border-radius:50%; background:linear-gradient(135deg,var(--accent),var(--accent2));
    display:inline-flex; align-items:center; justify-content:center; font-family:'Syne',sans-serif;
    font-weight:700; font-size:0.85rem; color:#fff; flex-shrink:0;
}

.user-cell{ display:flex; align-items:center; gap:12px; }
.user-name{ font-weight:500; }
.user-id{ font-size:0.75rem; color:var(--muted); }

.email-chip{ color:var(--muted); font-size:0.85rem; }

.role-badge{
    display:inline-flex; align-items:center; gap:5px; padding:4px 12px;
    border-radius:20px; font-size:0.78rem; font-weight:600;
}
.role-admin  { background:rgba(124,58,237,.15); color:#a78bfa; }
.role-user   { background:rgba(79,110,247,.15); color:#6b8cff; }
.role-officer{ background:rgba(16,185,129,.15); color:#10b981; }

.id-chip{ font-family:'Syne',sans-serif; font-size:0.75rem; font-weight:700; color:var(--muted); background:var(--surface); padding:3px 8px; border-radius:5px; border:1px solid var(--border); }

.empty{ text-align:center; padding:60px 20px; color:var(--muted); }
.empty-icon{ font-size:3rem; margin-bottom:16px; }

::-webkit-scrollbar{ width:5px; height:5px; }
::-webkit-scrollbar-track{ background:var(--bg); }
::-webkit-scrollbar-thumb{ background:var(--border); border-radius:4px; }
</style>
</head>
<body>

<div class="topbar">
    <div class="brand">
        <div class="brand-icon">🚔</div>
        e-Seva Admin
    </div>
    <a href="admin_dashboard.php" class="btn btn-ghost">← Dashboard</a>
</div>

<?php
$total       = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM users"))['c'];
$admins      = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM users WHERE role='admin'"))['c'];
$officers    = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM users WHERE role='officer'"))['c'];
$citizens    = $total - $admins - $officers;
?>

<div class="page-header">
    <div>
        <div class="page-title">User Registry</div>
        <div class="page-sub">All registered accounts across the platform</div>
    </div>
</div>

<div class="stats">
    <div class="stat-chip"><span>Total Users</span><strong><?php echo $total; ?></strong></div>
    <div class="stat-chip" style="border-color:rgba(124,58,237,.3)"><span>Admins</span><strong style="color:#a78bfa"><?php echo $admins; ?></strong></div>
    <div class="stat-chip" style="border-color:rgba(16,185,129,.3)"><span>Officers</span><strong style="color:#10b981"><?php echo $officers; ?></strong></div>
    <div class="stat-chip" style="border-color:rgba(79,110,247,.3)"><span>Citizens</span><strong style="color:#6b8cff"><?php echo $citizens; ?></strong></div>
</div>

<div class="table-wrap">
    <div class="table-toolbar">
        <div class="search-box">
            <span style="color:var(--muted)">⌕</span>
            <input type="text" id="searchInput" placeholder="Search users…">
        </div>
    </div>

    <div style="overflow-x:auto;">
    <table id="userTable">
    <thead>
    <tr>
        <th>ID</th>
        <th>User</th>
        <th>Email</th>
        <th>Role</th>
    </tr>
    </thead>
    <tbody>
    <?php
    $result = mysqli_query($conn,"SELECT * FROM users ORDER BY user_id ASC");
    if(mysqli_num_rows($result)>0){
        while($row=mysqli_fetch_assoc($result)){
            $initial = strtoupper(substr($row['name'],0,1));
            $role    = strtolower($row['role']);
            $roleClass= $role=='admin' ? 'role-admin' : ($role=='officer' ? 'role-officer' : 'role-user');
            $roleIcon = $role=='admin' ? '🛡' : ($role=='officer' ? '👮' : '👤');
    ?>
    <tr>
        <td><span class="id-chip">#<?php echo $row['user_id']; ?></span></td>
        <td>
            <div class="user-cell">
                <div class="avatar"><?php echo $initial; ?></div>
                <div>
                    <div class="user-name"><?php echo htmlspecialchars($row['name']); ?></div>
                </div>
            </div>
        </td>
        <td><span class="email-chip">✉ <?php echo htmlspecialchars($row['email']); ?></span></td>
        <td><span class="role-badge <?php echo $roleClass; ?>"><?php echo $roleIcon." ".ucfirst($row['role']); ?></span></td>
    </tr>
    <?php }} else { ?>
    <tr><td colspan="4"><div class="empty"><div class="empty-icon">👥</div><div>No users found.</div></div></td></tr>
    <?php } ?>
    </tbody>
    </table>
    </div>
</div>

<script>
document.getElementById('searchInput').addEventListener('input', function(){
    const q=this.value.toLowerCase();
    document.querySelectorAll('#userTable tbody tr').forEach(r=>{
        r.style.display=r.textContent.toLowerCase().includes(q)?'':'none';
    });
});
</script>
</body>
</html>