<?php
session_start();
include('../includes/dbconn.php');

if(!isset($_SESSION['alogin'])){
    exit;
}

$empid = $_POST['empid'] ?? '';
$from = $_POST['from'] ?? date('Y-m-01');
$to = $_POST['to'] ?? date('Y-m-t');

if(empty($empid)) exit;

$daily_rate = defined('DAILY_RATE') ? DAILY_RATE : 500;
$ot_rate = defined('OT_RATE') ? OT_RATE : 50;

$sql = "SELECT * FROM tbltime_logs WHERE EmpID=:empid AND DateWorked BETWEEN :from AND :to ORDER BY DateWorked DESC";
$query = $dbh->prepare($sql);
$query->bindParam(':empid', $empid);
$query->bindParam(':from', $from);
$query->bindParam(':to', $to);
$query->execute();
$results = $query->fetchAll(PDO::FETCH_OBJ);

$total_hours = 0;
$total_ot = 0;
$total_pay = 0;

foreach($results as $row){
    $hours = $row->HoursWorked ?? 0;
    $ot_hours = ($hours > 8) ? $hours - 8 : 0;
    $regular_hours = min($hours, 8);
    
    $daily_pay = ($regular_hours / 8) * $daily_rate;
    $ot_pay = $ot_hours * $ot_rate;
    $row_total = $daily_pay + $ot_pay;
    
    $total_hours += $hours;
    $total_ot += $ot_hours;
    $total_pay += $row_total;
    
    echo "<tr>";
    echo "<td>".$row->DateWorked."</td>";
    echo "<td>".($row->TimeIn ?? '-')."</td>";
    echo "<td>".($row->TimeOut ?? '-')."</td>";
    echo "<td>".number_format($hours,2)."</td>";
    echo "<td>".number_format($ot_hours,2)."</td>";
    echo "<td>".number_format($daily_pay,2)."</td>";
    echo "<td>".number_format($ot_pay,2)."</td>";
    echo "<td>".number_format($row_total,2)."</td>";
    echo "</tr>";
}

echo "<tr style='font-weight:bold; background:#f0f0f0;'>";
echo "<td colspan='3'>TOTAL</td>";
echo "<td>".number_format($total_hours,2)."</td>";
echo "<td>".number_format($total_ot,2)."</td>";
echo "<td colspan='2'></td>";
echo "<td>".number_format($total_pay,2)."</td>";
echo "</tr>";
?>
