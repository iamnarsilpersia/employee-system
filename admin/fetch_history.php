<?php
// API endpoint - no template needed
include('../includes/dbconn.php');

if(isset($_POST['empid'])){
    $empid = $_POST['empid'];
    $dateToday = date('Y-m-d');
    
    $sql = "SELECT * FROM tbltime_logs WHERE EmpID=:empid AND DateWorked=:date ORDER BY TimeIn DESC";
    $query = $dbh->prepare($sql);
    $query->bindParam(':empid', $empid);
    $query->bindParam(':date', $dateToday);
    $query->execute();
    $logs = $query->fetchAll(PDO::FETCH_OBJ);
    
    if($logs){
        echo "<h3>Today's Log</h3>";
        echo "<table border='1'><tr><th>Time In</th><th>Time Out</th><th>Hours Worked</th></tr>";
        foreach($logs as $log){
            echo "<tr>";
            echo "<td>".$log->TimeIn."</td>";
            echo "<td>".($log->TimeOut ? $log->TimeOut : "-")."</td>";
            echo "<td>".($log->HoursWorked ? number_format($log->HoursWorked,2) : "-")."</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No time log yet today.</p>";
    }
}
?>
