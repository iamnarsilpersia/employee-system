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
    delete_admin($dbh, $_GET['del']);
    header('location:manage-admin.php');
    exit;
}
?>

<?php $page='manage-admin'; include('../includes/admin-header.php'); ?>

    <!-- page title area start -->
    <div class="page-title-area">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <div class="breadcrumbs-area clearfix">
                    <h4 class="page-title pull-left">Manage Admin Section</h4>
                    <ul class="breadcrumbs pull-left"> 
                        <li><a href="dashboard.php">Home</a></li>
                        <li><span>Manage Admin</span></li>
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
                        <center><a href="add-admin.php" class="btn btn-sm btn-info">Add New Administrator</a></center>
                        <div class="data-tables datatable-dark">
                            <table id="dataTable3" class="table table-striped table-hover text-center">
                                <thead class="text-capitalize">
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Username</th>
                                        <th>Email ID</th>
                                        <th>Account Created On</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $sql = "SELECT * FROM admin";
                                    $query = $dbh -> prepare($sql);
                                    $query->execute();
                                    $results=$query->fetchAll(PDO::FETCH_OBJ);
                                    $cnt=1;
                                    if($query->rowCount() > 0){
                                        foreach($results as $result){               ?>  
                                        <tr>
                                            <td><?php echo htmlentities($cnt);?></td>
                                            <td><?php echo htmlentities($result->fullname);?></td>
                                            <td><?php echo htmlentities($result->UserName);?></td>
                                            <td><?php echo htmlentities($result->email);?></td>
                                            <td><?php echo htmlentities($result->updationDate);?></td>
                                            <td>
                                                <a href="manage-admin.php?del=<?php echo htmlentities($result->id);?>" onclick="return confirm('Do you want to delete');"><i class="fa fa-trash" style="color:red"></i></a>
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
