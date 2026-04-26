<?php

function isSuspicious($conn, $user_id, $description, $location){

    $flag = 0;

    /* 🔴 RULE 1: Too many complaints in 10 minutes */
    $query = "SELECT COUNT(*) as total FROM complaints 
              WHERE user_id = '$user_id' 
              AND created_at >= NOW() - INTERVAL 10 MINUTE";

    $res = mysqli_query($conn, $query);
    $data = mysqli_fetch_assoc($res);

    if($data['total'] >= 3){
        $flag = 1;
    }

    /* 🔴 RULE 2: Same description repeated */
    $query2 = "SELECT COUNT(*) as total FROM complaints 
               WHERE description = '$description' 
               AND user_id = '$user_id'";

    $res2 = mysqli_query($conn, $query2);
    $data2 = mysqli_fetch_assoc($res2);

    if($data2['total'] >= 2){
        $flag = 1;
    }

    /* 🔴 RULE 3: Very short text (spam) */
    if(strlen($description) < 10){
        $flag = 1;
    }

    /* 🔴 RULE 4: Same location flood */
    $query3 = "SELECT COUNT(*) as total FROM complaints 
               WHERE location = '$location' 
               AND created_at >= NOW() - INTERVAL 5 MINUTE";

    $res3 = mysqli_query($conn, $query3);
    $data3 = mysqli_fetch_assoc($res3);

    if($data3['total'] >= 5){
        $flag = 1;
    }

    return $flag;
}
?>