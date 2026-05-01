<?php
session_start();
error_reporting(0);
include('../includes/dbconn.php');
include('../includes/functions.php');
include('../includes/config.php');

if(strlen($_SESSION['emplogin'])==0) {   
    header('location:../index.php');
    exit();
}

$empid = $_SESSION['eid']; // employee ID (numeric)

// Payroll settings
$daily_rate = DAILY_RATE;
$ot_rate = OT_RATE;

// Month filter
$month = isset($_GET['month']) ? intval($_GET['month']) : date('m');
$year  = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// FIXED DATE RANGE
$from = date("Y-m-01 00:00:00", strtotime("$year-$month-01"));
$to   = date("Y-m-t 23:59:59", strtotime($from));

// Month navigation
$prev_month = $month-1; $prev_year = $year;
if($prev_month<1){ $prev_month=12; $prev_year=$year-1; }

$next_month = $month+1; $next_year = $year;
if($next_month>12){ $next_month=1; $next_year=$year+1; }

// Fetch employee info
$sqlEmp = "SELECT EmpId, FirstName, LastName FROM tblemployees WHERE id=:empid";
$qEmp = $dbh->prepare($sqlEmp);
$qEmp->bindParam(':empid', $empid);
$qEmp->execute();
$employee = $qEmp->fetch(PDO::FETCH_OBJ);

// FETCH ATTENDANCE FROM tbltime_logs
$sql = "SELECT * FROM tbltime_logs 
        WHERE EmpID=:empid 
        AND DateWorked >= :f 
        AND DateWorked <= :t
        ORDER BY DateWorked DESC";

$query = $dbh->prepare($sql);
$query->bindParam(':empid', $employee->EmpId, PDO::PARAM_STR);
$query->bindParam(':f', $from);
$query->bindParam(':t', $to);
$query->execute();
$results = $query->fetchAll(PDO::FETCH_OBJ);

// Calculate monthly total
$monthly_total = 0;
foreach($results as $row){

    try {
        $timein_dt = new DateTime($row->timein);
        $timein = $timein_dt->getTimestamp();
    } catch (Exception $e) {
        $timein = false;
    }

    $timeout = false;
    if($row->timeout){
        try {
            $timeout_dt = new DateTime($row->timeout);
            $timeout = $timeout_dt->getTimestamp();
        } catch (Exception $e) {
            $timeout = false;
        }
    }

    // Calculate hours worked
    if($timeout && $timein){
        $hours_worked = round(($timeout - $timein)/3600, 2);
    } else {
        $hours_worked = 0;
    }

    // Calculate pay based on hours worked (8 hours = full day)
    $regular_hours = ($hours_worked > HOURS_PER_DAY) ? HOURS_PER_DAY : $hours_worked;
    $ot = ($hours_worked > HOURS_PER_DAY) ? round($hours_worked - HOURS_PER_DAY, 2) :0;

    $daily_pay = round($regular_hours * ($daily_rate / HOURS_PER_DAY), 2);
    $ot_pay = round($ot * $ot_rate, 2);

    $monthly_total += $daily_pay + $ot_pay;
}
?>

