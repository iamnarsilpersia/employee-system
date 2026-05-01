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

    // 🔍 Get latest record
    $sql = "SELECT * FROM attendance 
            WHERE empid=:empid 
            ORDER BY id DESC LIMIT 1";
    $query = $dbh->prepare($sql);
    $query->bindParam(':empid',$empid);
    $query->execute();
    $row = $query->fetch(PDO::FETCH_ASSOC);

    // ======================
    // ✅ TIME IN
    // ======================
    if(isset($_POST['timein'])){

        // Check if already timed in without timeout
        if($row && $row['timeout'] == NULL){
            $response['error'] = "You already timed in!";
        } else {

            $timein = date("Y-m-d H:i:s");

            $sql = "INSERT INTO attendance(empid, timein) 
                    VALUES(:empid, :timein)";
            $query = $dbh->prepare($sql);
            $query->bindParam(':empid',$empid);
            $query->bindParam(':timein',$timein);
            $query->execute();

            $response['msg'] = "Time In recorded at ".$timein;
        }
    }

    // ======================
    // ✅ TIME OUT
    // ======================
    if(isset($_POST['timeout'])){

        // Find active timein
        $sql = "SELECT * FROM attendance 
                WHERE empid=:empid AND timeout IS NULL 
                ORDER BY id DESC LIMIT 1";
        $query = $dbh->prepare($sql);
        $query->bindParam(':empid',$empid);
        $query->execute();
        $row = $query->fetch(PDO::FETCH_ASSOC);

        if(!$row){
            $response['error'] = "No active Time In found!";
        } else {

            $timein = strtotime($row['timein']);
            $now = time();

            $hours = ($now - $timein) / 3600;

            // ❌ If less than 8 hours
            if($hours < HOURS_PER_DAY){
                $remaining = round(HOURS_PER_DAY - $hours,2);
                $response['error'] = "You cannot Time Out yet. Remaining hours: ".$remaining;
            } else {

                $timeout = date("Y-m-d H:i:s");

                $sql = "UPDATE attendance 
                        SET timeout=:timeout 
                        WHERE id=:id";
                $query = $dbh->prepare($sql);
                $query->bindParam(':timeout',$timeout);
                $query->bindParam(':id',$row['id']);
                $query->execute();

                $response['msg'] = "Time Out recorded at ".$timeout;
            }
        }
    }

    // ======================
    // 🔄 REFRESH STATUS
    // ======================
    $sql = "SELECT * FROM attendance 
            WHERE empid=:empid 
            ORDER BY id DESC LIMIT 1";
    $query = $dbh->prepare($sql);
    $query->bindParam(':empid',$empid);
    $query->execute();
    $row = $query->fetch(PDO::FETCH_ASSOC);

    if($row){
        if($row['timeout'] == NULL){
            $response['timein'] = false;
            $response['timeout'] = true;
        } else {
            $response['timein'] = true;
            $response['timeout'] = false;
        }
    } else {
        $response['timein'] = true;
        $response['timeout'] = false;
    }

    // ======================
    // 📊 HISTORY TABLE
    // ======================
    $sql = "SELECT * FROM attendance 
            WHERE empid=:empid 
            ORDER BY id DESC LIMIT 5";
    $query = $dbh->prepare($sql);
    $query->bindParam(':empid',$empid);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_ASSOC);

    $history = "<table>
                <tr>
                    <th>Date</th>
                    <th>Time In</th>
                    <th>Time Out</th>
                </tr>";

    foreach($results as $r){
        $history .= "<tr>
                        <td>".date("Y-m-d", strtotime($r['timein']))."</td>
                        <td>".$r['timein']."</td>
                        <td>".($r['timeout'] ? $r['timeout'] : '---')."</td>
                    </tr>";
    }

    $history .= "</table>";

    $response['history'] = $history;
}

echo json_encode($response);
?>
