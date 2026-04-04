<?php
include "db.php";
?>

<!DOCTYPE html>
<html>
<head>
<title>Track Complaint</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body style="background:#f4f6f9;">

<div class="container mt-5">

<div class="row justify-content-center">
<div class="col-md-6">

<div class="card p-4 shadow">

<h3 class="text-center mb-4">Complaint Tracking</h3>

<form method="POST">

<label>Enter Complaint ID</label>
<input type="number" name="id" class="form-control" required>

<br>

<button type="submit" name="track" class="btn btn-primary w-100">
Track Complaint
</button>

</form>

<hr>

<?php

if(isset($_POST['track']))
{
    $id = $_POST['id'];

    $query = "SELECT * FROM complaints WHERE id='$id'";
    $result = mysqli_query($conn,$query);

    if(mysqli_num_rows($result) > 0)
    {
        $row = mysqli_fetch_assoc($result);

        echo "<div class='alert alert-success'>";
        echo "<b>Complaint ID:</b> ".$row['id']."<br>";
        echo "<b>Name:</b> ".$row['name']."<br>";
        echo "<b>Complaint:</b> ".$row['complaint']."<br>";
        echo "<b>Status:</b> ".$row['status']."";
        echo "</div>";
    }
    else
    {
        echo "<div class='alert alert-danger'>Complaint not found</div>";
    }
}

?>

</div>
</div>
</div>

</div>

</body>
</html>