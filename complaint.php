<?php
include "db.php";

if(isset($_POST['submit']))
{
    $name = $_POST['name'];
    $email = $_POST['email'];
    $complaint = $_POST['complaint'];

    $sql = "INSERT INTO complaints (name,email,complaint) 
            VALUES ('$name','$email','$complaint')";

    if(mysqli_query($conn,$sql))
    {
        echo "Complaint Submitted Successfully!";
    }
}
?>

<form method="post">
Name:<br>
<input type="text" name="name" required><br><br>

Email:<br>
<input type="email" name="email" required><br><br>

Complaint:<br>
<textarea name="complaint" required></textarea><br><br>

<button type="submit" name="submit">Submit Complaint</button>
</form>