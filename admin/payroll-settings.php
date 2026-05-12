<?php
session_start();
include('../includes/dbconn.php');
include('../includes/functions.php');

require_admin_login();
initialize_payroll_tables($dbh);

$msg = $error = "";

if(isset($_POST['save_custom'])) {
    $name = sanitize_input($_POST['name']);
    $type = sanitize_input($_POST['type']);
    $amount = floatval($_POST['amount']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    if(save_custom_deduction($dbh, ['name' => $name, 'type' => $type, 'amount' => $amount, 'is_active' => $is_active])) {
        $msg = "Custom deduction saved successfully!";
    } else {
        $error = "Failed to save custom deduction.";
    }
}

if(isset($_GET['del'])) {
    $id = intval($_GET['del']);
    if(delete_custom_deduction($dbh, $id)) {
        $msg = "Deduction deleted successfully!";
    }
}

$custom_deductions = get_custom_deductions($dbh);

$sql = "SELECT * FROM settings WHERE id=1";
$query = $dbh->prepare($sql);
$query->execute();
$settings = $query->fetch(PDO::FETCH_OBJ);

if(isset($_POST['save_settings'])) {
    $daily_rate = floatval($_POST['daily_rate']);
    $ot_rate = floatval($_POST['ot_rate']);
    $payroll_type = sanitize_input($_POST['payroll_type']);
    $leave_limit = intval($_POST['leave_limit']);
    
    $dbh->exec("DELETE FROM settings WHERE id=1");
    $stmt = $dbh->prepare("INSERT INTO settings (id, daily_rate, ot_rate, payroll_type, leave_limit) VALUES (1, :dr, :ot, :pt, :ll)");
    $stmt->bindParam(':dr', $daily_rate);
    $stmt->bindParam(':ot', $ot_rate);
    $stmt->bindParam(':pt', $payroll_type);
    $stmt->bindParam(':ll', $leave_limit);
    $stmt->execute();
    
    $msg = "Payroll settings updated successfully!";
    header("Refresh:0");
}

$page='payroll';
include('../includes/admin-header.php');
?>

<div class="page-title-area">
    <div class="row align-items-center">
        <div class="col-sm-6">
            <div class="breadcrumbs-area clearfix">
                <h4 class="page-title pull-left">Payroll Settings</h4>
                <ul class="breadcrumbs pull-left">
                    <li><a href="dashboard.php">Home</a></li>
                    <li><span>Payroll Settings</span></li>
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
    
    <?php if($error){ ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <strong>Error: </strong><?php echo htmlentities($error); ?>
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
    <?php } ?>

    <div class="row">
        <div class="col-lg-6 col-md-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="header-title"><i class="ti-settings"></i> Payroll Rates</h5>
                    <form method="POST" class="mt-4">
                        <div class="form-group">
                            <label>Daily Salary Rate (PHP)</label>
                            <input type="number" step="0.01" name="daily_rate" value="<?php echo $settings->daily_rate ?? 500; ?>" class="form-control" required>
                            <small class="text-muted">Base daily rate for 8 hours work</small>
                        </div>
                        <div class="form-group">
                            <label>Overtime Rate per Hour (PHP)</label>
                            <input type="number" step="0.01" name="ot_rate" value="<?php echo $settings->ot_rate ?? 50; ?>" class="form-control" required>
                            <small class="text-muted">Rate per hour for overtime work</small>
                        </div>
                        <div class="form-group">
                            <label>Payroll Type</label>
                            <select name="payroll_type" class="form-control">
                                <option value="monthly" <?php echo ($settings->payroll_type ?? '') == 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                                <option value="semi-monthly" <?php echo ($settings->payroll_type ?? '') == 'semi-monthly' ? 'selected' : ''; ?>>Semi-Monthly</option>
                            </select>
                        </div>
                        <div class="form-group mt-4 pt-3 border-top">
                            <label>Max Leave Applications per Month (0 = Unlimited)</label>
                            <input type="number" step="1" min="0" name="leave_limit" value="<?php echo $settings->leave_limit ?? 0; ?>" class="form-control">
                            <small class="text-muted">Set the maximum number of leave applications (all types combined) a user can submit per month</small>
                        </div>
                        <button type="submit" name="save_settings" class="btn btn-primary">Save Settings</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6 col-md-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="header-title"><i class="ti-cut"></i> Custom Deductions</h5>
                    <form method="POST" class="mt-4">
                        <div class="form-group">
                            <label>Deduction Name</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g., SSS Loan" required>
                        </div>
                        <div class="form-group">
                            <label>Type</label>
                            <select name="type" class="form-control">
                                <option value="fixed">Fixed Amount</option>
                                <option value="percentage">Percentage</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Amount/Percentage</label>
                            <input type="number" step="0.01" name="amount" class="form-control" placeholder="0.00" required>
                        </div>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="is_active" value="1" checked> Active
                            </label>
                        </div>
                        <button type="submit" name="save_custom" class="btn btn-primary">Add Deduction</button>
                    </form>

                    <h6 class="mt-4">Active Deductions</h6>
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($custom_deductions as $cd){ ?>
                            <tr>
                                <td><?php echo htmlentities($cd->name); ?></td>
                                <td><span class="badge badge-info"><?php echo ucfirst($cd->type); ?></span></td>
                                <td><?php echo $cd->type == 'fixed' ? '₱' : ''; ?><?php echo number_format($cd->amount, 2); ?><?php echo $cd->type == 'percentage' ? '%' : ''; ?></td>
                                <td><a href="?del=<?php echo $cd->id; ?>" class="text-danger" onclick="return confirm('Delete this deduction?')"><i class="fa fa-trash"></i></a></td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="header-title"><i class="ti-info-alt"></i> Government Contribution Info</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="bg-primary text-white">
                                <tr>
                                    <th>Deduction</th>
                                    <th>Description</th>
                                    <th>Current Calculation</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>SSS</strong></td>
                                    <td>Social Security System Employee Contribution</td>
                                    <td>Bracket-based (₱135 - ₱1,485/month based on salary)</td>
                                </tr>
                                <tr>
                                    <td><strong>PhilHealth</strong></td>
                                    <td>Philippine Health Insurance Corporation</td>
                                    <td>4.5% of monthly salary (max ₱4,500, employee pays half)</td>
                                </tr>
                                <tr>
                                    <td><strong>Pag-IBIG</strong></td>
                                    <td>Home Development Mutual Fund</td>
                                    <td>1% if salary ≤ ₱1,500, 2% if salary > ₱1,500 (max ₱100)</td>
                                </tr>
                                <tr>
                                    <td><strong>Withholding Tax</strong></td>
                                    <td>BIR Tax under TRAIN Law</td>
                                    <td>Based on annual taxable income brackets</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/admin-footer.php'; ?>