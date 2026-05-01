<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Prevent browser caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

include('../includes/dbconn.php');
include('../includes/config.php');

if(strlen($_SESSION['emplogin'])==0){
    header('location:../index.php');
    exit;
}

$eid = $_SESSION['eid'];
$empid = $_SESSION['emplogin'];
$msg = $error = "";

// Create tbltime_logs table if not exists
$dbh->exec("CREATE TABLE IF NOT EXISTS tbltime_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    EmpID VARCHAR(50) NOT NULL,
    DateWorked DATE NOT NULL,
    TimeIn TIME NULL,
    TimeOut TIME NULL,
    HoursWorked DECIMAL(10,2) DEFAULT 0,
    Status VARCHAR(20) DEFAULT 'Pending',
    INDEX (EmpID),
    INDEX (DateWorked)
)");

// Handle time in/out
if(isset($_POST['time_action'])) {
    $action = $_POST['action'];
    $today = date('Y-m-d');
    
    if($action == 'time_in') {
        // Check if already timed in today
        $sql = "SELECT * FROM tbltime_logs WHERE EmpID=:empid AND DateWorked=:date ORDER BY id DESC LIMIT 1";
        $query = $dbh->prepare($sql);
        $query->bindParam(':empid', $empid, PDO::PARAM_STR);
        $query->bindParam(':date', $today, PDO::PARAM_STR);
        $query->execute();
        $existing = $query->fetch(PDO::FETCH_OBJ);
        
        // Check if can time in (either no record, or 8 hours have passed since last TimeIn)
        $canTimeIn = true;
        if($existing) {
            if($existing->TimeOut == NULL) {
                $canTimeIn = false;
                $error = "You are already timed in. Please time out first.";
            } else {
                // Check if 8 hours have passed since TimeIn
                $timeInTs = strtotime($existing->TimeIn);
                $timeOutTs = strtotime($existing->TimeOut);
                $eightHoursLater = $timeInTs + (8 * 3600);
                
                if(time() < $eightHoursLater) {
                    $canTimeIn = false;
                    $remaining = ceil(($eightHoursLater - time()) / 60);
                    $error = "You must wait " . $remaining . " more minutes before you can time in again.";
                }
            }
        }
        
        if($canTimeIn) {
            $timeIn = date('H:i:s');
            $sql = "INSERT INTO tbltime_logs (EmpID, DateWorked, TimeIn, Status) VALUES(:empid, :date, :timein, 'Pending')";
            $query = $dbh->prepare($sql);
            $query->bindParam(':empid', $empid, PDO::PARAM_STR);
            $query->bindParam(':date', $today, PDO::PARAM_STR);
            $query->bindParam(':timein', $timeIn, PDO::PARAM_STR);
            $query->execute();
            
            if($query->rowCount() > 0) {
                $msg = "Time In recorded at " . date('h:i A', strtotime($timeIn));
            } else {
                $error = "Failed to record Time In. Please try again.";
            }
        }
    } elseif($action == 'time_out' || $action == 'time_out_anyway') {
        $sql = "SELECT * FROM tbltime_logs WHERE EmpID=:empid AND DateWorked=:date ORDER BY id DESC LIMIT 1";
        $query = $dbh->prepare($sql);
        $query->bindParam(':empid', $empid, PDO::PARAM_STR);
        $query->bindParam(':date', $today, PDO::PARAM_STR);
        $query->execute();
        $record = $query->fetch(PDO::FETCH_OBJ);
        
        if($record) {
            if($record->TimeOut != NULL) {
                $error = "You have already timed out today.";
            } else {
                $timeOut = date('H:i:s');
                if($record->TimeIn) {
                    $timeIn = strtotime($record->TimeIn);
                    $timeOutTs = strtotime($timeOut);
                    $hoursWorked = round(($timeOutTs - $timeIn) / 3600, 2);
                } else {
                    $hoursWorked = 0;
                }
                
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
            }
        } else {
            $error = "No Time In record found for today.";
        }
    }
}

// Get today's record
$today = date('Y-m-d');
$sql = "SELECT * FROM tbltime_logs WHERE EmpID=:empid AND DateWorked=:date ORDER BY id DESC LIMIT 1";
$query = $dbh->prepare($sql);
$query->bindParam(':empid', $empid, PDO::PARAM_STR);
$query->bindParam(':date', $today, PDO::PARAM_STR);
$query->execute();
$todayRecord = $query->fetch(PDO::FETCH_OBJ);

