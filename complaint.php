<?php
include "db.php";

/* =========================
   ✅ AUTO PRIORITY FUNCTION
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

/* =========================
   ✅ FORM SUBMIT
========================= */
if(isset($_POST['submit']))
{
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $description = mysqli_real_escape_string($conn, $_POST['complaint']);

    // ✅ AUTO PRIORITY
    $priority = getPriority($description);

    $sql = "INSERT INTO complaints (name, email, description, priority, status, created_at) 
            VALUES ('$name','$email','$description','$priority','Pending',NOW())";

    if(mysqli_query($conn,$sql))
    {
        echo "<h3 style='color:green;'>✅ Complaint Submitted Successfully!</h3>";
    }
    else
    {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!-- =========================
     ✅ FORM UI
========================= -->

<form method="post">

Name:<br>
<input type="text" name="name" required><br><br>

Email:<br>
<input type="email" name="email" required><br><br>

Complaint:<br>
<textarea name="complaint" required></textarea><br><br>

<!-- ❌ REMOVE MANUAL PRIORITY (AUTO USED) -->
<!-- If you want manual + auto, tell me -->

<button type="submit" name="submit">Submit Complaint</button>

</form>