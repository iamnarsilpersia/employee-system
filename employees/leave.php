<?php
session_start();
include('../includes/dbconn.php');
include('../includes/functions.php');

require_employee_login();

$empid = $_SESSION['eid'];
$empcode = $_SESSION['emplogin'];
$msg = $error = "";

initialize_database_tables($dbh);

if(isset($_POST['apply'])) {
    $leavetype = sanitize_input($_POST['leavetype']);
    $fromdate = sanitize_input($_POST['fromdate']);  
    $todate = sanitize_input($_POST['todate']);
    $description = sanitize_input($_POST['description']);  
    
    $typeSql = "SELECT * FROM tblleavetype WHERE LeaveType=:type";
    $typeQ = $dbh->prepare($typeSql);
    $typeQ->bindParam(':type', $leavetype, PDO::PARAM_STR);
    $typeQ->execute();
    $selectedType = $typeQ->fetch(PDO::FETCH_OBJ);
    
    $maxPerMonth = $selectedType ? ($selectedType->max_per_month ?? 999) : 999;
    $globalLimit = get_global_leave_limit($dbh);
    $totalUsage = get_total_leave_usage_this_month($dbh, $empid);
    
    if(empty($leavetype) || empty($fromdate) || empty($todate) || empty($description)) {
        $error = "Please fill in all required fields.";
    } elseif($fromdate > $todate) {
        $error = "End Date should be ahead of Start Date!";
    } elseif($globalLimit > 0 && $totalUsage >= $globalLimit) {
        $error = "You have reached the maximum limit of $globalLimit leave application(s) this month (all types combined).";
    } elseif($maxPerMonth > 0) {
        $currentUsage = get_leave_usage_this_month($dbh, $empid, $leavetype);
        if($currentUsage >= $maxPerMonth) {
            $error = "You have reached the maximum limit of $maxPerMonth $leavetype submission(s) this month.";
        } else {
            $sql = "INSERT INTO tblleaves(LeaveType,ToDate,FromDate,Description,Status,IsRead,empid) VALUES(:type, :todate, :fromdate, :desc, 0, 0, :empid)";
            $query = $dbh->prepare($sql);
            $query->bindParam(':type', $leavetype, PDO::PARAM_STR);
            $query->bindParam(':todate', $todate, PDO::PARAM_STR);
            $query->bindParam(':fromdate', $fromdate, PDO::PARAM_STR);
            $query->bindParam(':desc', $description, PDO::PARAM_STR);
            $query->bindParam(':empid', $empid, PDO::PARAM_INT);
            
            if($query->execute()) {
                $msg = "Leave application submitted successfully.";
            } else {
                $error = "Could not process application. Please try again.";
            }
        }
    } else {
        $sql = "INSERT INTO tblleaves(LeaveType,ToDate,FromDate,Description,Status,IsRead,empid) VALUES(:type, :todate, :fromdate, :desc, 0, 0, :empid)";
        $query = $dbh->prepare($sql);
        $query->bindParam(':type', $leavetype, PDO::PARAM_STR);
        $query->bindParam(':todate', $todate, PDO::PARAM_STR);
        $query->bindParam(':fromdate', $fromdate, PDO::PARAM_STR);
        $query->bindParam(':desc', $description, PDO::PARAM_STR);
        $query->bindParam(':empid', $empid, PDO::PARAM_INT);
        
        if($query->execute()) {
            $msg = "Leave application submitted successfully.";
        } else {
            $error = "Could not process application. Please try again.";
        }
    }
}

$empSql = "SELECT FirstName, LastName, EmpId, Department FROM tblemployees WHERE id=:eid";
$empQ = $dbh->prepare($empSql);
$empQ->bindParam(':eid', $empid, PDO::PARAM_INT);
$empQ->execute();
$employee = $empQ->fetch(PDO::FETCH_OBJ);

$sql = "SELECT * FROM tblleavetype ORDER BY LeaveType";
$query = $dbh->prepare($sql);
$query->execute();
$leaveTypes = $query->fetchAll(PDO::FETCH_OBJ);

$leaveHistorySql = "SELECT * FROM tblleaves WHERE empid=:empid ORDER BY PostingDate DESC";
$leaveHistoryQ = $dbh->prepare($leaveHistorySql);
$leaveHistoryQ->bindParam(':empid', $empid, PDO::PARAM_INT);
$leaveHistoryQ->execute();
$leaveHistory = $leaveHistoryQ->fetchAll(PDO::FETCH_OBJ);

