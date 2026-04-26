<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Crime Trend Prediction — e-Seva</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
.hero-label{ font-size:0.75rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; color:var(--accent); margin-bottom:10px; }
.hero-title{ font-family:'Syne',sans-serif; font-size:2.4rem; font-weight:800; background:linear-gradient(135deg,#fff 40%,var(--accent)); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; margin-bottom:8px; }
.hero-sub{ color:var(--muted); max-width:500px; line-height:1.6; }

.stats-row{ display:flex; gap:16px; padding:0 40px 30px; flex-wrap:wrap; }
.stat{ background:var(--card); border:1px solid var(--border); border-radius:12px; padding:20px 24px; min-width:160px; }
.stat-label{ font-size:0.75rem; text-transform:uppercase; letter-spacing:0.08em; color:var(--muted); margin-bottom:6px; }
.stat-value{ font-family:'Syne',sans-serif; font-size:1.8rem; font-weight:800; }

.chart-wrap{ margin:0 40px 30px; background:var(--card); border:1px solid var(--border); border-radius:14px; padding:30px; }
.chart-header{ display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:12px; }
.chart-title{ font-family:'Syne',sans-serif; font-size:1.1rem; font-weight:700; }

.legend{ display:flex; gap:20px; flex-wrap:wrap; }
.legend-item{ display:flex; align-items:center; gap:8px; font-size:0.82rem; color:var(--muted); }
.legend-dot{ width:10px; height:10px; border-radius:50%; }
.legend-line{ width:20px; height:2px; border-radius:1px; }
.legend-dashed{ background:repeating-linear-gradient(90deg,var(--warning) 0,var(--warning) 4px,transparent 4px,transparent 8px); }

.canvas-container{ position:relative; height:380px; }

.insights{ margin:0 40px 40px; display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:16px; }
.insight-card{ background:var(--card); border:1px solid var(--border); border-radius:12px; padding:20px; }
.insight-icon{ font-size:1.5rem; margin-bottom:10px; }
.insight-title{ font-family:'Syne',sans-serif; font-weight:700; margin-bottom:6px; }
.insight-body{ font-size:0.85rem; color:var(--muted); line-height:1.6; }

.footer{ background:var(--surface); border-top:1px solid var(--border); text-align:center; padding:20px; color:var(--muted); font-size:0.82rem; }

#loadingState{ text-align:center; padding:60px 20px; color:var(--muted); }
.spinner{ width:32px; height:32px; border:2px solid var(--border); border-top-color:var(--accent); border-radius:50%; animation:spin .8s linear infinite; margin:0 auto 16px; }
@keyframes spin{ to{ transform:rotate(360deg); } }
</style>
</head>
<body>

<div class="topbar">
    <div class="brand"><div class="brand-icon">🚔</div>e-Seva Admin</div>
    <a href="admin_dashboard.php" class="btn">← Dashboard</a>
</div>

<div class="hero">
    <div class="hero-label">📈 Predictive Analytics</div>
    <div class="hero-title">Crime Trend Prediction</div>
    <div class="hero-sub">Linear regression model applied to historical complaint data to surface emerging crime trends and forecast future volume.</div>
</div>

<div class="stats-row" id="statsRow">
    <div class="stat"><div class="stat-label">Loading…</div><div class="stat-value" style="color:var(--muted)">—</div></div>
</div>

<div class="chart-wrap">
    <div class="chart-header">
        <div class="chart-title">Daily Complaint Volume & Regression Trend</div>
        <div class="legend">
            <div class="legend-item"><div class="legend-dot" style="background:var(--accent)"></div>Actual</div>
            <div class="legend-item"><div class="legend-line legend-dashed"></div>Predicted</div>
        </div>
    </div>
    <div id="loadingState"><div class="spinner"></div>Fetching complaint data…</div>
    <div class="canvas-container" id="chartContainer" style="display:none">
        <canvas id="chart"></canvas>
    </div>
</div>

<div class="insights" id="insightsRow" style="display:none">
    <div class="insight-card">
        <div class="insight-icon">📐</div>
        <div class="insight-title">Regression Model</div>
        <div class="insight-body">Ordinary least squares linear regression (y = mx + b) fitted to complaint count per day, indexed by time.</div>
    </div>
    <div class="insight-card">
        <div class="insight-icon" id="trendIcon">📈</div>
        <div class="insight-title">Trend Direction</div>
        <div class="insight-body" id="trendBody">Calculating…</div>
    </div>
    <div class="insight-card">
        <div class="insight-icon">⚡</div>
        <div class="insight-title">Forecast Use</div>
        <div class="insight-body">Use the predicted trend line to pre-position patrol resources before anticipated crime spikes.</div>
    </div>
</div>

<div class="footer">© 2026 Cop Friendly e-Seva &nbsp;·&nbsp; Predictive Analytics Engine</div>

<script>
fetch("trend_data.php")
.then(res => res.json())
.then(data => {
    const labels = data.map(d => d.date);
    const values = data.map(d => +d.total);
    const x = values.map((_,i) => i);
    const n = x.length;

    let sumX=0,sumY=0,sumXY=0,sumXX=0;
    for(let i=0;i<n;i++){ sumX+=x[i]; sumY+=values[i]; sumXY+=x[i]*values[i]; sumXX+=x[i]*x[i]; }
    const m = (n*sumXY - sumX*sumY)/(n*sumXX - sumX*sumX);
    const b = (sumY - m*sumX)/n;
    const trend = x.map(v => +(m*v+b).toFixed(2));

    const total  = values.reduce((a,c)=>a+c,0);
    const peak   = Math.max(...values);
    const avg    = (total/n).toFixed(1);
    const latest = values[values.length-1];

    // Stats
    document.getElementById('statsRow').innerHTML = `
        <div class="stat"><div class="stat-label">Total Complaints</div><div class="stat-value" style="color:var(--accent)">${total}</div></div>
        <div class="stat"><div class="stat-label">Daily Average</div><div class="stat-value" style="color:var(--warning)">${avg}</div></div>
        <div class="stat"><div class="stat-label">Peak Day</div><div class="stat-value" style="color:var(--danger)">${peak}</div></div>
        <div class="stat"><div class="stat-label">Latest</div><div class="stat-value">${latest}</div></div>
        <div class="stat"><div class="stat-label">Days Tracked</div><div class="stat-value">${n}</div></div>
    `;

    // Chart
    document.getElementById('loadingState').style.display='none';
    document.getElementById('chartContainer').style.display='block';
    document.getElementById('insightsRow').style.display='grid';

    const slope = m.toFixed(3);
    if(m>0){
        document.getElementById('trendIcon').textContent='📈';
        document.getElementById('trendBody').textContent=`Rising trend detected (slope: +${slope}/day). Consider increasing patrol coverage in active zones.`;
    } else if(m<0){
        document.getElementById('trendIcon').textContent='📉';
        document.getElementById('trendBody').textContent=`Declining trend detected (slope: ${slope}/day). Current strategies appear to be effective.`;
    } else {
        document.getElementById('trendIcon').textContent='➡️';
        document.getElementById('trendBody').textContent=`Stable complaint volume. No significant upward or downward movement detected.`;
    }

    new Chart(document.getElementById("chart"),{
        type:'line',
        data:{
            labels,
            datasets:[
                {
                    label:"Actual Complaints",
                    data:values,
                    borderColor:'rgba(79,110,247,1)',
                    backgroundColor:'rgba(79,110,247,0.08)',
                    borderWidth:2,
                    pointBackgroundColor:'rgba(79,110,247,1)',
                    pointRadius:4,
                    pointHoverRadius:6,
                    tension:0.3,
                    fill:true
                },
                {
                    label:"Predicted Trend",
                    data:trend,
                    borderColor:'rgba(245,158,11,0.8)',
                    borderDash:[6,4],
                    borderWidth:2,
                    pointRadius:0,
                    tension:0,
                    fill:false
                }
            ]
        },
        options:{
            responsive:true,
            maintainAspectRatio:false,
            interaction:{ mode:'index', intersect:false },
            plugins:{
                legend:{ display:false },
                tooltip:{
                    backgroundColor:'#12151f',
                    borderColor:'#1e2235',
                    borderWidth:1,
                    titleColor:'#e8eaf0',
                    bodyColor:'#9ca3af',
                    padding:12
                }
            },
            scales:{
                x:{
                    grid:{ color:'rgba(30,34,53,0.8)' },
                    ticks:{ color:'#6b7280', font:{ family:'DM Sans', size:11 }, maxTicksLimit:10 },
                    border:{ color:'#1e2235' }
                },
                y:{
                    grid:{ color:'rgba(30,34,53,0.8)' },
                    ticks:{ color:'#6b7280', font:{ family:'DM Sans', size:11 } },
                    border:{ color:'#1e2235' },
                    beginAtZero:true
                }
            }
        }
    });
})
.catch(()=>{
    document.getElementById('loadingState').innerHTML='<div style="color:var(--danger)">⚠ Failed to load trend data. Ensure trend_data.php is accessible.</div>';
});
</script>
</body>
</html>