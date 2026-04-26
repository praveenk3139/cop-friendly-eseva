<?php
include "db.php";

$sql = "
SELECT DATE(created_at) as date, COUNT(*) as total 
FROM complaints 
GROUP BY DATE(created_at)
ORDER BY date ASC
";

$result = mysqli_query($conn, $sql);

$data = [];

while($row = mysqli_fetch_assoc($result)){
    $data[] = $row;
}

echo json_encode($data);
?>