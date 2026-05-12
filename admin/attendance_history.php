<?php
session_start();
include('../includes/dbconn.php');
include('../includes/functions.php');

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
$total_gross = 0;
$total_sss = 0;
$total_philhealth = 0;
$total_pagibig = 0;
$total_tax = 0;
$total_net = 0;

foreach($results as $row){
    $hours = $row->HoursWorked ?? 0;
    $ot_hours = ($hours > 8) ? $hours - 8 : 0;
    $regular_hours = min($hours, 8);
    
    $daily_pay = ($regular_hours / 8) * $daily_rate;
    $ot_pay = $ot_hours * $ot_rate;
    $gross = $daily_pay + $ot_pay;
    
    $payroll = calculate_employee_payroll($gross, $daily_rate);
    
    $total_hours += $hours;
    $total_ot += $ot_hours;
    $total_gross += $gross;
    $total_sss += $payroll['sss'];
    $total_philhealth += $payroll['philhealth'];
    $total_pagibig += $payroll['pagibig'];
    $total_tax += $payroll['withholding_tax'];
    $total_net += $payroll['net_pay'];
    
    echo "<tr>";
    echo "<td>".date('M d, Y', strtotime($row->DateWorked))."</td>";
    echo "<td>".($row->TimeIn ? date('h:i A', strtotime($row->TimeIn)) : '-')."</td>";
    echo "<td>".($row->TimeOut ? date('h:i A', strtotime($row->TimeOut)) : '-')."</td>";
    echo "<td>".number_format($hours,2)."</td>";
    echo "<td>".number_format($ot_hours,2)."</td>";
    echo "<td>₱".number_format($gross,2)."</td>";
    echo "<td>₱".number_format($payroll['sss'],2)."</td>";
    echo "<td>₱".number_format($payroll['philhealth'],2)."</td>";
    echo "<td>₱".number_format($payroll['pagibig'],2)."</td>";
    echo "<td>₱".number_format($payroll['withholding_tax'],2)."</td>";
    echo "<td><strong>₱".number_format($payroll['net_pay'],2)."</strong></td>";
    echo "</tr>";
}

echo "<tr style='font-weight:bold; background:#e8f4fc;'>";
echo "<td colspan='3'>TOTAL</td>";
echo "<td>".number_format($total_hours,2)."</td>";
echo "<td>".number_format($total_ot,2)."</td>";
echo "<td>₱".number_format($total_gross,2)."</td>";
echo "<td>₱".number_format($total_sss,2)."</td>";
echo "<td>₱".number_format($total_philhealth,2)."</td>";
echo "<td>₱".number_format($total_pagibig,2)."</td>";
echo "<td>₱".number_format($total_tax,2)."</td>";
echo "<td><strong>₱".number_format($total_net,2)."</strong></td>";
echo "</tr>";
?>