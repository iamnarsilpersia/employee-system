<?php
session_start();
include('../includes/dbconn.php');
include('../includes/functions.php');

require_employee_login();

$empid = $_SESSION['emplogin'];
$eid = $_SESSION['eid'];

$month = isset($_GET['month']) ? intval($_GET['month']) : date('m');
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

$sql = "SELECT * FROM tblemployees WHERE EmpId=:empid";
$query = $dbh->prepare($sql);
$query->bindParam(':empid', $empid, PDO::PARAM_STR);
$query->execute();
$employee = $query->fetch(PDO::FETCH_OBJ);

$sql = "SELECT * FROM payroll WHERE empid=:empid AND period_month=:month AND period_year=:year";
$query = $dbh->prepare($sql);
$query->bindParam(':empid', $empid, PDO::PARAM_STR);
$query->bindParam(':month', $month, PDO::PARAM_INT);
$query->bindParam(':year', $year, PDO::PARAM_INT);
$query->execute();
$payroll = $query->fetch(PDO::FETCH_OBJ);

$from = date("Y-m-01", strtotime("$year-$month-01"));
$to = date("Y-m-t", strtotime("$year-$month-01"));

$sql = "SELECT * FROM tbltime_logs WHERE EmpID=:empid AND DateWorked BETWEEN :f AND :t ORDER BY DateWorked";
$query = $dbh->prepare($sql);
$query->bindParam(':empid', $empid, PDO::PARAM_STR);
$query->bindParam(':f', $from);
$query->bindParam(':t', $to);
$query->execute();
$records = $query->fetchAll(PDO::FETCH_OBJ);

$total_hours = 0;
foreach($records as $r) {
    $total_hours += $r->HoursWorked;
}

$page='payslip';
include('../includes/employee-header.php');
?>

