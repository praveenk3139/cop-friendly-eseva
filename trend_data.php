<?php
include "db.php";

// ✅ Set JSON header
header("Content-Type: application/json");

// ✅ Get filter safely
$type = isset($_GET['type']) ? trim($_GET['type']) : "";

// ✅ Prepare query
if(!empty($type)){
    $stmt = $conn->prepare("
        SELECT DATE(created_at) as date, COUNT(*) as total
        FROM complaints
        WHERE subject = ?
        GROUP BY DATE(created_at)
        ORDER BY date ASC
    ");
    $stmt->bind_param("s", $type);
} else {
    $stmt = $conn->prepare("
        SELECT DATE(created_at) as date, COUNT(*) as total
        FROM complaints
        GROUP BY DATE(created_at)
        ORDER BY date ASC
    ");
}

// ✅ Execute
$stmt->execute();
$result = $stmt->get_result();

// ✅ Collect data
$data = [];

while($row = $result->fetch_assoc()){
    $data[] = [
        "date" => $row['date'],
        "total" => (int)$row['total']
    ];
}

// ✅ Return JSON
echo json_encode($data);

// ✅ Cleanup
$stmt->close();
$conn->close();
?>