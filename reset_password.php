<?php
include "db.php";

if($_SERVER["REQUEST_METHOD"] == "POST"){

    /* Get and clean input */
    $email = trim(mysqli_real_escape_string($conn, $_POST['email']));
    $new_password = trim($_POST['new_password']);

    /* Validate input */
    if(empty($email) || empty($new_password)){
        echo "<script>
        alert('All fields are required');
        window.location.href='forgot_password.html';
        </script>";
        exit();
    }

    /* Check if email exists */
    $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");

    if(mysqli_num_rows($check) == 1){

        /* Hash password */
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        /* Update password */
        $update = mysqli_query($conn, 
        "UPDATE users SET password='$hashed_password' WHERE email='$email'");

        if($update){

            echo "<script>
            alert('✅ Password Updated Successfully');
            window.location.href='login.html';
            </script>";

        }else{

            echo "<script>
            alert('❌ Database error while updating');
            window.location.href='forgot_password.html';
            </script>";

        }

    }else{

        echo "<script>
        alert('❌ Email not found');
        window.location.href='forgot_password.html';
        </script>";

    }

}else{

    echo "<script>
    alert('Invalid Request');
    window.location.href='login.html';
    </script>";

}
?>