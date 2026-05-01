<?php
session_start();
error_reporting(0);
include('../includes/dbconn.php');
include('../includes/functions.php');

if(strlen($_SESSION['emplogin'])==0) {
    header('location:../index.php');
    exit;
}

$empid = $_SESSION['eid'];
$msg = $error = "";

// Handle time in/out
if(isset($_POST['time_action'])) {
    $action = $_POST['action'];
    $today = date('Y-m-d');

    if($action == 'time_in') {
        $sql = "SELECT * FROM tbltime_logs WHERE EmpID=:empid AND DateWorked=:date";
        $query = $dbh->prepare($sql);
        $query->bindParam(':empid', $empid, PDO::PARAM_INT);
        $query->bindParam(':date', $today, PDO::PARAM_STR);
        $query->execute();

        if($query->rowCount() > 0) {
            $error = "You have already timed in today.";
        } else {
            $timeIn = date('H:i:s');
            $sql = "INSERT INTO tbltime_logs (EmpID, DateWorked, TimeIn, Status) VALUES(:empid, :date, :timein, 'Pending')";
            $query = $dbh->prepare($sql);
            $query->bindParam(':empid', $empid, PDO::PARAM_INT);
            $query->bindParam(':date', $today, PDO::PARAM_STR);
            $query->bindParam(':timein', $timeIn, PDO::PARAM_STR);
            $query->execute();

            if($query->rowCount() > 0) {
                $msg = "Time In recorded at " . date('h:i A', strtotime($timeIn));
            } else {
                $error = "Failed to record Time In. Please try again.";
            }
        }
    } elseif($action == 'time_out') {
        $sql = "SELECT * FROM tbltime_logs WHERE EmpID=:empid AND DateWorked=:date AND TimeOut IS NULL";
        $query = $dbh->prepare($sql);
        $query->bindParam(':empid', $empid, PDO::PARAM_INT);
        $query->bindParam(':date', $today, PDO::PARAM_STR);
        $query->execute();
        $record = $query->fetch(PDO::FETCH_OBJ);

        if($record) {
            $timeOut = date('H:i:s');
            $timeIn = strtotime($record->TimeIn);
            $timeOutTs = strtotime($timeOut);
            $hoursWorked = round(($timeOutTs - $timeIn) / 3600, 2);

            $sql = "UPDATE tbltime_logs SET TimeOut=:timeout, HoursWorked=:hours WHERE id=:id";
            $query = $dbh->prepare($sql);
            $query->bindParam(':timeout', $timeOut, PDO::PARAM_STR);
            $query->bindParam(':hours', $hoursWorked, PDO::PARAM_STR);
            $query->bindParam(':id', $record->id, PDO::PARAM_INT);
            $query->execute();

            if($query->rowCount() > 0) {
                $msg = "Time Out recorded at " . date('h:i A', strtotime($timeOut)) . ". Hours worked: " . $hoursWorked;
            } else {
                $error = "Failed to record Time Out. Please try again.";
            }
        } else {
            $error = "No active Time In found for today.";
        }
    }
}

// Get today's record
$today = date('Y-m-d');
$sql = "SELECT * FROM tbltime_logs WHERE EmpID=:empid AND DateWorked=:date";
$query = $dbh->prepare($sql);
$query->bindParam(':empid', $empid, PDO::PARAM_INT);
$query->bindParam(':date', $today, PDO::PARAM_STR);
$query->execute();
$todayRecord = $query->fetch(PDO::FETCH_OBJ);

// Get recent attendance
$sql = "SELECT * FROM tbltime_logs WHERE EmpID=:empid ORDER BY DateWorked DESC LIMIT 10";
$query = $dbh->prepare($sql);
$query->bindParam(':empid', $empid, PDO::PARAM_INT);
$query->execute();
$attendance = $query->fetchAll(PDO::FETCH_OBJ);

$page='time';
include('../includes/employee-header.php');
?>

<div class="main-content-inner">
    <div class="row">
        <div class="col-lg-6 col-ml-12" style="margin:auto;">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title">Time In / Time Out</h4>
                    <p class="text-muted font-14 mb-4">Today: <?php echo date('F d, Y'); ?></p>

                    <?php if($error){ ?><div class="alert alert-danger alert-dismissible fade show"><strong>Error: </strong><?php echo htmlentities($error); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div><?php } ?>

                    <?php if($msg){ ?><div class="alert alert-success alert-dismissible fade show"><strong>Success: </strong><?php echo htmlentities($msg); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div><?php } ?>

                    <form method="POST">
                        <div class="text-center mb-4">
                            <h5>Current Time: <span id="currentTime"><?php echo date('h:i:s A'); ?></span></h5>
                        </div>

                        <?php if(!$todayRecord || $todayRecord->TimeOut != NULL) { ?>
                            <input type="hidden" name="action" value="time_in">
                            <button class="btn btn-success btn-lg btn-block" name="time_action" type="submit">TIME IN</button>
                        <?php } elseif($todayRecord && $todayRecord->TimeOut == NULL) { ?>
                            <div class="alert alert-info">Timed in at: <?php echo date('h:i A', strtotime($todayRecord->TimeIn)); ?></div>
                            <input type="hidden" name="action" value="time_out">
                            <button class="btn btn-danger btn-lg btn-block" name="time_action" type="submit">TIME OUT</button>
                        <?php } ?>
                    </form>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-body">
                    <h4 class="header-title">Recent Attendance</h4>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead><tr><th>Date</th><th>Time In</th><th>Time Out</th><th>Hours</th><th>Status</th></tr></thead>
                            <tbody>
                                <?php foreach($attendance as $row) { ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($row->DateWorked)); ?></td>
                                    <td><?php echo $row->TimeIn ? date('h:i A', strtotime($row->TimeIn)) : '-'; ?></td>
                                    <td><?php echo $row->TimeOut ? date('h:i A', strtotime($row->TimeOut)) : '-'; ?></td>
                                    <td><?php echo $row->HoursWorked ? $row->HoursWorked : '-'; ?></td>
                                    <td><span class="badge badge-<?php echo $row->Status=='Approved'?'success':'warning'; ?>"><?php echo $row->Status; ?></span></td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
setInterval(function() {
    var now = new Date();
    var time = now.toLocaleTimeString('en-US', {hour12: true});
    document.getElementById('currentTime').textContent = time;
}, 1000);
</script>

<?php include '../includes/employee-footer.php'; ?>
