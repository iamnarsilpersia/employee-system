<?php
session_start();
include('../includes/dbconn.php');
include('../includes/functions.php');

require_admin_login();

$msg = $error = "";

if(isset($_GET['del']) && isset($_SESSION['alogin'])) {
    $id = intval($_GET['del']);
    if(delete_payroll_record($dbh, $id)) {
        $msg = "Payroll record deleted successfully!";
    }
}

$month = isset($_GET['month']) ? intval($_GET['month']) : date('m');
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$empid_filter = isset($_GET['empid']) ? sanitize_input($_GET['empid']) : '';

$records = get_payroll_history($dbh, $empid_filter ?: null, $month, $year);

$page='payroll';
include('../includes/admin-header.php');
?>

<div class="page-title-area">
    <div class="row align-items-center">
        <div class="col-sm-6">
            <div class="breadcrumbs-area clearfix">
                <h4 class="page-title pull-left">Payroll History</h4>
                <ul class="breadcrumbs pull-left">
                    <li><a href="dashboard.php">Home</a></li>
                    <li><span>Payroll History</span></li>
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
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="header-title">Filter Payroll Records</h5>
                    <form method="GET" class="form-inline mb-4">
                        <select name="month" class="form-control mr-2">
                            <option value="">All Months</option>
                            <?php for($m=1; $m<=12; $m++){ ?>
                            <option value="<?php echo $m; ?>" <?php echo $m==$month?'selected':''; ?>><?php echo date('F', mktime(0,0,0,$m,1)); ?></option>
                            <?php } ?>
                        </select>
                        <select name="year" class="form-control mr-2">
                            <option value="">All Years</option>
                            <?php for($y=date('Y')-5; $y<=date('Y'); $y++){ ?>
                            <option value="<?php echo $y; ?>" <?php echo $y==$year?'selected':''; ?>><?php echo $y; ?></option>
                            <?php } ?>
                        </select>
                        <input type="text" name="empid" class="form-control mr-2" placeholder="Employee ID" value="<?php echo htmlentities($empid_filter); ?>">
                        <button type="submit" class="btn btn-primary mr-2"><i class="ti-search"></i> Search</button>
                        <a href="payroll-history.php" class="btn btn-secondary"><i class="ti-reload"></i> Reset</a>
                    </form>

                    <div class="table-responsive">
                        <table id="historyTable" class="table table-striped table-bordered">
                            <thead class="bg-primary text-white">
                                <tr>
                                    <th>#</th>
                                    <th>Employee</th>
                                    <th>Period</th>
                                    <th>Gross Pay</th>
                                    <th>Total Ded.</th>
                                    <th>Net Pay</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(count($records) > 0){ $cnt = 1; foreach($records as $row){ ?>
                                <tr>
                                    <td><?php echo $cnt++; ?></td>
                                    <td>
                                        <strong><?php echo htmlentities($row->emp_name ?? 'N/A'); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlentities($row->empid); ?></small>
                                    </td>
                                    <td><?php echo date('M Y', strtotime($row->period_year . '-' . $row->period_month . '-01')); ?></td>
                                    <td>₱<?php echo number_format($row->gross_pay, 2); ?></td>
                                    <td>₱<?php echo number_format($row->total_deductions, 2); ?></td>
                                    <td><strong class="text-success">₱<?php echo number_format($row->net_pay, 2); ?></strong></td>
                                    <td>
                                        <a href="payslip.php?id=<?php echo $row->id; ?>" class="btn btn-sm btn-info" title="View Payslip"><i class="ti-eye"></i></a>
                                        <a href="?del=<?php echo $row->id; ?>" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Delete this payroll record?')"><i class="ti-trash"></i></a>
                                    </td>
                                </tr>
                                <?php } } else { ?>
                                <tr><td colspan="7" class="text-center py-4">
                                    <i class="ti-folder fa-3x text-muted"></i>
                                    <p class="mt-2">No payroll records found.</p>
                                </td></tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/admin-footer.php'; ?>