// Calculate button states
$canTimeIn = true;
$canTimeOut = false;
$canTimeOutAnyway = true;
$timeInTimestamp = 0;
$eightHoursPassed = false;

if($todayRecord) {
    if($todayRecord->TimeOut == NULL) {
        // Currently timed in
        $canTimeIn = false;
        $canTimeOutAnyway = true;
        
        // Check if 8 hours have passed
        if($todayRecord->TimeIn) {
            $timeInTimestamp = strtotime($todayRecord->TimeIn);
            $eightHoursLater = $timeInTimestamp + (8 * 3600);
            if(time() >= $eightHoursLater) {
                $canTimeOut = true;
                $eightHoursPassed = true;
            }
        }
    } else {
        // Already timed out
        $canTimeOut = false;
        $canTimeOutAnyway = false;
        
        // Check if 8 hours have passed since TimeIn (can time in again)
        if($todayRecord->TimeIn) {
            $timeInTimestamp = strtotime($todayRecord->TimeIn);
            $eightHoursLater = $timeInTimestamp + (8 * 3600);
            if(time() >= $eightHoursLater) {
                $canTimeIn = true;
            }
        }
    }
}

// Month filter for salary/attendance
$month = isset($_GET['month']) ? intval($_GET['month']) : date('m');
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$from = date("Y-m-01", strtotime("$year-$month-01"));
$to = date("Y-m-t", strtotime($from));

// Get monthly records
$sql = "SELECT * FROM tbltime_logs WHERE EmpID=:empid AND DateWorked BETWEEN :f AND :t ORDER BY DateWorked DESC";
$query = $dbh->prepare($sql);
$query->bindParam(':empid', $empid, PDO::PARAM_STR);
$query->bindParam(':f', $from);
$query->bindParam(':t', $to);
$query->execute();
$records = $query->fetchAll(PDO::FETCH_OBJ);

// Calculate totals
$total_hours = 0;
$total_pay = 0;
foreach($records as $row){
    $pay = ($row->HoursWorked * DAILY_RATE) / 8;
    $total_hours += $row->HoursWorked;
    $total_pay += $pay;
}

// Get deduction rates from database (set by admin as percentages)
$sss_rate = 0;
$philhealth_rate = 0;
$pagibig_rate = 0;
$tax_rate = 0;

try {
    $deductions = $dbh->query("SELECT * FROM deduction_rates")->fetchAll(PDO::FETCH_OBJ);
    foreach($deductions as $d){
        if($d->type == 'SSS') $sss_rate = $d->rate;
        if($d->type == 'PhilHealth') $philhealth_rate = $d->rate;
        if($d->type == 'Pag-IBIG') $pagibig_rate = $d->rate;
        if($d->type == 'Withholding Tax') $tax_rate = $d->rate;
    }
} catch (Exception $e) {
    // Use default values (0%)
}

// Calculate deductions as percentages of gross pay
$sss = $total_pay * ($sss_rate / 100);
$philhealth = $total_pay * ($philhealth_rate / 100);
$pagibig = $total_pay * ($pagibig_rate / 100);
$tax = $total_pay * ($tax_rate / 100);
$total_deductions = $sss + $philhealth + $pagibig + $tax;
$net_pay = $total_pay - $total_deductions;

$page='payroll';
include('../includes/employee-header.php');
?>

