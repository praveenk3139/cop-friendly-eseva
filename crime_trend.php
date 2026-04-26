<!DOCTYPE html>
<html>
<head>
<title>📈 Advanced Crime Trend Prediction</title>

<style>
body{
    font-family:Arial;
    background:linear-gradient(120deg,#0f2027,#203a43,#2c5364);
    color:white;
    text-align:center;
}

.container{
    width:90%;
    margin:auto;
}

select, button{
    padding:8px;
    margin:10px;
    border-radius:5px;
    border:none;
}

canvas{
    background:white;
    border-radius:10px;
    margin-top:20px;
}
</style>
</head>

<body>

<div class="container">

<h2>📈 Crime Trend Prediction (Advanced)</h2>

<!-- FILTER -->
<select id="type">
<option value="">All Crimes</option>
<option>Theft</option>
<option>Fraud</option>
<option>Cyber Crime</option>
<option>Harassment</option>
<option>Accident</option>
</select>

<button onclick="loadData()">Analyze</button>

<canvas id="chart" width="900" height="400"></canvas>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
let chart;

function loadData(){

let type = document.getElementById("type").value;

fetch("trend_data.php?type=" + type)
.then(res => res.json())
.then(data => {

    let labels = data.map(d => d.date);
    let values = data.map(d => d.total);

    let x = values.map((_, i) => i);
    let y = values;

    let n = x.length;

    let sumX=0, sumY=0, sumXY=0, sumXX=0;

    for(let i=0;i<n;i++){
        sumX += x[i];
        sumY += y[i];
        sumXY += x[i]*y[i];
        sumXX += x[i]*x[i];
    }

    let m = (n*sumXY - sumX*sumY) / (n*sumXX - sumX*sumX);
    let b = (sumY - m*sumX)/n;

    let trend = x.map(val => m*val + b);

    // 🔥 FUTURE PREDICTION (next 7 days)
    let futureLabels = [];
    let futureValues = [];

    for(let i=1;i<=7;i++){
        let nextX = x.length + i;
        futureLabels.push("Day+" + i);
        futureValues.push(m*nextX + b);
    }

    // Destroy old chart
    if(chart) chart.destroy();

    chart = new Chart(document.getElementById("chart"), {
        type: 'line',
        data: {
            labels: [...labels, ...futureLabels],
            datasets: [
                {
                    label: "Actual Data",
                    data: [...values, ...Array(7).fill(null)],
                    borderWidth:2
                },
                {
                    label: "Trend Line",
                    data: [...trend, ...futureValues],
                    borderDash:[5,5],
                    borderWidth:2
                }
            ]
        }
    });

});
}

// Load default
loadData();
</script>

</body>
</html>