<?php
session_start();
include('../includes/dbconn.php');

// Make sure employee is logged in
if(strlen($_SESSION['empid'])==0){
    header('location:login.php');
    exit();
}

$empid = $_SESSION['empid'];
$dateToday = date('Y-m-d');

if(isset($_POST['timein'])){
    $timeIn = date('H:i:s');
    
    // Check if already clocked in today
    $check = $dbh->prepare("SELECT * FROM tbltime_logs WHERE EmpID=:empid AND DateWorked=:date");
    $check->bindParam(':empid',$empid,PDO::PARAM_STR);
    $check->bindParam(':date',$dateToday,PDO::PARAM_STR);
    $check->execute();

    if($check->rowCount() == 0){
        $sql = "INSERT INTO tbltime_logs(EmpID, DateWorked, TimeIn) VALUES(:empid, :date, :timein)";
        $query = $dbh->prepare($sql);
        $query->bindParam(':empid',$empid,PDO::PARAM_STR);
        $query->bindParam(':date',$dateToday,PDO::PARAM_STR);
        $query->bindParam(':timein',$timeIn,PDO::PARAM_STR);
        $query->execute();
        $msg = "Time In recorded at $timeIn";
    } else {
        $error = "You have already clocked in today!";
    }
}

if(isset($_POST['timeout'])){
    $timeOut = date('H:i:s');

    // Get time in
    $check = $dbh->prepare("SELECT TimeIn FROM tbltime_logs WHERE EmpID=:empid AND DateWorked=:date AND TimeOut IS NULL");
    $check->bindParam(':empid',$empid,PDO::PARAM_STR);
    $check->bindParam(':date',$dateToday,PDO::PARAM_STR);
    $check->execute();
    
    if($check->rowCount() > 0){
        $row = $check->fetch(PDO::FETCH_ASSOC);
        $timeIn = $row['TimeIn'];

        // Calculate hours worked
        $hoursWorked = (strtotime($timeOut) - strtotime($timeIn))/3600; // in hours

        $update = $dbh->prepare("UPDATE tbltime_logs SET TimeOut=:timeout, HoursWorked=:hours, Status='Completed' WHERE EmpID=:empid AND DateWorked=:date");
        $update->bindParam(':timeout',$timeOut,PDO::PARAM_STR);
        $update->bindParam(':hours',$hoursWorked);
        $update->bindParam(':empid',$empid,PDO::PARAM_STR);
        $update->bindParam(':date',$dateToday,PDO::PARAM_STR);
        $update->execute();

        $msg = "Time Out recorded at $timeOut. Hours worked: $hoursWorked";
    } else {
        $error = "You haven't clocked in yet or already clocked out!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Employee Time In/Out</title>
</head>
<body>
    <h2>Time In / Time Out</h2>

    <?php if(isset($msg)){ echo "<p style='color:green;'>$msg</p>"; } ?>
    <?php if(isset($error)){ echo "<p style='color:red;'>$error</p>"; } ?>

    <form method="POST">
        <button type="submit" name="timein">Time In</button>
        <button type="submit" name="timeout">Time Out</button>
    </form>

    <h3>Today's Log</h3>
    <table border="1">
        <tr>
            <th>Time In</th>
            <th>Time Out</th>
            <th>Hours Worked</th>
        </tr>
        <?php
        $sql = $dbh->prepare("SELECT * FROM tbltime_logs WHERE EmpID=:empid AND DateWorked=:date");
        $sql->bindParam(':empid',$empid,PDO::PARAM_STR);
        $sql->bindParam(':date',$dateToday,PDO::PARAM_STR);
        $sql->execute();
        $results = $sql->fetchAll(PDO::FETCH_OBJ);
        foreach($results as $result){
            echo "<tr>";
            echo "<td>".$result->TimeIn."</td>";
            echo "<td>".($result->TimeOut ? $result->TimeOut : "-")."</td>";
            echo "<td>".($result->HoursWorked ? number_format($result->HoursWorked,2) : "-")."</td>";
            echo "</tr>";
        }
        ?>
    </table>

    <p>Standard working hours: 8 hours/day</p>
</body>
</html>