<?php
session_start();
error_reporting(0);
include('../includes/dbconn.php');
include('../includes/functions.php');

if(strlen($_SESSION['alogin'])==0){   
    header('location:../index.php');
    exit;
}

if(isset($_GET['del'])){
    delete_leave_type($dbh, $_GET['del']);
    header('location:leave-section.php');
    exit;
}
?>

<?php $page='leave'; include('../includes/admin-header.php'); ?>

    <!-- page title area start -->
    <div class="page-title-area">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <div class="breadcrumbs-area clearfix">
                    <h4 class="page-title pull-left">Leave Types Section</h4>
                    <ul class="breadcrumbs pull-left"> 
                        <li><a href="dashboard.php">Home</a></li>
                        <li><span>Leave Types</span></li>
                    </ul>
                </div>
            </div>
            
            <div class="col-sm-6 clearfix">
                <div class="user-profile pull-right">
                    <img class="avatar user-thumb" src="../assets/images/admin.png" alt="avatar">
                    <h4 class="user-name dropdown-toggle" data-toggle="dropdown">ADMIN <i class="fa fa-angle-down"></i></h4>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="logout.php">Log Out</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- page title area end -->

    <div class="main-content-inner">
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="ti-settings"></i> Global Leave Limit Setting</h5>
                            <?php
                            $sql = "SELECT leave_limit FROM settings WHERE id=1";
                            $query = $dbh->prepare($sql);
                            $query->execute();
                            $settings = $query->fetch(PDO::FETCH_OBJ);
                            $currentLimit = $settings ? ($settings->leave_limit ?? 0) : 0;
                            ?>
                            <div>
                                <span class="badge badge-<?php echo ($currentLimit > 0) ? 'info' : 'secondary'; ?> p-2">
                                    Current: <strong><?php echo $currentLimit > 0 ? $currentLimit : 'Unlimited'; ?></strong> per month
                                </span>
                            </div>
                        </div>
                        <hr>
                        <form action="update-leave-limit.php" method="POST" class="form-inline">
                            <label class="mr-2">Set max leave applications per month (all types):</label>
                            <input type="number" name="leave_limit" class="form-control mr-2" min="0" value="<?php echo $currentLimit; ?>" required>
                            <small class="text-muted mr-3">(0 = Unlimited)</small>
                            <button type="submit" class="btn btn-primary btn-sm">Update</button>
                        </form>
                        <small class="text-muted d-block mt-2">
                            <i class="ti-info-alt"></i> This limit applies to ALL leave types combined. If set to 10, users can submit a maximum of 10 leave applications per month regardless of type.
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 mt-5">
                <div class="card">
                    <div class="card-body">
                        <center><a href="add-leavetype.php" class="btn btn-sm btn-info">Add New Leave Type</a></center>
                        <div class="data-tables datatable-dark">
                            <table id="dataTable3" class="table table-hover table-striped text-center">
                                <thead class="text-capitalize">
                                    <tr>
                                        <th>#</th>
                                        <th>Leave Type</th>
                                        <th>Description</th>
                                        <th>Created On</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $sql = "SELECT * FROM tblleavetype";
                                    $query = $dbh -> prepare($sql);
                                    $query->execute();
                                    $results=$query->fetchAll(PDO::FETCH_OBJ);
                                    $cnt=1;
                                    if($query->rowCount() > 0){
                                        foreach($results as $result){               
                                    ?>  
                                    <tr>
                                        <td><?php echo htmlentities($cnt);?></td>
                                        <td><?php echo htmlentities($result->LeaveType);?></td>
                                        <td><?php echo htmlentities(substr($result->Description, 0, 50));?>...</td>
                                        <td><?php echo htmlentities($result->CreationDate);?></td>
                                        <td>
                                            <a href="edit-leaveType.php?typeid=<?php echo htmlentities($result->id);?>"><i class="fa fa-edit" style="color:green"></i></a>
                                            <a href="leave-section.php?del=<?php echo htmlentities($result->id);?>" onclick="return confirm('Do you want to delete');"><i class="fa fa-trash" style="color:red"></i></a>
                                        </td>
                                    </tr>
                                    <?php $cnt++;} }?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include '../includes/admin-footer.php'; ?>