$pendingCount = 0;
$approvedCount = 0;
$declinedCount = 0;
foreach($leaveHistory as $lh) {
    if($lh->Status == 0) $pendingCount++;
    elseif($lh->Status == 1) $approvedCount++;
    elseif($lh->Status == 2) $declinedCount++;
}

$globalLimit = get_global_leave_limit($dbh);
$totalUsage = get_total_leave_usage_this_month($dbh, $empid);
$globalRemaining = ($globalLimit > 0) ? $globalLimit - $totalUsage : 999;
?>

<?php $page='leave'; include('../includes/employee-header.php'); ?>

<div class="main-content-inner">
    <div class="row">
        <div class="col-lg-10 col-md-12 mx-auto mt-4">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <h4 class="header-title mb-1">
                                <i class="ti-pencil-alt text-primary"></i> Leave Application Form
                            </h4>
                            <p class="text-muted">Please complete all required fields to submit your leave request</p>
                        </div>
                        <div class="col-md-4 text-right">
                            <span class="badge badge-info p-2">
                                <i class="ti-calendar"></i> <?php echo date('F d, Y'); ?>
                            </span>
                        </div>
                    </div>

                    <?php if($error){ ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="ti-close"></i> <strong>Error:</strong> <?php echo htmlentities($error); ?>
                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    <?php } ?>
                    
                    <?php if($msg){ ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="ti-check"></i> <strong>Success:</strong> <?php echo htmlentities($msg); ?>
                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    <?php } ?>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="text-muted mb-3">Employee Information</h6>
                                    <table class="table table-sm table-borderless">
                                        <tr><td class="text-muted">Name:</td><td><strong><?php echo htmlentities($employee->FirstName . ' ' . $employee->LastName); ?></strong></td></tr>
                                        <tr><td class="text-muted">ID:</td><td><strong><?php echo htmlentities($employee->EmpId); ?></strong></td></tr>
                                        <tr><td class="text-muted">Department:</td><td><?php echo !empty($employee->Department) ? htmlentities($employee->Department) : 'N/A'; ?></td></tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <form method="POST" id="leaveForm">
                                <div class="form-group mb-4">
                                    <label class="font-weight-bold">Leave Type <span class="text-danger">*</span></label>
                                    <select class="form-control form-control-lg" name="leavetype" id="leaveTypeSelect" required <?php echo ($globalLimit > 0 && $globalRemaining <= 0) ? 'disabled' : ''; ?>>
                                        <option value="">-- Select Leave Type --</option>
                                        <?php foreach($leaveTypes as $type) { ?> 
                                            <option value="<?php echo htmlentities($type->LeaveType); ?>">
                                                <?php echo htmlentities($type->LeaveType); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                                
                                <?php if($globalLimit > 0 && $globalRemaining <= 0) { ?>
                                <div class="alert alert-danger mt-3">
                                    <i class="ti-close"></i> <strong>Limit Reached!</strong> You have no remaining leave applications for this month. Please contact the admin.
                                </div>
                                <?php } ?>

                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="font-weight-bold">From Date <span class="text-danger">*</span></label>
                                            <input class="form-control form-control-lg" name="fromdate" type="date" required <?php echo ($globalLimit > 0 && $globalRemaining <= 0) ? 'disabled' : ''; ?>>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="font-weight-bold">To Date <span class="text-danger">*</span></label>
                                            <input class="form-control form-control-lg" name="todate" type="date" required <?php echo ($globalLimit > 0 && $globalRemaining <= 0) ? 'disabled' : ''; ?>>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mb-4">
                                    <label class="font-weight-bold">Reason / Description <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="description" rows="4" placeholder="Please provide detailed reason for your leave application..." required <?php echo ($globalLimit > 0 && $globalRemaining <= 0) ? 'disabled' : ''; ?>></textarea>
                                </div>

                                <div class="form-group">
                                    <button class="btn btn-primary btn-lg btn-block" name="apply" type="submit" <?php echo ($globalLimit > 0 && $globalRemaining <= 0) ? 'disabled' : ''; ?>>
                                        <i class="ti-check-box"></i> SUBMIT LEAVE APPLICATION
                                    </button>
                                </div>

                                <div class="text-center mt-3">
                                    <small class="text-muted">
                                        <i class="ti-info-alt"></i> Your application will be reviewed by the admin.
                                    </small>
                                </div>
                            </form>
                        </div>
                    </div>
                <div class="row mt-4">
                        <div class="col-md-4">
                            <div class="card text-center text-white" style="background: #f59e0b;">
                                <div class="card-body py-3">
                                    <h3 class="mb-1"><?php echo $pendingCount; ?></h3>
                                    <small>Pending</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-center text-white" style="background: #10b981;">
                                <div class="card-body py-3">
                                    <h3 class="mb-1"><?php echo $approvedCount; ?></h3>
                                    <small>Approved</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-center text-white bg-danger">
                                <div class="card-body py-3">
                                    <h3 class="mb-1"><?php echo $declinedCount; ?></h3>
                                    <small>Declined</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h5 class="mb-3"><i class="ti-list"></i> Leave Application Status</h5>
                        
                        <?php if($globalLimit > 0) { ?>
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="card <?php echo ($globalRemaining <= 0) ? 'border-danger' : ''; ?>">
                                    <div class="card-body text-center py-4">
                                        <h2 class="mb-2">
                                            <?php echo $globalRemaining; ?>
                                            <small class="text-muted">/ <?php echo $globalLimit; ?></small>
                                        </h2>
                                        <p class="mb-0 text-muted">
                                            <?php if($globalRemaining <= 0) { ?>
                                                <span class="text-danger"><strong>No remaining leave applications for this month</strong></span>
                                            <?php } else { ?>
                                                <strong>Remaining Leave Application(s) This Month</strong>
                                            <?php } ?>
                                        </p>
                                        <div class="progress mt-3 mx-auto" style="height: 12px; max-width: 400px;">
                                            <div class="progress-bar bg-<?php echo ($totalUsage >= $globalLimit) ? 'danger' : (($totalUsage >= ($globalLimit * 0.75)) ? 'warning' : 'success'); ?>" 
                                                 style="width: <?php echo min(($totalUsage / $globalLimit) * 100, 100); ?>%"></div>
                                        </div>
                                        <small class="text-muted mt-2 d-block"><?php echo $totalUsage; ?> used | <?php echo $globalLimit; ?> total allowance</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php } else { ?>
                        <div class="alert alert-info mb-4">
                            <i class="ti-info-alt"></i> <strong>Unlimited Leave</strong> - No monthly limit set for leave applications.
                        </div>
                        <?php } ?>
                    </div>

                    <div class="mt-4">
                        <h5 class="mb-3"><i class="ti-list"></i> Recent Leave Applications</h5>
                        <?php if(count($leaveHistory) > 0) { ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Leave Type</th>
                                        <th>From</th>
                                        <th>To</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        <th>Applied On</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($leaveHistory as $lh) { ?>
                                    <tr>
                                        <td><strong><?php echo htmlentities($lh->LeaveType); ?></strong></td>
                                        <td><?php echo date('M d, Y', strtotime($lh->FromDate)); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($lh->ToDate)); ?></td>
                                        <td><?php echo htmlentities(substr($lh->Description, 0, 40)); ?>...</td>
                                        <td>
                                            <?php if($lh->Status == 1) { ?>
                                                <span class="badge badge-pill badge-success">Approved</span>
                                            <?php } elseif($lh->Status == 2) { ?>
                                                <span class="badge badge-pill badge-danger">Declined</span>
                                            <?php } else { ?>
                                                <span class="badge badge-pill badge-warning">Pending</span>
                                            <?php } ?>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($lh->PostingDate)); ?></td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        <?php } else { ?>
                        <div class="alert alert-info">
                            <i class="ti-info-alt"></i> No leave applications yet. Submit your first leave request above.
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function(){
    var today = new Date().toISOString().split('T')[0];
    $('input[name="fromdate"]').attr('min', today);
    $('input[name="todate"]').attr('min', today);
    
    $('input[name="fromdate"]').change(function() {
        $('input[name="todate"]').attr('min', $(this).val());
    });
    
    $('#leaveForm').submit(function(e) {
        var fromDate = new Date($('input[name="fromdate"]').val());
        var toDate = new Date($('input[name="todate"]').val());
        if(toDate < fromDate) {
            alert('End Date should be ahead of Start Date');
            e.preventDefault();
            return false;
        }
    });
});
</script>

<?php include '../includes/employee-footer.php'; ?>