<div class="main-content-inner">
    <div class="row">
        <div class="col-12 mt-4">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title mb-3"><i class="ti-money text-primary"></i> Payroll Dashboard</h4>
                    
                    <!-- Alerts -->
                    <?php if($error){ ?><div class="alert alert-danger alert-dismissible fade show"><strong>Error: </strong><?php echo htmlentities($error); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div><?php } ?>
                    <?php if($msg){ ?><div class="alert alert-success alert-dismissible fade show"><strong>Success: </strong><?php echo htmlentities($msg); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div><?php } ?>

                    <!-- Summary Cards Row -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-light-primary">
                                <div class="card-body text-center">
                                    <i class="ti-time fa-2x text-primary mb-2"></i>
                                    <h6>Today's Status</h6>
                                    <?php if($todayRecord && $todayRecord->TimeOut == NULL) { ?>
                                        <span class="badge badge-success">Timed In</span>
                                    <?php } elseif($todayRecord && $todayRecord->TimeOut != NULL) { ?>
                                        <span class="badge badge-info">Completed</span>
                                    <?php } else { ?>
                                        <span class="badge badge-warning">Not Yet</span>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light-success">
                                <div class="card-body text-center">
                                    <i class="ti-calendar fa-2x text-success mb-2"></i>
                                    <h6>This Month</h6>
                                    <h4><?php echo count($records); ?> Days</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light-info">
                                <div class="card-body text-center">
                                    <i class="ti-stats-up fa-2x text-info mb-2"></i>
                                    <h6>Total Hours</h6>
                                    <h4><?php echo round($total_hours, 2); ?> hrs</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light-danger">
                                <div class="card-body text-center">
                                    <i class="ti-wallet fa-2x text-danger mb-2"></i>
                                    <h6>Net Pay</h6>
                                    <h4>₱<?php echo number_format($net_pay, 2); ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabs -->
                    <ul class="nav nav-pills mb-4" id="payrollTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="time-tab" data-toggle="pill" href="#time" role="tab">
                                <i class="ti-time"></i> Time In/Out
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="today-tab" data-toggle="pill" href="#today" role="tab">
                                <i class="ti-calendar-check"></i> Today's Attendance
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="salary-tab" data-toggle="pill" href="#salary" role="tab">
                                <i class="ti-stats-up"></i> Monthly Salary
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="payslip-tab" data-toggle="pill" href="#payslip" role="tab">
                                <i class="ti-clipboard"></i> Payslip
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="record-tab" data-toggle="pill" href="#record" role="tab">
                                <i class="ti-list"></i> Attendance Record
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content" id="payrollTabContent">
                        <!-- Time In/Out Tab -->
                        <div class="tab-pane fade show active" id="time" role="tabpanel">
                            <div class="row">
                                <div class="col-lg-6" style="margin:auto;">
                                    <div class="card">
                                        <div class="card-body text-center">
                                            <h4 class="header-title">Time In / Time Out</h4>
                                            <p class="text-muted font-14 mb-4">Today: <?php echo date('F d, Y'); ?></p>
                                            
                                            <div class="mb-4">
                                                <h5>Current Time: <span id="currentTime"><?php echo date('h:i:s A'); ?></span></h5>
                                                <div id="countdownTimer" class="mt-2"></div>
                                            </div>

                                            <?php if($todayRecord) { ?>
                                                <div class="alert alert-info">
                                                    <strong>Time In:</strong> <?php echo date('h:i A', strtotime($todayRecord->TimeIn)); ?>
                                                    <?php if($todayRecord->TimeOut) { ?>
                                                        <br><strong>Time Out:</strong> <?php echo date('h:i A', strtotime($todayRecord->TimeOut)); ?>
                                                        <br><strong>Hours Worked:</strong> <?php echo round($todayRecord->HoursWorked, 2); ?> hrs
                                                    <?php } else { ?>
                                                        <br><strong>Status:</strong> Currently timed in
                                                    <?php } ?>
                                                </div>
                                            <?php } ?>

                                            <form method="POST" class="mb-3">
                                                <input type="hidden" name="action" value="time_in">
                                                <button class="btn btn-success btn-lg btn-block" name="time_action" type="submit" id="timeInBtn" <?php echo !$canTimeIn ? 'disabled' : ''; ?>>
                                                    <i class="ti-control-play"></i> TIME IN
                                                </button>
                                            </form>

                                            <form method="POST" class="mb-3">
                                                <input type="hidden" name="action" value="time_out">
                                                <button class="btn btn-danger btn-lg btn-block" name="time_action" type="submit" id="timeOutBtn" <?php echo !$canTimeOut ? 'disabled' : ''; ?>>
                                                    <i class="ti-control-stop"></i> TIME OUT
                                                </button>
                                            </form>

                                            <form method="POST" onsubmit="return confirm('Are you sure you want to time out anyway? You will only be paid for the actual hours worked.');">
                                                <input type="hidden" name="action" value="time_out_anyway">
                                                <button class="btn btn-warning btn-lg btn-block" name="time_action" type="submit" id="timeOutAnywayBtn" <?php echo !$canTimeOutAnyway ? 'disabled' : ''; ?>>
                                                    <i class="ti-control-stop"></i> TIME OUT ANYWAY
                                                </button>
                                            </form>
                                            
                                            <?php if($todayRecord && $todayRecord->TimeOut == NULL && !$eightHoursPassed) { ?>
                                                <small class="text-muted mt-2 d-block">TIME OUT will be available after 8 hours of work</small>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Today's Attendance Tab -->
                        <div class="tab-pane fade" id="today" role="tabpanel">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="header-title">Today's Attendance - <?php echo date('F d, Y'); ?></h4>
                                    <?php if($todayRecord) { ?>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <table class="table table-bordered">
                                                    <tr><td><strong>Time In</strong></td><td><?php echo date('h:i A', strtotime($todayRecord->TimeIn)); ?></td></tr>
                                                    <tr><td><strong>Time Out</strong></td><td><?php echo $todayRecord->TimeOut ? date('h:i A', strtotime($todayRecord->TimeOut)) : '<span class="text-warning">Not yet</span>'; ?></td></tr>
                                                    <tr><td><strong>Hours Worked</strong></td><td><?php echo $todayRecord->HoursWorked ? round($todayRecord->HoursWorked, 2) . ' hrs' : '<span class="text-warning">In progress</span>'; ?></td></tr>
                                                    <tr><td><strong>Status</strong></td><td><span class="badge badge-<?php echo $todayRecord->Status=='Approved'?'success':'warning'; ?>"><?php echo $todayRecord->Status; ?></span></td></tr>
                                                    <tr><td><strong>Daily Pay</strong></td><td>₱<?php echo number_format(($todayRecord->HoursWorked * DAILY_RATE) / 8, 2); ?></td></tr>
                                                </table>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="card bg-primary text-white">
                                                    <div class="card-body text-center">
                                                        <h6>Today's Earnings</h6>
                                                        <h2>₱<?php echo number_format(($todayRecord->HoursWorked * DAILY_RATE) / 8, 2); ?></h2>
                                                        <p class="mb-0">Based on ₱<?php echo DAILY_RATE; ?>/day rate</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } else { ?>
                                        <div class="text-center py-5">
                                            <i class="ti-time fa-4x text-muted mb-3"></i>
                                            <h5 class="text-muted">No attendance record for today</h5>
                                            <p>Click TIME IN to start your day</p>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>

                        <!-- Monthly Salary Tab -->
                        <div class="tab-pane fade" id="salary" role="tabpanel">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="header-title">Monthly Salary - <?php echo date('F Y', strtotime("$year-$month-01")); ?></h4>
                                    
                                    <form method="GET" class="form-inline mb-4">
                                        <input type="hidden" name="tab" value="salary">
                                        <select name="month" class="form-control mr-2">
                                            <?php for($m=1; $m<=12; $m++){ ?>
                                                <option value="<?php echo $m; ?>" <?php echo ($m==$month)?'selected':''; ?>>
                                                    <?php echo date('F', mktime(0,0,0,$m,1)); ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                        <select name="year" class="form-control mr-2">
                                            <?php for($y=date('Y')-2; $y<=date('Y'); $y++){ ?>
                                                <option value="<?php echo $y; ?>" <?php echo ($y==$year)?'selected':''; ?>><?php echo $y; ?></option>
                                            <?php } ?>
                                        </select>
                                        <button type="submit" class="btn btn-primary">View</button>
                                    </form>

                                    <div class="row">
                                        <div class="col-md-8">
                                            <h5>Salary Details</h5>
                                            <table class="table table-bordered">
                                                <tr><td>Total Hours Worked</td><td><?php echo round($total_hours, 2); ?> hrs</td></tr>
                                                <tr><td>Days Worked</td><td><?php echo count($records); ?> days</td></tr>
                                                <tr><td>Gross Pay (₱<?php echo DAILY_RATE; ?>/day)</td><td>₱<?php echo number_format($total_pay, 2); ?></td></tr>
                                            </table>

                                            <h5 class="mt-4">Deductions (based on admin settings)</h5>
                                            <table class="table table-bordered">
                                                <tr><td>SSS (<?php echo $sss_rate; ?>%)</td><td>₱<?php echo number_format($sss, 2); ?></td></tr>
                                                <tr><td>PhilHealth (<?php echo $philhealth_rate; ?>%)</td><td>₱<?php echo number_format($philhealth, 2); ?></td></tr>
                                                <tr><td>Pag-IBIG (<?php echo $pagibig_rate; ?>%)</td><td>₱<?php echo number_format($pagibig, 2); ?></td></tr>
                                                <tr><td>Withholding Tax (<?php echo $tax_rate; ?>%)</td><td>₱<?php echo number_format($tax, 2); ?></td></tr>
                                                <tr class="bg-light"><th>Total Deductions</th><th>₱<?php echo number_format($total_deductions, 2); ?></th></tr>
                                            </table>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card bg-success text-white">
                                                <div class="card-body text-center">
                                                    <h6>Net Pay</h6>
                                                    <h2>₱<?php echo number_format($net_pay, 2); ?></h2>
                                                    <p class="mb-0"><?php echo date('F Y', strtotime("$year-$month-01")); ?></p>
                                                </div>
                                            </div>
                                            <div class="card mt-3">
                                                <div class="card-body">
                                                    <h6>Summary</h6>
                                                    <p><strong>Daily Rate:</strong> ₱<?php echo DAILY_RATE; ?></p>
                                                    <p><strong>OT Rate:</strong> ₱<?php echo OT_RATE; ?>/hr</p>
                                                    <p><strong>Hours/Day:</strong> <?php echo HOURS_PER_DAY; ?> hrs</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payslip Tab -->
                        <div class="tab-pane fade" id="payslip" role="tabpanel">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="header-title">My Payslip - <?php echo date('F Y', strtotime("$year-$month-01")); ?></h4>
                                    
                                    <form method="GET" class="form-inline mb-4">
                                        <input type="hidden" name="tab" value="payslip">
                                        <select name="month" class="form-control mr-2">
                                            <?php for($m=1; $m<=12; $m++){ ?>
                                                <option value="<?php echo $m; ?>" <?php echo ($m==$month)?'selected':''; ?>>
                                                    <?php echo date('F', mktime(0,0,0,$m,1)); ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                        <select name="year" class="form-control mr-2">
                                            <?php for($y=date('Y')-2; $y<=date('Y'); $y++){ ?>
                                                <option value="<?php echo $y; ?>" <?php echo ($y==$year)?'selected':''; ?>><?php echo $y; ?></option>
                                            <?php } ?>
                                        </select>
                                        <button type="submit" class="btn btn-primary">View</button>
                                    </form>

                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead class="bg-primary text-white">
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Time In</th>
                                                    <th>Time Out</th>
                                                    <th>Hours Worked</th>
                                                    <th>Daily Pay</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach($records as $row){ 
                                                    $pay = ($row->HoursWorked * DAILY_RATE) / 8;
                                                ?>
                                                    <tr>
                                                        <td><?php echo date('M d, Y', strtotime($row->DateWorked)); ?></td>
                                                        <td><?php echo $row->TimeIn ? date('h:i A', strtotime($row->TimeIn)) : '-'; ?></td>
                                                        <td><?php echo $row->TimeOut ? date('h:i A', strtotime($row->TimeOut)) : '-'; ?></td>
                                                        <td><?php echo round($row->HoursWorked, 2); ?> hrs</td>
                                                        <td>₱<?php echo number_format($pay, 2); ?></td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                            <tfoot>
                                                <tr class="bg-light">
                                                    <th colspan="3">Total</th>
                                                    <th><?php echo round($total_hours, 2); ?> hrs</th>
                                                    <th>₱<?php echo number_format($total_pay, 2); ?></th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>

                                    <div class="row mt-4">
                                        <div class="col-md-6">
                                            <h5>Deductions (based on admin settings)</h5>
                                            <table class="table table-sm">
                                                <tr><td>SSS (<?php echo $sss_rate; ?>%)</td><td>₱<?php echo number_format($sss, 2); ?></td></tr>
                                                <tr><td>PhilHealth (<?php echo $philhealth_rate; ?>%)</td><td>₱<?php echo number_format($philhealth, 2); ?></td></tr>
                                                <tr><td>Pag-IBIG (<?php echo $pagibig_rate; ?>%)</td><td>₱<?php echo number_format($pagibig, 2); ?></td></tr>
                                                <tr><td>Withholding Tax (<?php echo $tax_rate; ?>%)</td><td>₱<?php echo number_format($tax, 2); ?></td></tr>
                                                <tr class="bg-light"><th>Total Deductions</th><th>₱<?php echo number_format($total_deductions, 2); ?></th></tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card bg-success text-white">
                                                <div class="card-body">
                                                    <h3>Net Pay: ₱<?php echo number_format($net_pay, 2); ?></h3>
                                                    <p class="mb-0">For <?php echo date('F Y', strtotime("$year-$month-01")); ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Attendance Record Tab -->
                        <div class="tab-pane fade" id="record" role="tabpanel">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="header-title">Attendance Record - <?php echo date('F Y', strtotime("$year-$month-01")); ?></h4>
                                    
                                    <form method="GET" class="form-inline mb-4">
                                        <input type="hidden" name="tab" value="record">
                                        <select name="month" class="form-control mr-2">
                                            <?php for($m=1; $m<=12; $m++){ ?>
                                                <option value="<?php echo $m; ?>" <?php echo ($m==$month)?'selected':''; ?>>
                                                    <?php echo date('F', mktime(0,0,0,$m,1)); ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                        <select name="year" class="form-control mr-2">
                                            <?php for($y=date('Y')-2; $y<=date('Y'); $y++){ ?>
                                                <option value="<?php echo $y; ?>" <?php echo ($y==$year)?'selected':''; ?>><?php echo $y; ?></option>
                                            <?php } ?>
                                        </select>
                                        <button type="submit" class="btn btn-primary">Filter</button>
                                    </form>

                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered" id="attendanceTable">
                                            <thead class="bg-primary text-white">
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Time In</th>
                                                    <th>Time Out</th>
                                                    <th>Hours Worked</th>
                                                    <th>Status</th>
                                                    <th>Daily Pay</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach($records as $row){
                                                    $pay = ($row->HoursWorked * DAILY_RATE) / 8;
                                                ?>
                                                    <tr>
                                                        <td><?php echo date('M d, Y', strtotime($row->DateWorked)); ?></td>
                                                        <td><?php echo $row->TimeIn ? date('h:i A', strtotime($row->TimeIn)) : '-'; ?></td>
                                                        <td><?php echo $row->TimeOut ? date('h:i A', strtotime($row->TimeOut)) : '-'; ?></td>
                                                        <td><?php echo round($row->HoursWorked, 2); ?> hrs</td>
                                                        <td>
                                                            <?php if($row->Status == 'Approved'){ ?>
                                                                <span class="badge badge-success">Approved</span>
                                                            <?php } elseif($row->Status == 'Pending'){ ?>
                                                                <span class="badge badge-warning">Pending</span>
                                                            <?php } else { ?>
                                                                <span class="badge badge-danger"><?php echo $row->Status; ?></span>
                                                            <?php } ?>
                                                        </td>
                                                        <td>₱<?php echo number_format($pay, 2); ?></td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                            <tfoot>
                                                <tr class="bg-light">
                                                    <th colspan="3">Total</th>
                                                    <th><?php echo round($total_hours, 2); ?> hrs</th>
                                                    <th colspan="2">₱<?php echo number_format($total_pay, 2); ?></th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Update current time
setInterval(function() {
    var now = new Date();
    var time = now.toLocaleTimeString('en-US', {hour12: true});
    document.getElementById('currentTime').textContent = time;
}, 1000);

// Countdown timer for TIME OUT button
<?php if($todayRecord && $todayRecord->TimeOut == NULL && $timeInTimestamp > 0) { ?>
var timeInTimestamp = <?php echo $timeInTimestamp * 1000; ?>;
var eightHoursLater = timeInTimestamp + (8 * 60 * 60 * 1000);

function updateCountdown() {
    var now = new Date().getTime();
    var timeLeft = eightHoursLater - now;
    
    var timeOutBtn = document.getElementById('timeOutBtn');
    
    if(timeLeft <= 0) {
        timeOutBtn.disabled = false;
        timeOutBtn.classList.remove('disabled');
        document.getElementById('countdownTimer').innerHTML = '<div class="alert alert-success">You can now TIME OUT</div>';
        return;
    }
    
    var hours = Math.floor(timeLeft / (1000 * 60 * 60));
    var minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
    var seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);
    
    document.getElementById('countdownTimer').innerHTML = 
        '<div class="alert alert-warning">Time until TIME OUT is available: ' + 
        hours + 'h ' + minutes + 'm ' + seconds + 's</div>';
    
    setTimeout(updateCountdown, 1000);
}

updateCountdown();
<?php } ?>

// Handle tab persistence via URL
$(document).ready(function(){
    // Check if there's a tab parameter in URL
    const urlParams = new URLSearchParams(window.location.search);
    const tab = urlParams.get('tab');
    if(tab) {
        $('#payrollTab a[href="#' + tab + '"]').tab('show');
    }
    
    // Update URL when tab changes
    $('#payrollTab a').on('shown.bs.tab', function (e) {
        var tabId = $(e.target).attr('href').substring(1);
        if(history.replaceState) {
            history.replaceState(null, null, '?tab=' + tabId);
        }
    });
    
    // Initialize DataTable for attendance record (if library is loaded)
    if(typeof $.fn.DataTable !== 'undefined' && $('#attendanceTable').length) {
        $('#attendanceTable').DataTable({
            "pageLength": 10,
            "order": [[0, "desc"]]
        });
    }
});
</script>

<?php include '../includes/employee-footer.php'; ?>
