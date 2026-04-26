<?php
session_start();
include "db.php";

// ✅ Check login
if(!isset($_SESSION['user_id'])){
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// ✅ Get form data
$type = mysqli_real_escape_string($conn, $_POST['type']);
$description = mysqli_real_escape_string($conn, $_POST['description']);

/* =========================
   ✅ STEP 4: AUTO PRIORITY FUNCTION
========================= */
function getPriority($text){

    $text = strtolower($text);

    $high = ["murder", "theft", "kidnap", "attack", "rape", "robbery"];
    $medium = ["fraud", "harassment", "threat", "abuse", "scam"];

    foreach($high as $word){
        if(strpos($text, $word) !== false){
            return "High";
        }
    }

    foreach($medium as $word){
        if(strpos($text, $word) !== false){
            return "Medium";
        }
    }

    return "Low";
}

// ✅ Get priority automatically
$priority = getPriority($description);

/* =========================
   ✅ INSERT COMPLAINT
========================= */
$sql = "INSERT INTO complaints 
        (user_id, complaint_type, description, priority, status, created_at)
        VALUES 
        ('$user_id', '$type', '$description', '$priority', 'Pending', NOW())";

if(mysqli_query($conn, $sql)){

    $complaint_id = mysqli_insert_id($conn);

    header("Location: complaint_success.php?id=".$complaint_id);
    exit();

}else{
    echo "Error: " . mysqli_error($conn);
}
?>