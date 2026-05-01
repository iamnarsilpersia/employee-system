<?php
session_start();
include('../includes/dbconn.php');

if(empty($_SESSION['emplogin'])) {   
    header('Location: ../index.php');
    exit();
}

$empid = $_SESSION['eid'];
$msg = $error = "";

if(isset($_POST['apply'])) {
    $leavetype = $_POST['leavetype'];
    $fromdate = $_POST['fromdate'];  
    $todate = $_POST['todate'];
    $description = $_POST['description'];  
    if($fromdate > $todate){
        $error = "End Date should be ahead of Start Date!";
    } else {
        $sql = "INSERT INTO tblleaves(LeaveType,ToDate,FromDate,Description,Status,IsRead,empid) VALUES(?,?,?,?,0,0,?)";
        $query = $dbh->prepare($sql);
        $query->execute([$leavetype, $todate, $fromdate, $description, $empid]);
        if($query->rowCount() > 0) {
            $msg = "Leave application submitted successfully.";
        } else {
            $error = "Could not process application. Please try again.";
        }
    }
}

$empSql = "SELECT FirstName, LastName, EmpId, Department FROM tblemployees WHERE id=?";
$empQ = $dbh->prepare($empSql);
$empQ->execute([$empid]);
$employee = $empQ->fetch(PDO::FETCH_OBJ);

$sql = "SELECT * FROM tblleavetype";
$query = $dbh->prepare($sql);
$query->execute();
$leaveTypes = $query->fetchAll(PDO::FETCH_OBJ);
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
                                    <select class="form-control form-control-lg" name="leavetype" required>
                                        <option value="">-- Select Leave Type --</option>
                                        <?php foreach($leaveTypes as $type) { ?> 
                                            <option value="<?php echo htmlentities($type->LeaveType); ?>">
                                                <?php echo htmlentities($type->LeaveType); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="font-weight-bold">From Date <span class="text-danger">*</span></label>
                                            <input class="form-control form-control-lg" name="fromdate" type="date" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="font-weight-bold">To Date <span class="text-danger">*</span></label>
                                            <input class="form-control form-control-lg" name="todate" type="date" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mb-4">
                                    <label class="font-weight-bold">Reason / Description <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="description" rows="4" placeholder="Please provide detailed reason for your leave application..." required></textarea>
                                </div>

                                <div class="form-group">
                                    <button class="btn btn-primary btn-lg btn-block" name="apply" type="submit">
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
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function(){
    $('#leaveForm').submit(function(e) {
        var fromDate = new Date($('input[name="fromdate"]').val());
        var toDate = new Date($('input[name="todate"]').val());
        if(toDate < fromDate) {
            alert('End Date should be ahead of Start Date');
            e.preventDefault();
            return false;
        }
    });
    
    var today = new Date().toISOString().split('T')[0];
    $('input[name="fromdate"]').attr('min', today);
    $('input[name="todate"]').attr('min', today);
    
    $('input[name="fromdate"]').change(function() {
        $('input[name="todate"]').attr('min', $(this).val());
    });
});
</script>

<?php include '../includes/employee-footer.php'; ?>
