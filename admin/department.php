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
    delete_department($dbh, $_GET['del']);
    header('location:department.php');
    exit;
}
?>

<?php $page='department'; include('../includes/admin-header.php'); ?>

    <!-- page title area start -->
    <div class="page-title-area">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <div class="breadcrumbs-area clearfix">
                    <h4 class="page-title pull-left">Department Section</h4>
                    <ul class="breadcrumbs pull-left">
                        <li><a href="dashboard.php">Home</a></li>
                        <li><span>Departments</span></li>
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
        <div class="row">
            <div class="col-12 mt-5">
                <div class="card">
                    <div class="card-body">
                        <center><a href="add-department.php" class="btn btn-sm btn-info">Add New Department</a></center>
                        <div class="data-tables datatable-dark">
                            <table id="dataTable3" class="table table-hover table-striped text-center">
                                <thead class="text-capitalize">
                                    <tr>
                                        <th>#</th>
                                        <th>Department Name</th>
                                        <th>Short Name</th>
                                        <th>Code</th>
                                        <th>Created On</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $sql = "SELECT * FROM tbldepartments";
                                    $query = $dbh -> prepare($sql);
                                    $query->execute();
                                    $results=$query->fetchAll(PDO::FETCH_OBJ);
                                    $cnt=1;
                                    if($query->rowCount() > 0){
                                        foreach($results as $result){               ?>  
                                        <tr>
                                            <td><?php echo htmlentities($cnt);?></td>
                                            <td><?php echo htmlentities($result->DepartmentName);?></td>
                                            <td><?php echo htmlentities($result->DepartmentShortName);?></td>
                                            <td><?php echo htmlentities($result->DepartmentCode);?></td>
                                            <td><?php echo htmlentities($result->CreationDate);?></td>
                                            <td>
                                                <a href="edit-department.php?deptid=<?php echo htmlentities($result->id);?>"><i class="fa fa-edit" style="color:green"></i></a>
                                                <a href="department.php?del=<?php echo htmlentities($result->id);?>" onclick="return confirm('Do you want to delete');"><i class="fa fa-trash" style="color:red"></i></a>
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
