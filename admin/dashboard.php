<?php
session_start();
error_reporting(0);
include('../includes/dbconn.php');
include('../includes/config.php');
include('../includes/functions.php');

// Redirect if not logged in
if(strlen($_SESSION['alogin'])==0){   
    header('location:../index.php');
    exit;
}

// Default daily and OT rates from config
$daily_rate = DAILY_RATE;
$ot_rate = OT_RATE;

// Create settings table if not exists
$sqlCreate = "CREATE TABLE IF NOT EXISTS settings (
    id INT PRIMARY KEY,
    daily_rate DECIMAL(10,2) DEFAULT 500,
    ot_rate DECIMAL(10,2) DEFAULT 50
)";
$dbh->exec($sqlCreate);

// Load rates from database
$sqlLoad = "SELECT daily_rate, ot_rate FROM settings WHERE id=1";
$queryLoad = $dbh->prepare($sqlLoad);
$queryLoad->execute();
$rowSettings = $queryLoad->fetch(PDO::FETCH_OBJ);
if($rowSettings){
    $daily_rate = $rowSettings->daily_rate;
    $ot_rate = $rowSettings->ot_rate;
}

// Create settings table if not exists
$dbh->exec("CREATE TABLE IF NOT EXISTS settings (
    id INT PRIMARY KEY,
    daily_rate DECIMAL(10,2) DEFAULT 500,
    ot_rate DECIMAL(10,2) DEFAULT 50
)");
$check = $dbh->query("SELECT COUNT(*) FROM settings");
if($check->fetchColumn() == 0) {
    $dbh->exec("INSERT INTO settings (id, daily_rate, ot_rate) VALUES (1, 500, 50)");
}

// Handle saving rates
if(isset($_POST['save'])){
    $daily_rate = floatval($_POST['daily_rate']);
    $ot_rate = floatval($_POST['ot_rate']);

    $sqlSave = "INSERT INTO settings (id, daily_rate, ot_rate) VALUES (1, :dr, :or)
               ON DUPLICATE KEY UPDATE daily_rate=:dr, ot_rate=:or";
    $querySave = $dbh->prepare($sqlSave);
    $querySave->bindParam(':dr', $daily_rate);
    $querySave->bindParam(':or', $ot_rate);
    $querySave->execute();

    $msg = "Rates updated successfully!";
}

// Month navigation
if(isset($_GET['month']) && isset($_GET['year'])){
    $month = intval($_GET['month']);
    $year = intval($_GET['year']);
} else {
    $month = date('m');
    $year = date('Y');
}

// Compute first and last day of month
$from = date("Y-m-01", strtotime("$year-$month-01"));
$to   = date("Y-m-t", strtotime($from));

// Month navigation buttons
$prev_month = $month-1; $prev_year=$year;
if($prev_month<1){ $prev_month=12; $prev_year=$year-1; }

$next_month = $month+1; $next_year=$year;
if($next_month>12){ $next_month=1; $next_year=$year+1; }

