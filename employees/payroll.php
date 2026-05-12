<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

include('../includes/dbconn.php');
include('../includes/config.php');
include('../includes/functions.php');

require_employee_login();

$empid = $_SESSION['emplogin'];
$msg = $error = "";

initialize_database_tables($dbh);

if(isset($_POST['time_action'])) {
    $action = $_POST['action'];
    $result = null;

    if($action == 'time_in') {
        $result = time_in($dbh, $empid);
    } elseif($action == 'time_out') {
        $result = time_out($dbh, $empid, false);
    } elseif($action == 'time_out_anyway') {
        $result = time_out($dbh, $empid, true);
    }

    if($result) {
        if($result['success']) {
            $msg = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
}

$todayRecord = get_today_time_record($dbh, $empid);

$canTimeIn = true;
$canTimeOut = false;
$canTimeOutAnyway = true;
$timeInTimestamp = 0;
$eightHoursPassed = false;

if($todayRecord) {
    if($todayRecord->TimeOut == NULL) {
        $canTimeIn = false;
        $canTimeOutAnyway = true;
        if($todayRecord->TimeIn) {
            $timeInTimestamp = strtotime($todayRecord->TimeIn);
            $eightHoursLater = $timeInTimestamp + (8 * 3600);
            if(time() >= $eightHoursLater) {
                $canTimeOut = true;
                $eightHoursPassed = true;
            }
        }
    } else {
        $canTimeOut = false;
        $canTimeOutAnyway = false;
        if($todayRecord->TimeIn) {
            $timeInTimestamp = strtotime($todayRecord->TimeIn);
            $eightHoursLater = $timeInTimestamp + (8 * 3600);
            if(time() >= $eightHoursLater) {
                $canTimeIn = true;
            }
        }
    }
}

$month = isset($_GET['month']) ? intval($_GET['month']) : date('m');
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

$records = get_monthly_time_records($dbh, $empid, $month, $year);
$payroll = calculate_payroll($records, DAILY_RATE);
$total_hours = $payroll['total_hours'];
$total_pay = $payroll['total_pay'];

$monthly_gross = $total_pay;
$payroll_details = calculate_employee_payroll($monthly_gross, DAILY_RATE);

$sss = $payroll_details['sss'];
$philhealth = $payroll_details['philhealth'];
$pagibig = $payroll_details['pagibig'];
$tax = $payroll_details['withholding_tax'];
$total_deductions = $payroll_details['total_deductions'];
$net_pay = $payroll_details['net_pay'];

$page='payroll';
include('../includes/employee-header.php');
?>

<div class="main-content-inner">
    <div class="row">
        <div class="col-12 mt-4">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title mb-3"><i class="ti-money text-primary"></i> Payroll Dashboard</h4>
                    
                    <?php if($error){ ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <strong>Error: </strong> <?php echo htmlentities($error); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <?php } ?>
                    
                    <?php if($msg){ ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <strong>Success: </strong> <?php echo htmlentities($msg); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <?php } ?>

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
                                                    <tr><td><strong>Status</strong></td><td><span class="badge badge-<?php echo $todayRecord->Status=='Approved'?'success':'warning'; ?>"><?php echo sanitize_input($todayRecord->Status); ?></span></td></tr>
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

                                            <h5 class="mt-4">Deductions (Philippine Standards)</h5>
                                            <table class="table table-bordered">
                                                <tr><td>SSS Contribution</td><td>₱<?php echo number_format($sss, 2); ?></td></tr>
                                                <tr><td>PhilHealth</td><td>₱<?php echo number_format($philhealth, 2); ?></td></tr>
                                                <tr><td>Pag-IBIG</td><td>₱<?php echo number_format($pagibig, 2); ?></td></tr>
                                                <tr><td>Withholding Tax (TRAIN Law)</td><td>₱<?php echo number_format($tax, 2); ?></td></tr>
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

                        <div class="tab-pane fade" id="payslip" role="tabpanel">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <h4 class="header-title">My Payslip - <?php echo date('F Y', strtotime("$year-$month-01")); ?></h4>
                                        <button onclick="window.print()" class="btn btn-success">
                                            <i class="fa fa-print"></i> Print Payslip
                                        </button>
                                    </div>
                                    
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
                                            <h5>Deductions</h5>
                                            <table class="table table-sm">
                                                <tr><td>SSS</td><td>₱<?php echo number_format($sss, 2); ?></td></tr>
                                                <tr><td>PhilHealth</td><td>₱<?php echo number_format($philhealth, 2); ?></td></tr>
                                                <tr><td>Pag-IBIG</td><td>₱<?php echo number_format($pagibig, 2); ?></td></tr>
                                                <tr><td>Withholding Tax</td><td>₱<?php echo number_format($tax, 2); ?></td></tr>
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
                                                                <span class="badge badge-danger"><?php echo sanitize_input($row->Status); ?></span>
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
setInterval(function() {
    var now = new Date();
    var time = now.toLocaleTimeString('en-US', {hour12: true});
    document.getElementById('currentTime').textContent = time;
}, 1000);

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

$(document).ready(function(){
    var urlParams = new URLSearchParams(window.location.search);
    var tab = urlParams.get('tab');
    if(tab) {
        $('#payrollTab a[href="#' + tab + '"]').tab('show');
    }
    
    $('#payrollTab a').on('shown.bs.tab', function (e) {
        var tabId = $(e.target).attr('href').substring(1);
        if(history.replaceState) {
            history.replaceState(null, null, '?tab=' + tabId);
        }
    });
    
    if(typeof $.fn.DataTable !== 'undefined' && $('#attendanceTable').length) {
        $('#attendanceTable').DataTable({
            "pageLength": 10,
            "order": [[0, "desc"]]
        });
    }
});
</script>

<style>
@media print {
    body { background: white !important; }
    .metismenu, .main-footer, .alert, .breadcrumbs, .page-title-area,
    .employee-header, nav, .sidebar, .main-sidebar, .header-area,
    #payrollTab, .form-inline, .nav-pills, .ti-money, .ti-calendar,
    .col-md-3, .bg-light-primary, .bg-light-success, .bg-light-info, .bg-light-danger,
    .tab-content > .tab-pane:not(.show), .tab-content > .tab-pane {
        display: none !important;
    }
    .main-content-inner { padding: 0 !important; margin: 0 !important; }
    .main-panel { padding: 0 !important; }
    .card { border: 2px solid #333 !important; box-shadow: none !important; margin: 0 !important; }
    .tab-content { display: block !important; }
    .tab-pane { display: none !important; }
    .tab-pane#payslip { display: block !important; }
    #payslip .card { page-break-inside: avoid; }
}
</style>

<?php include '../includes/employee-footer.php'; ?>