<div class="main-content-inner">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card">
                <div class="card-body">
                    <div style="margin-bottom:20px;">
                        <h4 style="margin:0 0 15px 0;"><i class="fa fa-file-text"></i> My Payslip</h4>
                        <button onclick="window.print()" class="btn btn-success" id="printPayslipBtn">
                            <i class="fa fa-print"></i> Print Payslip
                        </button>
                    </div>

                    <style>
                    #printPayslipBtn {
                        display: inline-block !important;
                        visibility: visible !important;
                        opacity: 1 !important;
                        padding: 10px 30px !important;
                        font-size: 16px !important;
                        border-radius: 5px !important;
                        margin-bottom: 15px;
                        background-color: #28a745 !important;
                        border-color: #28a745 !important;
                        color: white !important;
                    }
                    </style>

                    <form method="GET" class="form-inline mb-4">
                        <select name="month" class="form-control mr-2">
                            <?php for($m=1; $m<=12; $m++){ ?>
                                <option value="<?php echo $m; ?>" <?php echo $m==$month?'selected':''; ?>>
                                    <?php echo date('F', mktime(0,0,0,$m,1)); ?>
                                </option>
                            <?php } ?>
                        </select>
                        <select name="year" class="form-control mr-2">
                            <?php for($y=date('Y')-2; $y<=date('Y'); $y++){ ?>
                                <option value="<?php echo $y; ?>" <?php echo $y==$year?'selected':''; ?>><?php echo $y; ?></option>
                            <?php } ?>
                        </select>
                        <button type="submit" class="btn btn-primary">View</button>
                    </form>

                    <?php if($payroll){ ?>
                    <div class="payslip-preview">
                        <div class="text-center mb-4">
                            <h4>EMPLOYEE MANAGEMENT SYSTEM</h4>
                            <p class="text-muted">Payslip - <?php echo date('F Y', strtotime("$year-$month-01")); ?></p>
                            <hr>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <strong>Employee:</strong> <?php echo htmlentities($employee->FirstName . ' ' . $employee->LastName); ?><br>
                                <strong>ID:</strong> <?php echo htmlentities($empid); ?><br>
                                <strong>Department:</strong> <?php echo htmlentities($employee->Department ?? 'N/A'); ?>
                            </div>
                            <div class="col-md-6 text-md-right">
                                <strong>Total Hours:</strong> <?php echo round($total_hours, 2); ?> hrs<br>
                                <strong>Days Worked:</strong> <?php echo count($records); ?><br>
                            </div>
                        </div>

                        <table class="table table-bordered">
                            <thead class="bg-light">
                                <tr>
                                    <th colspan="2">EARNINGS</th>
                                    <th colspan="2">DEDUCTIONS</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Basic Salary</strong></td>
                                    <td class="text-right">₱<?php echo number_format($payroll->basic_salary, 2); ?></td>
                                    <td><strong>SSS</strong></td>
                                    <td class="text-right text-danger">-₱<?php echo number_format($payroll->sss_deduction, 2); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Overtime</strong></td>
                                    <td class="text-right">₱<?php echo number_format($payroll->overtime_pay, 2); ?></td>
                                    <td><strong>PhilHealth</strong></td>
                                    <td class="text-right text-danger">-₱<?php echo number_format($payroll->philhealth_deduction, 2); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Allowances</strong></td>
                                    <td class="text-right">₱<?php echo number_format($payroll->allowances, 2); ?></td>
                                    <td><strong>Pag-IBIG</strong></td>
                                    <td class="text-right text-danger">-₱<?php echo number_format($payroll->pagibig_deduction, 2); ?></td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td><strong>BIR Tax</strong></td>
                                    <td class="text-right text-danger">-₱<?php echo number_format($payroll->tax_deduction, 2); ?></td>
                                </tr>
                            </tbody>
                            <tfoot class="bg-light">
                                <tr>
                                    <td><strong>Gross Pay</strong></td>
                                    <td class="text-right"><strong>₱<?php echo number_format($payroll->gross_pay, 2); ?></strong></td>
                                    <td><strong>Total</strong></td>
                                    <td class="text-right text-danger"><strong>-₱<?php echo number_format($payroll->total_deductions, 2); ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center py-3">
                                        <h3 class="mb-0">NET PAY: ₱<?php echo number_format($payroll->net_pay, 2); ?></h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <h6>Hours Summary</h6>
                                <table class="table table-sm">
                                    <tr><td>Regular Hours</td><td class="text-right"><?php echo round($payroll->regular_hours, 2); ?> hrs</td></tr>
                                    <tr><td>Overtime Hours</td><td class="text-right"><?php echo round($payroll->ot_hours, 2); ?> hrs</td></tr>
                                    <tr><td>Total</td><td class="text-right"><strong><?php echo round($payroll->total_hours, 2); ?> hrs</strong></td></tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6>Deductions Breakdown</h6>
                                <table class="table table-sm">
                                    <tr><td>SSS</td><td class="text-right">₱<?php echo number_format($payroll->sss_deduction, 2); ?></td></tr>
                                    <tr><td>PhilHealth</td><td class="text-right">₱<?php echo number_format($payroll->philhealth_deduction, 2); ?></td></tr>
                                    <tr><td>Pag-IBIG</td><td class="text-right">₱<?php echo number_format($payroll->pagibig_deduction, 2); ?></td></tr>
                                    <tr><td>BIR Tax</td><td class="text-right">₱<?php echo number_format($payroll->tax_deduction, 2); ?></td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php } else { ?>
                    <div class="text-center py-5">
                        <i class="ti-info-alt fa-4x text-muted"></i>
                        <h5 class="mt-3">No Payslip Available</h5>
                        <p class="text-muted">Payroll has not been processed for this period yet.</p>
                        <p class="text-muted">Please contact your administrator.</p>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    body { background: white !important; }
    .metismenu, .main-footer, .btn, .alert, .breadcrumbs, .page-title-area, 
    .employee-header, nav, .form-inline, .sidebar, .main-sidebar,
    .d-flex.justify-content-between, #printPayslipBtn { display: none !important; }
    .main-content-inner { padding: 0 !important; margin: 0 !important; }
    .main-panel { padding: 0 !important; }
    .card { border: 2px solid #333 !important; box-shadow: none !important; margin: 0 !important; }
    .payslip-preview { padding: 30px; }
    .table { font-size: 14px; }
    h4 { font-size: 20px !important; }
}
</style>

<?php include('../includes/employee-footer.php'); ?>