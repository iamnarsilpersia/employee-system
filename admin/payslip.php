<?php
session_start();
include('../includes/dbconn.php');
include('../includes/functions.php');

require_admin_login();

$id = intval($_GET['id'] ?? 0);
$empid_param = $_GET['empid'] ?? '';
$month_param = intval($_GET['month'] ?? 0);
$year_param = intval($_GET['year'] ?? 0);

$payroll = null;

if($id > 0) {
    $payroll = get_payroll_by_id($dbh, $id);
} elseif($empid_param && $month_param && $year_param) {
    $sql = "SELECT p.*, CONCAT(e.FirstName, ' ', e.LastName) as emp_name, e.Department, e.EmailId
            FROM payroll p
            LEFT JOIN tblemployees e ON p.empid = e.EmpId
            WHERE p.empid = :empid AND p.period_month = :month AND p.period_year = :year
            ORDER BY p.id DESC LIMIT 1";
    $query = $dbh->prepare($sql);
    $query->bindParam(':empid', $empid_param);
    $query->bindParam(':month', $month_param, PDO::PARAM_INT);
    $query->bindParam(':year', $year_param, PDO::PARAM_INT);
    $query->execute();
    $payroll = $query->fetch(PDO::FETCH_OBJ);
}

if(!$payroll) {
    header('location:process-payroll.php');
    exit;
}

$page='payroll';
include('../includes/admin-header.php');
?>

<div class="page-title-area">
    <div class="row align-items-center">
        <div class="col-sm-6">
            <div class="breadcrumbs-area clearfix">
                <h4 class="page-title pull-left">Payslip</h4>
                <ul class="breadcrumbs pull-left">
                    <li><a href="dashboard.php">Home</a></li>
                    <li><a href="process-payroll.php">Payroll</a></li>
                    <li><span>Payslip</span></li>
                </ul>
            </div>
        </div>
        <div class="col-sm-6 clearfix">
            <?php include '../includes/admin-profile-section.php'; ?>
        </div>
    </div>
</div>

<div class="main-content-inner">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="header-title"><i class="ti-printer"></i> Employee Payslip</h4>
                        <div>
                            <button onclick="window.print()" class="btn btn-primary"><i class="ti-print"></i> Print</button>
                            <a href="process-payroll.php" class="btn btn-secondary"><i class="ti-back-left"></i> Back</a>
                        </div>
                    </div>

                    <div class="payslip-container" id="printArea">
                        <div class="text-center mb-4">
                            <h2 class="mb-0">EMPLOYEE MANAGEMENT SYSTEM</h2>
                            <p class="text-muted">Official Payslip</p>
                            <hr>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <strong>Employee Name:</strong> <?php echo htmlentities($payroll->emp_name); ?><br>
                                <strong>Employee ID:</strong> <?php echo htmlentities($payroll->empid); ?><br>
                                <strong>Department:</strong> <?php echo htmlentities($payroll->Department ?? 'N/A'); ?>
                            </div>
                            <div class="col-md-6 text-md-right">
                                <strong>Period:</strong> <?php echo date('F Y', strtotime($payroll->period_year . '-' . $payroll->period_month . '-01')); ?><br>
                                <strong>Generated:</strong> <?php echo date('M d, Y h:i A', strtotime($payroll->created_at)); ?>
                            </div>
                        </div>

                        <table class="table table-bordered">
                            <thead class="bg-light">
                                <tr>
                                    <th colspan="2" class="text-center">EARNINGS</th>
                                    <th colspan="2" class="text-center">DEDUCTIONS</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Basic Salary</strong></td>
                                    <td class="text-right">₱<?php echo number_format($payroll->basic_salary, 2); ?></td>
                                    <td><strong>SSS Contribution</strong></td>
                                    <td class="text-right text-danger">-₱<?php echo number_format($payroll->sss_deduction, 2); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Overtime Pay</strong></td>
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
                                <?php if($payroll->late_deduction > 0 || $payroll->undertime_deduction > 0 || $payroll->absence_deduction > 0 || $payroll->other_deduction > 0){ ?>
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td><strong>Late/Undertime/Absence</strong></td>
                                    <td class="text-right text-danger">-₱<?php echo number_format($payroll->late_deduction + $payroll->undertime_deduction + $payroll->absence_deduction, 2); ?></td>
                                </tr>
                                <?php } ?>
                                <?php if($payroll->other_deduction > 0){ ?>
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td><strong>Other Deductions</strong></td>
                                    <td class="text-right text-danger">-₱<?php echo number_format($payroll->other_deduction, 2); ?></td>
                                </tr>
                                <?php } ?>
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td><strong>Withholding Tax (BIR)</strong></td>
                                    <td class="text-right text-danger">-₱<?php echo number_format($payroll->tax_deduction, 2); ?></td>
                                </tr>
                            </tbody>
                            <tfoot class="bg-light">
                                <tr>
                                    <td><strong>Gross Pay</strong></td>
                                    <td class="text-right"><strong>₱<?php echo number_format($payroll->gross_pay, 2); ?></strong></td>
                                    <td><strong>Total Deductions</strong></td>
                                    <td class="text-right text-danger"><strong>-₱<?php echo number_format($payroll->total_deductions, 2); ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center py-4">
                                        <h4 class="mb-0">NET PAY: ₱<?php echo number_format($payroll->net_pay, 2); ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <h6>Attendance Summary</h6>
                                <table class="table table-sm">
                                    <tr><td>Total Hours Worked</td><td class="text-right"><?php echo round($payroll->total_hours, 2); ?> hrs</td></tr>
                                    <tr><td>Regular Hours</td><td class="text-right"><?php echo round($payroll->regular_hours, 2); ?> hrs</td></tr>
                                    <tr><td>Overtime Hours</td><td class="text-right"><?php echo round($payroll->ot_hours, 2); ?> hrs</td></tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6>Deductions Breakdown</h6>
                                <table class="table table-sm">
                                    <tr><td>SSS</td><td class="text-right">₱<?php echo number_format($payroll->sss_deduction, 2); ?></td></tr>
                                    <tr><td>PhilHealth</td><td class="text-right">₱<?php echo number_format($payroll->philhealth_deduction, 2); ?></td></tr>
                                    <tr><td>Pag-IBIG</td><td class="text-right">₱<?php echo number_format($payroll->pagibig_deduction, 2); ?></td></tr>
                                    <tr><td>Withholding Tax</td><td class="text-right">₱<?php echo number_format($payroll->tax_deduction, 2); ?></td></tr>
                                </table>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12 text-center text-muted">
                                <small>This is a computer-generated document. No signature required.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .page-title-area, .main-footer, .btn, .alert, .breadcrumbs { display: none !important; }
    .main-content-inner { padding: 0 !important; }
    .card { border: none !important; box-shadow: none !important; }
    .payslip-container { padding: 20px; }
}
</style>

<?php include '../includes/admin-footer.php'; ?>