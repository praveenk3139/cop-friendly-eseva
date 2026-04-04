<?php

include("db.php");
require('fpdf/fpdf.php');

if(isset($_GET['id']))
{

$id = $_GET['id'];

$sql = "SELECT * FROM complaints WHERE complaint_id='$id'";
$result = mysqli_query($conn,$sql);

if(mysqli_num_rows($result) > 0)
{

$row = mysqli_fetch_assoc($result);

$pdf = new FPDF();
$pdf->AddPage();

$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'Cop Friendly e-Seva Complaint Report',0,1,'C');

$pdf->Ln(10);

$pdf->SetFont('Arial','',12);

$pdf->Cell(50,10,'Complaint ID:');
$pdf->Cell(100,10,$row['complaint_id'],0,1);

$pdf->Cell(50,10,'User ID:');
$pdf->Cell(100,10,$row['user_id'],0,1);

$pdf->Cell(50,10,'Subject:');
$pdf->Cell(100,10,$row['subject'],0,1);

$pdf->Cell(50,10,'Description:');
$pdf->Ln(10);
$pdf->MultiCell(0,10,$row['description']);

$pdf->Ln(5);

$pdf->Cell(50,10,'Status:');
$pdf->Cell(100,10,$row['status'],0,1);

$pdf->Cell(50,10,'Date:');
$pdf->Cell(100,10,$row['created_at'],0,1);

$pdf->Ln(10);

/* Evidence Image */

if(!empty($row['file']))
{
$image = "uploads/".$row['file'];

$pdf->Cell(0,10,'Evidence Image:',0,1);

$pdf->Image($image,50,$pdf->GetY(),100);

}

$pdf->Output("D","Complaint_".$row['complaint_id'].".pdf");

}

}

?>