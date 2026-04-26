<?php
session_start();
include "db.php";

$user_id = $_SESSION['user_id'];
$admin_id = 1;

$result = mysqli_query($conn,"
SELECT * FROM messages 
WHERE (sender_id='$user_id' AND receiver_id='$admin_id')
   OR (sender_id='$admin_id' AND receiver_id='$user_id')
ORDER BY created_at ASC
");

while($row = mysqli_fetch_assoc($result)){
    $class = ($row['sender_id'] == $user_id) ? 'sent' : 'received';
    echo "<div class='msg $class'>{$row['message']}</div>";
}
?>