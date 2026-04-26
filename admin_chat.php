<?php
session_start();
include "db.php";

if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit();
}

if(!isset($_GET['user'])){
    echo "User not selected";
    exit();
}

$user_id = $_GET['user'];

/* FETCH MESSAGES */
$result = mysqli_query($conn,"
SELECT * FROM messages 
WHERE (sender_id='$user_id' AND receiver_id='admin') 
   OR (sender_id='admin' AND receiver_id='$user_id')
ORDER BY created_at ASC
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Chat</title>

<style>
body{
font-family:Arial;
background:#0f2027;
color:white;
}

.chat-box{
width:60%;
margin:40px auto;
background:#222;
padding:20px;
border-radius:10px;
height:400px;
overflow-y:auto;
}

.msg{
padding:10px;
margin:10px;
border-radius:8px;
max-width:60%;
}

.user{
background:#007bff;
margin-left:auto;
}

.admin{
background:#22c55e;
}

form{
width:60%;
margin:10px auto;
display:flex;
gap:10px;
}

input{
flex:1;
padding:10px;
border-radius:6px;
border:none;
}

button{
padding:10px;
background:#22c55e;
color:white;
border:none;
border-radius:6px;
}
</style>
</head>

<body>

<h2 style="text-align:center;">💬 Chat with User #<?php echo $user_id; ?></h2>

<div class="chat-box">
<?php while($row = mysqli_fetch_assoc($result)){ ?>

<div class="msg <?php echo ($row['sender_id']=='admin') ? 'admin' : 'user'; ?>">
<?php echo $row['message']; ?>
</div>

<?php } ?>
</div>

<form method="post">
<input type="text" name="msg" placeholder="Type message..." required>
<button type="submit">Send</button>
</form>

<?php
if(isset($_POST['msg'])){
    $msg = $_POST['msg'];

    mysqli_query($conn,"
    INSERT INTO messages (sender_id, receiver_id, message)
    VALUES ('admin','$user_id','$msg')
    ");

    echo "<script>location.reload();</script>";
}
?>

</body>
</html>