<?php $page='leaves'; include('../includes/employee-header.php'); ?>

    <div class="main-content-inner">
        
        <!-- ATTENDANCE & SALARY ROW -->
        <div class="row mb-4">
            <!-- TIME IN/OUT PANEL -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="ti-time"></i> Time In / Time Out</h5>
                    </div>
                    <div class="card-body text-center">
                        <div id="status" class="mb-3"></div>
                        <div id="buttons" class="d-flex justify-content-center gap-2">
                            <button id="timein-btn" class="btn btn-success">Time In</button>
                            <button id="timeout-btn" class="btn btn-danger" disabled>Time Out</button>
                            <button id="force-timeout-btn" class="btn btn-warning" disabled>Force Out</button>
                        </div>
                        <div id="message" class="mt-3"></div>
                    </div>
                </div>
            </div>
            
            <!-- TODAY'S ATTENDANCE -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="ti-calendar"></i> Today's Attendance</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $today = date("Y-m-d");
                        $sqlToday = "SELECT * FROM tbltime_logs 
                                      WHERE EmpID=:empid 
                                      AND DateWorked = :today
                                      ORDER BY TimeIn DESC";
                        $queryToday = $dbh->prepare($sqlToday);
                        $queryToday->bindParam(':empid', $employee->EmpId, PDO::PARAM_STR);
                        $queryToday->bindParam(':today', $today);
                        $queryToday->execute();
                        $resultsToday = $queryToday->fetchAll(PDO::FETCH_OBJ);
                        
                        if(count($resultsToday) > 0){
                            foreach($resultsToday as $rowT){
                                $timeInDisplay = $rowT->TimeIn ? date("h:i A", strtotime($rowT->TimeIn)) : "-";
                                $timeOutDisplay = $rowT->TimeOut ? date("h:i A", strtotime($rowT->TimeOut)) : "---";
                                $hoursDisplay = $rowT->HoursWorked ? round($rowT->HoursWorked, 2) : "0";
                                ?>
                                <table class="table table-borderless">
                                    <tr><td><strong>Time In:</strong></td><td><?php echo $timeInDisplay; ?></td></tr>
                                    <tr><td><strong>Time Out:</strong></td><td><?php echo $timeOutDisplay; ?></td></tr>
                                    <tr><td><strong>Hours:</strong></td><td><?php echo $hoursDisplay; ?> hrs</td></tr>
                                </table>
                                <?php
                            }
                        } else {
                            echo '<p class="text-muted">No attendance recorded today.</p>';
                        }
                        ?>
                    </div>
                </div>
            </div>
            
            <!-- MONTHLY SALARY -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="ti-money"></i> Monthly Salary</h5>
                    </div>
                    <div class="card-body text-center">
                        <h2 class="text-success">₱ <?php echo number_format($monthly_total, 2); ?></h2>
                        <p class="text-muted">For <?php echo date("F Y", strtotime("$year-$month-01")); ?></p>
                        <small class="text-muted">Daily Rate: ₱<?php echo $daily_rate; ?> | OT Rate: ₱<?php echo $ot_rate; ?>/hr</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- MONTH NAVIGATION & ATTENDANCE TABLE -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <!-- Month Navigation -->
                            <div class="mb-3 text-center">
                                <a href="?month=<?php echo $prev_month;?>&year=<?php echo $prev_year;?>" class="btn btn-secondary">&lt; Prev</a>
                                <strong style="margin:0 15px;"><?php echo date("F Y", strtotime("$year-$month-01")); ?></strong>
                                <a href="?month=<?php echo $next_month;?>&year=<?php echo $next_year;?>" class="btn btn-secondary">Next &gt;</a>
                            </div>

                        <!-- Attendance Table -->
                        <h4 class="header-title">Attendance Records</h4>
                        <div class="data-tables datatable-dark">
                            <table id="attendanceTable" class="table table-hover table-striped text-center">
                                <thead>
                                    <tr>
                                            <th>#</th>
                                            <th>Date</th>
                                            <th>Time In</th>
                                            <th>Time Out</th>
                                            <th>Hours</th>
                                            <th>OT</th>
                                            <th>Pay</th>
                                        </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $cnt=1;
                                    foreach($results as $row){
                                        $timein_f = $row->TimeIn ? date("h:i A", strtotime($row->TimeIn)) : "-";
                                        $timeout_f = $row->TimeOut ? date("h:i A", strtotime($row->TimeOut)) : "-";
                                        $hours = $row->HoursWorked ? round($row->HoursWorked, 2) : 0;

                                        $regular = ($hours > HOURS_PER_DAY) ? HOURS_PER_DAY : $hours;
                                        $ot_hours = ($hours > HOURS_PER_DAY) ? round($hours - HOURS_PER_DAY, 2) : 0;
                        
                                        $pay = round($regular * ($daily_rate / HOURS_PER_DAY), 2) + round($ot_hours * $ot_rate, 2);
                                        ?>
                                        <tr>
                                            <td><?php echo $cnt++; ?></td>
                                            <td><?php echo date("M d, Y", strtotime($row->DateWorked)); ?></td>
                                            <td><?php echo $timein_f; ?></td>
                                            <td><?php echo $timeout_f; ?></td>
                                            <td><?php echo $hours; ?></td>
                                            <td><?php echo $ot_hours; ?></td>
                                            <td>₱<?php echo number_format($pay, 2); ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- LEAVE APPLICATION ROW -->
        <div class="row mt-4">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title">Apply for Leave</h4>
                        <p class="text-muted font-14 mb-4">Submit a new leave application</p>

                        <form method="POST">
                            <?php if(isset($error) && $error){?><div class="alert alert-danger alert-dismissible fade show"><strong>Error: </strong><?php echo htmlentities($error); ?>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                             </div><?php } 
                             else if(isset($msg) && $msg){?><div class="alert alert-success alert-dismissible fade show"><strong>Success: </strong><?php echo htmlentities($msg); ?> 
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                             </div><?php }?>

                            <div class="form-group">
                                <label>Leave Type</label>
                                <select class="custom-select" name="leavetype" required>
                                    <option value="">Choose..</option>
                                    <?php 
                                    $sql = "SELECT LeaveType FROM tblleavetype";
                                    $query = $dbh->prepare($sql);
                                    $query->execute();
                                    $types = $query->fetchAll(PDO::FETCH_OBJ);
                                    foreach($types as $type){ ?> 
                                        <option value="<?php echo htmlentities($type->LeaveType);?>"><?php echo htmlentities($type->LeaveType);?></option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>From Date</label>
                                <input class="form-control" name="fromdate" type="date" required>
                            </div>

                            <div class="form-group">
                                <label>To Date</label>
                                <input class="form-control" name="todate" type="date" required>
                            </div>

                            <div class="form-group">
                                <label>Description</label>
                                <textarea class="form-control" name="description" rows="3" required></textarea>
                            </div>

                            <button class="btn btn-primary" name="apply" type="submit">APPLY LEAVE</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
$(document).ready(function(){
    // Check current status on load
    checkStatus();

    $('#timein-btn').click(function(){
        $.post('time_action.php', {timein: true}, function(data){
            $('#message').html('<div class="alert alert-success">'+data.msg+'</div>');
            if(data.error) $('#message').append('<div class="alert alert-danger">'+data.error+'</div>');
            checkStatus();
        }, 'json');
    });

    $('#timeout-btn').click(function(){
        $.post('time_action.php', {timeout: true}, function(data){
            $('#message').html('<div class="alert alert-success">'+data.msg+'</div>');
            if(data.error) $('#message').append('<div class="alert alert-danger">'+data.error+'</div>');
            checkStatus();
        }, 'json');
    });

    $('#force-timeout-btn').click(function(){
        if(confirm('Are you sure you want to time out early? This may affect your pay.')){
            $.post('time_action.php', {force_timeout: true}, function(data){
                $('#message').html('<div class="alert alert-success">'+data.msg+'</div>');
                if(data.error) $('#message').append('<div class="alert alert-danger">'+data.error+'</div>');
                checkStatus();
            }, 'json');
        }
    });

    function checkStatus(){
        $.post('time_action.php', {check: true}, function(data){
            if(data.timedin){
                $('#timein-btn').prop('disabled', true);
                $('#timeout-btn').prop('disabled', false);
                $('#force-timeout-btn').prop('disabled', false);
                $('#status').html('<div class="alert alert-info">Time In: '+data.timein+'</div>');
            } else {
                $('#timein-btn').prop('disabled', false);
                $('#timeout-btn').prop('disabled', true);
                $('#force-timeout-btn').prop('disabled', true);
                $('#status').html('<div class="alert alert-warning">Not timed in</div>');
            }
        }, 'json');
    }
});
</script>

<?php include '../includes/employee-footer.php'; ?>
