<?php
include "db.php";

// Fetch latitude & longitude
$result = mysqli_query($conn, "SELECT latitude, longitude FROM complaints WHERE latitude IS NOT NULL");

$points = [];

while($row = mysqli_fetch_assoc($result)){
    $points[] = [
        'lat' => (float)$row['latitude'],
        'lng' => (float)$row['longitude']
    ];
}
?>

<!DOCTYPE html>
<html>
<head>
<title>🔥 Crime Hotspots Heatmap</title>

<style>
body{
    margin:0;
    font-family:Arial;
    background:linear-gradient(120deg,#0f2027,#203a43,#2c5364);
    color:white;
    text-align:center;
}

h2{
    padding:15px;
}

#map{
    height:85vh;
    width:90%;
    margin:auto;
    border-radius:12px;
    box-shadow:0 5px 20px rgba(0,0,0,0.6);
}
</style>

</head>

<body>

<h2>🔥 Crime Hotspots Heatmap</h2>

<div id="map"></div>

<script>
// PHP data to JS
var points = <?php echo json_encode($points); ?>;

function initMap(){

    var center = {lat:13.0827, lng:80.2707}; // Chennai default

    var map = new google.maps.Map(document.getElementById("map"),{
        zoom:11,
        center:center,
        styles: [
            { elementType: "geometry", stylers: [{ color: "#1d2c4d" }] },
            { elementType: "labels.text.fill", stylers: [{ color: "#8ec3b9" }] },
            { elementType: "labels.text.stroke", stylers: [{ color: "#1a3646" }] }
        ]
    });

    // Convert to heatmap format
    var heatmapData = [];

    points.forEach(function(p){
        var latLng = new google.maps.LatLng(p.lat, p.lng);
        heatmapData.push(latLng);

        // Marker (optional)
        new google.maps.Marker({
            position: latLng,
            map: map
        });
    });

    // Heatmap Layer
    var heatmap = new google.maps.visualization.HeatmapLayer({
        data: heatmapData,
        radius: 30
    });

    heatmap.setMap(map);
}
</script>

<!-- IMPORTANT: Add visualization library -->
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB5MqWmVqQT9r--U_qRzhy7Fn06mG-o4cc" async defer></script>

</body>
</html>