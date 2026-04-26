<?php
session_start();
include "db.php";
include "classifier.php"; // AI categorization

// ✅ Check login
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ✅ Sanitize input
$location    = trim($_POST['location']);
$description = trim($_POST['description']);
$user_type   = isset($_POST['type']) ? $_POST['type'] : "";

// ✅ Basic validation
if(empty($location) || empty($description)){
    die("❌ All fields are required!");
}

// =====================================================
// 🤖 SMART CATEGORY (AI-lite)
// =====================================================
if(empty($user_type)){
    $type = classifyComplaint($description);
}else{
    $type = $user_type;
}

// =====================================================
// 🚨 FRAUD / FAKE DETECTION (Simple Anomaly Logic)
// =====================================================

// Count complaints by same user in last 1 hour
$fraud_check = $conn->prepare("
    SELECT COUNT(*) as total 
    FROM complaints 
    WHERE user_id = ? 
    AND created_at >= (NOW() - INTERVAL 1 HOUR)
");

$fraud_check->bind_param("i", $user_id);
$fraud_check->execute();
$result = $fraud_check->get_result()->fetch_assoc();

$is_suspicious = 0;

if($result['total'] >= 5){
    $is_suspicious = 1; // mark as suspicious
}

// =====================================================
// 📍 LOCATION (SAFE)
// =====================================================
$lat = (!empty($_POST['latitude'])) ? floatval($_POST['latitude']) : NULL;
$lng = (!empty($_POST['longitude'])) ? floatval($_POST['longitude']) : NULL;

// =====================================================
// 📁 FILE UPLOAD (SECURE)
// =====================================================
$fileName = "";

if(isset($_FILES['file']) && $_FILES['file']['name'] != ""){

    $allowedTypes = ['jpg','jpeg','png','pdf'];
    $fileExt = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

    if(!in_array($fileExt, $allowedTypes)){
        die("❌ Only JPG, PNG, PDF allowed!");
    }

    if($_FILES['file']['size'] > 5 * 1024 * 1024){
        die("❌ Max file size is 5MB!");
    }

    $fileName = time() . "_" . basename($_FILES['file']['name']);
    $target = "uploads/" . $fileName;

    if(!move_uploaded_file($_FILES['file']['tmp_name'], $target)){
        die("❌ File upload failed!");
    }
}

// =====================================================
// 💾 INSERT DATA (PREPARED STATEMENT)
// =====================================================
$stmt = $conn->prepare("
    INSERT INTO complaints 
    (user_id, subject, location, description, file, latitude, longitude, is_suspicious, status, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())
");

$stmt->bind_param(
    "issssdii",
    $user_id,
    $type,
    $location,
    $description,
    $fileName,
    $lat,
    $lng,
    $is_suspicious
);

// =====================================================
// 🚀 EXECUTE
// =====================================================
if($stmt->execute()){
    header("Location: dashboard.php");
    exit();
}else{
    echo "❌ Error: " . $stmt->error;
}

// Cleanup
$stmt->close();
$conn->close();
?>