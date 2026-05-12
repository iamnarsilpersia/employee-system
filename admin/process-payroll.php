<?php
session_start();
include('../includes/dbconn.php');
include('../includes/config.php');
include('../includes/functions.php');

require_admin_login();
initialize_payroll_tables($dbh);

$msg = $error = "";
$processed = [];

$month = isset($_GET['month']) ? intval($_GET['month']) : date('m');
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

if(isset($_POST['process_payroll'])) {
    $month = intval($_POST['month']);
    $year = intval($_POST['year']);
    
    $sql = "SELECT EmpId FROM tblemployees WHERE Status=1";
    $query = $dbh->prepare($sql);
    $query->execute();
    $employees = $query->fetchAll(PDO::FETCH_OBJ);
    
    $daily_rate = 500;
    $ot_rate = 50;
    
    $settings_sql = "SELECT daily_rate, ot_rate FROM settings WHERE id=1";
    $settings_query = $dbh->prepare($settings_sql);
    $settings_query->execute();
    $db_settings = $settings_query->fetch(PDO::FETCH_OBJ);
    if($db_settings) {
        $daily_rate = floatval($db_settings->daily_rate);
        $ot_rate = floatval($db_settings->ot_rate);
    }
    
    $processed_count = 0;
    foreach($employees as $emp) {
        $payroll_data = compute_full_payroll($dbh, $emp->EmpId, $month, $year, [
            'daily_rate' => $daily_rate,
            'ot_rate' => $ot_rate,
            'processed_by' => $_SESSION['aid']
        ]);
        
        if(save_payroll_record($dbh, $payroll_data)) {
            $processed_count++;
        }
    }
    
    $msg = "Successfully processed payroll for $processed_count employees.";
}

$sql = "SELECT p.*, CONCAT(e.FirstName, ' ', e.LastName) as emp_name 
        FROM payroll p 
        LEFT JOIN tblemployees e ON p.empid = e.EmpId 
        WHERE p.period_month = :month AND p.period_year = :year 
        ORDER BY e.FirstName";
$query = $dbh->prepare($sql);
$query->bindParam(':month', $month, PDO::PARAM_INT);
$query->bindParam(':year', $year, PDO::PARAM_INT);
$query->execute();
$payroll_records = $query->fetchAll(PDO::FETCH_OBJ);

$summary = get_payroll_summary($dbh, $month, $year);

$page='payroll';
include('../includes/admin-header.php');
?>

<div class="page-title-area">
    <div class="row align-items-center">
        <div class="col-sm-6">
            <div class="breadcrumbs-area clearfix">
                <h4 class="page-title pull-left">Process Payroll</h4>
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

<div class="main-content-inner">
    <?php if($msg){ ?>
    <div class="alert alert-success alert-dismissible fade show">
        <strong>Success: </strong><?php echo htmlentities($msg); ?>
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
    <?php } ?>

    <div class="row">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h6>Total Employees</h6>
                    <h2><?php echo $summary->total_employees; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h6>Total Gross</h6>
                    <h2>₱<?php echo number_format($summary->total_gross, 2); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h6>Total Deductions</h6>
                    <h2>₱<?php echo number_format($summary->total_deductions, 2); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h6>Total Net Pay</h6>
                    <h2>₱<?php echo number_format($summary->total_net, 2); ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="header-title">Payroll Summary - <?php echo date('F Y', strtotime("$year-$month-01")); ?></h5>
                        <form method="POST" class="form-inline">
                            <input type="hidden" name="process_payroll" value="1">
                            <select name="month" class="form-control mr-2">
                                <?php for($m=1; $m<=12; $m++){ ?>
                                <option value="<?php echo $m; ?>" <?php echo $m==$month?'selected':''; ?>><?php echo date('F', mktime(0,0,0,$m,1)); ?></option>
                                <?php } ?>
                            </select>
                            <select name="year" class="form-control mr-2">
                                <?php for($y=date('Y')-2; $y<=date('Y'); $y++){ ?>
                                <option value="<?php echo $y; ?>" <?php echo $y==$year?'selected':''; ?>><?php echo $y; ?></option>
                                <?php } ?>
                            </select>
                            <button type="submit" class="btn btn-success"><i class="ti-check-box"></i> Process Payroll</button>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table id="payrollTable" class="table table-striped table-bordered">
                            <thead class="bg-primary text-white">
                                <tr>
                                    <th>Employee</th>
                                    <th>Basic Salary</th>
                                    <th>OT Pay</th>
                                    <th>Gross Pay</th>
                                    <th>SSS</th>
                                    <th>PhilHealth</th>
                                    <th>Pag-IBIG</th>
                                    <th>Tax</th>
                                    <th>Other Ded.</th>
                                    <th>Net Pay</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(count($payroll_records) > 0){ ?>
                                    <?php foreach($payroll_records as $row){ ?>
                                    <tr>
                                        <td><strong><?php echo htmlentities($row->emp_name ?? 'N/A'); ?></strong><br><small class="text-muted"><?php echo htmlentities($row->empid); ?></small></td>
                                        <td>₱<?php echo number_format($row->basic_salary, 2); ?></td>
                                        <td>₱<?php echo number_format($row->overtime_pay, 2); ?></td>
                                        <td><strong>₱<?php echo number_format($row->gross_pay, 2); ?></strong></td>
                                        <td>₱<?php echo number_format($row->sss_deduction, 2); ?></td>
                                        <td>₱<?php echo number_format($row->philhealth_deduction, 2); ?></td>
                                        <td>₱<?php echo number_format($row->pagibig_deduction, 2); ?></td>
                                        <td>₱<?php echo number_format($row->tax_deduction, 2); ?></td>
                                        <td>₱<?php echo number_format($row->late_deduction + $row->undertime_deduction + $row->absence_deduction + $row->other_deduction, 2); ?></td>
                                        <td><strong class="text-success">₱<?php echo number_format($row->net_pay, 2); ?></strong></td>
                                        <td>
                                            <a href="payslip.php?id=<?php echo $row->id; ?>" class="btn btn-sm btn-info"><i class="ti-printer"></i> Payslip</a>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                <?php } else { ?>
                                    <tr><td colspan="11" class="text-center py-4">
                                        <i class="ti-info-alt fa-3x text-muted"></i>
                                        <p class="mt-2">No payroll records for this period.</p>
                                        <p class="text-muted">Click "Process Payroll" to generate payroll for all employees.</p>
                                    </td></tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="header-title">Deductions Summary</h5>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6>SSS Total</h6>
                                    <h4>₱<?php echo number_format($summary->total_sss, 2); ?></h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6>PhilHealth Total</h6>
                                    <h4>₱<?php echo number_format($summary->total_philhealth, 2); ?></h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6>Pag-IBIG Total</h6>
                                    <h4>₱<?php echo number_format($summary->total_pagibig, 2); ?></h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6>BIR Tax Total</h6>
                                    <h4>₱<?php echo number_format($summary->total_tax, 2); ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/admin-footer.php'; ?>