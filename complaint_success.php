<?php
$id = $_GET['id'];
?>

<!DOCTYPE html>
<html>
<head>
<title>Complaint Submitted</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body{
background:#f4f6f9;
}

.card{
margin-top:120px;
padding:40px;
border-radius:12px;
text-align:center;
}
</style>

</head>

<body>

<div class="container">

<div class="row justify-content-center">

<div class="col-md-6">

<div class="card">

<h2 style="color:green;">Complaint Submitted Successfully</h2>

<p>Your complaint has been registered.</p>

<h4>
Complaint ID : <b>#<?php echo $id; ?></b>
</h4>

<p>Please save this ID to track your complaint.</p>

<a href="track.php?id=<?php echo $id; ?>" class="btn btn-primary">
Track Complaint
</a>

</div>

</div>

</div>

</div>

</body>
</html>