<?php
session_start();
include "db.php";

$email = mysqli_real_escape_string($conn, $_POST['email']);
$password = $_POST['password'];

/* Get user by email only */
$sql = "SELECT * FROM users WHERE email='$email'";
$result = mysqli_query($conn, $sql);

if(mysqli_num_rows($result) == 1){

    $user = mysqli_fetch_assoc($result);

    /* MUST MATCH THIS */
    if(password_verify($password, $user['password'])){

        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];

        header("Location: dashboard.php");
        exit();

    } else {

        echo "<script>
        alert('Incorrect Password');
        window.location.href='login.html';
        </script>";

    }

} else {

    echo "<script>
    alert('Email not registered');
    window.location.href='login.html';
    </script>";

}
?>