$page='payroll'; include('../includes/admin-header.php');
?>

    <!-- page title area start -->
    <div class="page-title-area">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <div class="breadcrumbs-area clearfix">
                    <h4 class="page-title pull-left">Payroll - Attendance</h4>
                    <ul class="breadcrumbs pull-left">
                        <li><a href="dashboard.php">Home</a></li>
                        <li><span>Payroll</span></li>
                    </ul>
                </div>
            </div>
            
            <div class="col-sm-6 clearfix">
                <?php include '../includes/admin-profile-section.php'; ?>
            </div>
        </div>
    </div>
    <!-- page title area end -->

        <!-- Main content inner -->
        <div class="main-content-inner">
            <div class="row">
                <div class="col-12 mt-5">
                    <div class="card">
                        <div class="card-body">

                        <!-- RATES -->
                        <form method="POST" class="mb-4">
                            <div class="row">
                                <div class="col-md-3">
                                    <label>Daily Salary</label>
                                    <input type="number" step="0.01" name="daily_rate" value="<?php echo $daily_rate; ?>" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label>OT per Hour</label>
                                    <input type="number" step="0.01" name="ot_rate" value="<?php echo $ot_rate; ?>" class="form-control">
                                </div>
                                <div class="col-md-2">
                                    <label>&nbsp;</label>
                                    <button class="btn btn-primary btn-block" name="save">Save Rates</button>
                                </div>
                            </div>
                        </form>

                        <!-- MONTH NAVIGATION -->
                        <div class="mb-3 text-center">
                            <a href="?month=<?php echo $prev_month;?>&year=<?php echo $prev_year;?>" class="btn btn-secondary">&lt; Prev</a>
                            <strong style="margin:0 15px;"><?php echo date("F Y", strtotime("$year-$month-01")); ?></strong>
                            <a href="?month=<?php echo $next_month;?>&year=<?php echo $next_year;?>" class="btn btn-secondary">Next &gt;</a>
                        </div>

                        <!-- PAYROLL TABLE -->
                        <div class="data-tables datatable-dark">
                            <table id="payrollTable" class="table table-hover table-striped text-center">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Total Hours Worked</th>
                                        <th>Total Overtime</th>
                                        <th>Total Salary</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql = "SELECT e.EmpId, CONCAT(e.FirstName,' ',e.LastName) as name,
                                            SUM(tl.HoursWorked) as hours
                                            FROM tblemployees e
                                            LEFT JOIN tbltime_logs tl ON tl.EmpID=e.EmpId AND tl.DateWorked BETWEEN :f AND :t AND tl.Status='Completed'
                                            GROUP BY e.EmpId
                                            ORDER BY e.FirstName";
                                    $query = $dbh->prepare($sql);
                                    $query->bindParam(':f',$from);
                                    $query->bindParam(':t',$to);
                                    $query->execute();
                                    $cnt=1;
                                    while($row=$query->fetch(PDO::FETCH_OBJ)){
                                        $worked_hours = $row->hours ? floatval($row->hours) : 0;
                                        $regular_hours = ($worked_hours > HOURS_PER_DAY) ? HOURS_PER_DAY : $worked_hours;
                                        $ot = ($worked_hours > HOURS_PER_DAY) ? round($worked_hours - HOURS_PER_DAY, 2) : 0;

                                        $daily_pay = ($regular_hours / HOURS_PER_DAY) * $daily_rate;
                                        $ot_pay = $ot * $ot_rate;
                                        $total_pay = $daily_pay + $ot_pay;
                                    ?>
                                    <tr>
                                        <td><?php echo $cnt;?></td>
                                        <td><a href="#" class="view-history" data-empid="<?php echo $row->EmpId;?>"><?php echo htmlentities($row->name);?></a></td>
                                        <td><?php echo round($worked_hours,2);?></td>
                                        <td><?php echo round($ot,2);?></td>
                                        <td><?php echo round($total_pay,2);?></td>
                                    </tr>
                                    <?php $cnt++; } ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- HISTORY TABLE -->
                        <div class="mt-5" id="history-section" style="display:none;">
                            <h5>Attendance History</h5>
                            <table id="historyTable" class="table table-bordered table-striped text-center">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Time In</th>
                                        <th>Time Out</th>
                                        <th>Hours Worked</th>
                                        <th>Overtime</th>
                                        <th>Daily Pay</th>
                                        <th>OT Pay</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody id="historyBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
                         </div>
                         <?php include '../includes/admin-footer.php'; ?>
                         <script>
                         $(document).ready(function(){
                             $('.view-history').click(function(e){
                                 e.preventDefault();
                                 var empid = $(this).data('empid');
                                 $.post("attendance_history.php", {empid: empid, from: "<?php echo $from;?>", to: "<?php echo $to;?>"}, function(data){
                                     $('#historyBody').html(data);
                                     $('#history-section').show();
                                 });
                             });
                         });
                         </script>
