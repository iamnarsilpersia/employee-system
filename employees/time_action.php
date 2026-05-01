<?php
session_start();
include('../includes/dbconn.php');

if(!isset($_SESSION['emplogin'])){
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$empid = $_SESSION['emplogin'];
$response = ['timein' => false, 'timeout' => true, 'history' => '', 'msg' => '', 'error' => ''];

$dateToday = date('Y-m-d');

// Function to get today's history
function getTodayHistory($dbh, $empid, $dateToday) {
    $sql = "SELECT * FROM tbltime_logs WHERE EmpID=:empid AND DateWorked=:date ORDER BY TimeIn DESC";
    $query = $dbh->prepare($sql);
    $query->bindParam(':empid', $empid);
    $query->bindParam(':date', $dateToday);
    $query->execute();
    return $query->fetchAll(PDO::FETCH_OBJ);
}

// Function to generate history HTML
function generateHistoryHTML($records) {
    if(count($records) == 0) return '<p>No records for today.</p>';
    
    $html = '<table border="1" style="width:100%; border-collapse:collapse;">
                <tr><th>Time In</th><th>Time Out</th><th>Hours Worked</th><th>Status</th></tr>';
    
    foreach($records as $row){
        $html .= '<tr>';
        $html .= '<td>'.$row->TimeIn.'</td>';
        $html .= '<td>'.($row->TimeOut ? $row->TimeOut : '-').'</td>';
        $html .= '<td>'.($row->HoursWorked ? number_format($row->HoursWorked,2) : '-').'</td>';
        $html .= '<td>'.$row->Status.'</td>';
        $html .= '</tr>';
    }
    $html .= '</table>';
    return $html;
}

// Check current status
$check = $dbh->prepare("SELECT * FROM tbltime_logs WHERE EmpID=:empid AND DateWorked=:date AND TimeOut IS NULL");
$check->bindParam(':empid', $empid);
$check->bindParam(':date', $dateToday);
$check->execute();
$activeRecord = $check->fetch(PDO::FETCH_OBJ);

if($activeRecord) {
    $response['timein'] = true;
    $response['timeout'] = false;
}

// Handle Time In
if(isset($_POST['timein'])){
    if($activeRecord) {
        $response['error'] = "You have already timed in today!";
    } else {
        $timeIn = date('H:i:s');
        $sql = "INSERT INTO tbltime_logs(EmpID, DateWorked, TimeIn, Status) VALUES(:empid, :date, :timein, 'In Progress')";
        $query = $dbh->prepare($sql);
        $query->bindParam(':empid', $empid);
        $query->bindParam(':date', $dateToday);
        $query->bindParam(':timein', $timeIn);
        
        if($query->execute()){
            $response['msg'] = 'Time In recorded at '.$timeIn;
            $response['timein'] = true;
            $response['timeout'] = false;
        } else {
            $response['error'] = "Error recording time in!";
        }
    }
}

// Handle Time Out
if(isset($_POST['timeout'])){
    if(!$activeRecord) {
        $response['error'] = "You haven't timed in yet or already timed out!";
    } else {
        $timeOut = date('H:i:s');
        $timeIn = $activeRecord->TimeIn;
        $hoursWorked = round((strtotime($timeOut) - strtotime($timeIn))/3600, 2);
        
        $update = $dbh->prepare("UPDATE tbltime_logs SET TimeOut=:timeout, HoursWorked=:hours, Status='Completed' WHERE id=:id");
        $update->bindParam(':timeout', $timeOut);
        $update->bindParam(':hours', $hoursWorked);
        $update->bindParam(':id', $activeRecord->id);
        
        if($update->execute()){
            $response['msg'] = 'Time Out recorded at '.$timeOut.'. Hours worked: '.$hoursWorked;
            $response['timein'] = false;
            $response['timeout'] = true;
        } else {
            $response['error'] = "Error recording time out!";
        }
    }
}

// Get history
$records = getTodayHistory($dbh, $empid, $dateToday);
$response['history'] = generateHistoryHTML($records);

echo json_encode($response);
exit;
?>
