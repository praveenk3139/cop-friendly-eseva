<?php

function getRecommendation($type){

    $type = strtolower($type);

    if($type == "theft"){
        return "🚨 Increase patrol in the area and alert nearby units.";
    }
    elseif($type == "vehicle theft"){
        return "🚗 Inform traffic police and check CCTV footage.";
    }
    elseif($type == "accident"){
        return "🚑 Notify ambulance and traffic control immediately.";
    }
    elseif($type == "cyber crime"){
        return "💻 Forward case to cyber crime department.";
    }
    elseif($type == "harassment"){
        return "👮 Assign officer and ensure victim safety.";
    }
    elseif($type == "fraud"){
        return "💰 Alert financial investigation team.";
    }
    else{
        return "📌 Review case manually and assign appropriate department.";
    }
}

?>