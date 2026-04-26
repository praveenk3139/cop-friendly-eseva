<?php
include "db.php";
include "mail_config.php"; // ✅ Mail function

// ⏱️ Escalation time (48 hours)
$hours = 48;

// ✅ Get complaints that need escalation
$sql = "
SELECT c.*, u.name, u.email
FROM complaints c
JOIN users u ON c.user_id = u.user_id
WHERE 
    c.status != 'Resolved'
    AND c.escalated = 0
    AND TIMESTAMPDIFF(HOUR, c.created_at, NOW()) >= $hours
";

$result = mysqli_query($conn, $sql);

if(mysqli_num_rows($result) > 0){

    while($row = mysqli_fetch_assoc($result)){

        $complaint_id = $row['complaint_id'];

        // ✅ Update complaint as escalated
        mysqli_query($conn, "
            UPDATE complaints 
            SET escalated = 1,
                status = 'Escalated',
                escalation_time = NOW()
            WHERE complaint_id = '$complaint_id'
        ");

        // ============================
        // 📧 SEND EMAIL TO SENIOR OFFICER
        // ============================

        $senior_email = "praveenk3139@gmail.com"; // 🔁 change this

        $subject = "🚨 Escalation Alert - Complaint ID #".$complaint_id;

        $message = "
        <h3>Escalation Alert</h3>
        <p><b>Complaint ID:</b> ".$complaint_id."</p>
        <p><b>Type:</b> ".$row['subject']."</p>
        <p><b>Location:</b> ".$row['location']."</p>
        <p><b>Description:</b> ".$row['description']."</p>
        <p><b>User:</b> ".$row['name']." (".$row['email'].")</p>
        <p><b>Delay:</b> More than $hours hours without resolution</p>

        <br>
        <p style='color:red;'><b>Immediate action required!</b></p>
        ";

        // ✅ Send email
        if(sendMail($senior_email, $subject, $message)){
            echo "📧 Email sent for Complaint ID: $complaint_id <br>";
        }else{
            echo "❌ Email failed for Complaint ID: $complaint_id <br>";
        }
    }

    echo "<br>✅ Escalation process completed";

}else{
    echo "✅ No complaints to escalate";
}
?>