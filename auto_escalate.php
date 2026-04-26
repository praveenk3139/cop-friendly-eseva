<?php
include "db.php";

// ✅ Escalation rules (SMART)
$highPriorityHours   = 24; // High priority → faster escalation
$mediumPriorityHours = 48;
$lowPriorityHours    = 72;

// ✅ Prepared query
$sql = "
UPDATE complaints 
SET 
    escalated = 1,
    status = 'Escalated',
    escalation_time = NOW()
WHERE 
    status != 'Resolved'
    AND escalated = 0
    AND (
        (priority = 'High' AND TIMESTAMPDIFF(HOUR, created_at, NOW()) >= ?) OR
        (priority = 'Medium' AND TIMESTAMPDIFF(HOUR, created_at, NOW()) >= ?) OR
        (priority = 'Low' AND TIMESTAMPDIFF(HOUR, created_at, NOW()) >= ?)
    )
";

$stmt = $conn->prepare($sql);

if(!$stmt){
    die("❌ Prepare failed: " . $conn->error);
}

// ✅ Bind parameters
$stmt->bind_param("iii", $highPriorityHours, $mediumPriorityHours, $lowPriorityHours);

// ✅ Execute
if($stmt->execute()){

    $affected = $stmt->affected_rows;

    echo "✅ Escalation check completed<br>";
    echo "🚨 Escalated Complaints: " . $affected;

}else{
    echo "❌ Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>