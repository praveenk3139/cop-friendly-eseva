<?php
session_start();
include "db.php";

if(!isset($_SESSION['user_id'])){
    echo json_encode(["messages"=>[]]);
    exit();
}

$user_id = $_SESSION['user_id'];
$admin_id = 1; // police/admin

/* ======================
   FETCH MESSAGES
====================== */
if(isset($_GET['action']) && $_GET['action']=="fetch"){

    $last_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;

    $res = mysqli_query($conn,"
        SELECT m.*, u.name as sender_name
        FROM messages m
        JOIN users u ON u.user_id = m.sender_id
        WHERE m.id > $last_id
        AND (
            (m.sender_id = '$user_id' AND m.receiver_id = '$admin_id')
            OR
            (m.sender_id = '$admin_id' AND m.receiver_id = '$user_id')
        )
        ORDER BY m.id ASC
    ");

    $messages = [];

    while($row = mysqli_fetch_assoc($res)){
        $messages[] = [
            "id" => $row['id'],
            "sender_id" => $row['sender_id'],
            "sender_name" => $row['sender_name'],
            "message" => $row['message'],
            "sent_at" => $row['created_at']   // ✅ FIX
        ];
    }

    echo json_encode(["messages"=>$messages]);
    exit();
}

/* ======================
   SEND MESSAGE
====================== */
if(isset($_GET['action']) && $_GET['action']=="send"){

    $msg = trim($_POST['message'] ?? '');

    if($msg == ''){
        echo json_encode(["success"=>false]);
        exit();
    }

    // decide receiver
    if($_SESSION['role'] == "police"){
        $receiver = $_POST['receiver_id'] ?? 0;
    } else {
        $receiver = $admin_id;
    }

    $msg = mysqli_real_escape_string($conn, $msg);

    mysqli_query($conn,"
        INSERT INTO messages (sender_id, receiver_id, message)
        VALUES ('$user_id', '$receiver', '$msg')
    ");

    echo json_encode(["success"=>true]);
    exit();
}

echo json_encode(["messages"=>[]]);
