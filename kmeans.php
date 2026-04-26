<?php

function distance($a, $b){
    return sqrt(pow($a['lat'] - $b['lat'], 2) + pow($a['lng'] - $b['lng'], 2));
}

function kmeans($points, $k = 3, $iterations = 10){

    // Step 1: Random centroids
    $centroids = array_slice($points, 0, $k);

    for($i=0; $i<$iterations; $i++){

        $clusters = [];

        // Step 2: Assign points to nearest centroid
        foreach($points as $p){

            $minDist = PHP_INT_MAX;
            $clusterIndex = 0;

            foreach($centroids as $index => $c){
                $dist = distance($p, $c);

                if($dist < $minDist){
                    $minDist = $dist;
                    $clusterIndex = $index;
                }
            }

            $clusters[$clusterIndex][] = $p;
        }

        // Step 3: Update centroids
        foreach($clusters as $index => $cluster){

            $lat = 0;
            $lng = 0;
            $count = count($cluster);

            foreach($cluster as $p){
                $lat += $p['lat'];
                $lng += $p['lng'];
            }

            $centroids[$index] = [
                'lat' => $lat / $count,
                'lng' => $lng / $count
            ];
        }
    }

    return $clusters;
}
?>