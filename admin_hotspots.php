<!DOCTYPE html>
<html>
<head>
<title>Crime Hotspots</title>
</head>

<body>

<h2>Hotspot Areas</h2>

<div id="result"></div>

<script>
fetch("api/hotspots.php")
.then(res => res.json())
.then(data => {

    let output = "";

    data.forEach((cluster, index) => {
        output += "<h3>Cluster " + (index+1) + " 🚨</h3>";
        output += "Total Complaints: " + cluster.length + "<br><br>";
    });

    document.getElementById("result").innerHTML = output;
});
</script>

</body>
</html>