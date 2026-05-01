<?php
session_start();
include('../includes/dbconn.php');
include('../includes/config.php');

$response = [
    "timein" => false,
    "timeout" => false,
    "history" => "",
    "msg" => "",
    "error" => ""
];

if(isset($_POST['empid'])){
    $empid = $_POST['empid'];

    // ======================
    // GET LAST RECORD
    // ======================
    $sql = "SELECT * FROM attendance 
            WHERE empid=:empid 
            ORDER BY id DESC LIMIT 1";
    $query = $dbh->prepare($sql);
    $query->bindParam(':empid',$empid);
    $query->execute();
    $last = $query->fetch(PDO::FETCH_ASSOC);

    // ======================
    // TIME IN
    // ======================
    if(isset($_POST['timein'])){
        if($last && $last['timeout'] == NULL){
            $response['error'] = "Already timed in!";
        } else {
            $timein = date("Y-m-d H:i:s");

            $sql = "INSERT INTO attendance(empid, timein) 
                    VALUES(:empid, :timein)";
            $query = $dbh->prepare($sql);
            $query->bindParam(':empid',$empid);
            $query->bindParam(':timein',$timein);
            $query->execute();

            $response['msg'] = "Time In: ".$timein;
        }
    }

    // ======================
    // TIME OUT
    // ======================
    if(isset($_POST['timeout'])){
        $sql = "SELECT * FROM attendance 
                WHERE empid=:empid AND timeout IS NULL 
                ORDER BY id DESC LIMIT 1";
        $query = $dbh->prepare($sql);
        $query->bindParam(':empid',$empid);
        $query->execute();
        $row = $query->fetch(PDO::FETCH_ASSOC);

        if(!$row){
            $response['error'] = "No active Time In!";
        } else {
            $timein = strtotime($row['timein']);
            $now = time();

            $seconds_worked = $now - $timein;

            // Total hours and minutes
            $total_hours = floor($seconds_worked / 3600);
            $total_minutes = floor(($seconds_worked % 3600) / 60);

            // Check if >= 8 hours
            if($total_hours < HOURS_PER_DAY){
                $remaining_seconds = HOURS_PER_DAY*3600 - $seconds_worked;
                $rem_hours = floor($remaining_seconds / 3600);
                $rem_minutes = floor(($remaining_seconds % 3600) / 60);
                $response['error'] = "Cannot Time Out. Remaining: {$rem_hours}h {$rem_minutes}m";
} else {
                $timeout = date("Y-m-d H:i:s");

                // Calculate total hours as numeric
                $total_hours_numeric = $seconds_worked / 3600;
                $ot_hours_numeric = max(0, $seconds_worked - HOURS_PER_DAY*3600) / 3600;

                $sql = "UPDATE attendance 
                        SET timeout=:timeout, total_hours=:th, overtime=:ot 
                        WHERE id=:id";
                $query = $dbh->prepare($sql);
                $query->bindParam(':timeout',$timeout);
                $query->bindParam(':th',$total_hours_numeric);
                $query->bindParam(':ot',$ot_hours_numeric);
                $query->bindParam(':id',$row['id']);
                $query->execute();

                $response['msg'] = "Time Out: ".$timeout." | Hours: ".round($total_hours_numeric,2);
            }
        }
    }

    // ======================
    // BUTTON CONTROL
    // ======================
    $sql = "SELECT * FROM attendance 
            WHERE empid=:empid 
            ORDER BY id DESC LIMIT 1";
    $query = $dbh->prepare($sql);
    $query->bindParam(':empid',$empid);
    $query->execute();
    $row = $query->fetch(PDO::FETCH_ASSOC);

    if($row && $row['timeout'] == NULL){
        $response['timein'] = false;
        $response['timeout'] = true;
    } else {
        $response['timein'] = true;
        $response['timeout'] = false;
    }

    // ======================
    // HISTORY
    // ======================
    $sql = "SELECT * FROM attendance 
            WHERE empid=:empid 
            ORDER BY id DESC LIMIT 5";
    $query = $dbh->prepare($sql);
    $query->bindParam(':empid',$empid);
    $query->execute();
    $rows = $query->fetchAll(PDO::FETCH_ASSOC);

    $history = "<table>
        <tr>
            <th>Date</th>
            <th>Time In</th>
            <th>Time Out</th>
            <th>Hours</th>
            <th>OT</th>
        </tr>";

    foreach($rows as $r){
        $th_display = $r['total_hours'] ?? '---';
        $ot_display = $r['overtime'] ?? '---';

        $history .= "<tr>
            <td>".date("Y-m-d", strtotime($r['timein']))."</td>
            <td>{$r['timein']}</td>
            <td>".($r['timeout'] ?? '---')."</td>
            <td>{$th_display}</td>
            <td>{$ot_display}</td>
        </tr>";
    }

    $history .= "</table>";

    $response['history'] = $history;
}

echo json_encode($response);
?>
