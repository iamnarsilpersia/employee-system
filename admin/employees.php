<?php
    session_start();
    error_reporting(0);
    include('../includes/dbconn.php');
    include('../includes/functions.php');
    
    if(strlen($_SESSION['alogin'])==0){   
        header('location:../index.php');
        exit;
    }

    // Inactive Employee    
    if(isset($_GET['inid'])){
        toggle_employee_status($dbh, $_GET['inid'], 0);
        header('location:employees.php');
        exit;
    }

    // Activated Employee
    if(isset($_GET['id'])){
        toggle_employee_status($dbh, $_GET['id'], 1);
        header('location:employees.php');
        exit;
    }

    // Delete Employee
    if(isset($_GET['did'])){
        delete_employee($dbh, $_GET['did']);
        header('location:employees.php');
        exit;
    }
    
    $page='employee'; 
    include('../includes/admin-header.php'); 
?>

    <!-- page title area start -->
    <div class="page-title-area">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <div class="breadcrumbs-area clearfix">
                    <h4 class="page-title pull-left">Employee Section</h4>
                    <ul class="breadcrumbs pull-left">
                        <li><a href="dashboard.php">Home</a></li>
                        <li><span>Employee Management</span></li>
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
        <?php if($error){?><div class="alert alert-danger alert-dismissible fade show"><strong>Info: </strong><?php echo htmlentities($error); ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div><?php } 
        else if($msg){?><div class="alert alert-success alert-dismissible fade show"><strong>Info: </strong><?php echo htmlentities($msg); ?> 
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div><?php }?>

        <div class="row">
            <div class="col-12 mt-5">
                <div class="card">
                    <div class="card-body">
                        <center><a href="add-employee.php" class="btn btn-sm btn-info">Add New Employee</a></center>
                        <div class="data-tables datatable-dark">
                            <table id="dataTable3" class="table table-hover table-striped text-center">
                                <thead class="text-capitalize">
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Employee ID</th>
                                        <th>Department</th>
                                        <th>Joined On</th>
                                        <th>Status</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $sql = "SELECT EmpId,FirstName,LastName,Department,Status,RegDate,id FROM tblemployees";
                                    $query = $dbh -> prepare($sql);
                                    $query->execute();
                                    $results=$query->fetchAll(PDO::FETCH_OBJ);
                                    $cnt=1;
                                    if($query->rowCount() > 0){
                                        foreach($results as $result){               ?>  
                                            <tr>
                                                <td><?php echo htmlentities($cnt);?></td>
                                                <td><?php echo htmlentities($result->FirstName);?>&nbsp;<?php echo htmlentities($result->LastName);?></td>
                                                <td><?php echo htmlentities($result->EmpId);?></td>
                                                <td><?php echo htmlentities($result->Department);?></td>
                                                <td><?php echo htmlentities($result->RegDate);?></td>
                                                <td><?php $stats=$result->Status;
                                                    if($stats){?>
                                                        <span class="badge badge-pill badge-success">Active</span>
                                                    <?php } else { ?>
                                                        <span class="badge badge-pill badge-danger">Inactive</span>
                                                    <?php } ?>
                                                </td>
                                                <td>
                                                    <a href="update-employee.php?empid=<?php echo htmlentities($result->id);?>"><i class="fa fa-edit" style="color:green"></i></a>
                                                    <?php if($result->Status==1){?>
                                                        <a href="employees.php?inid=<?php echo htmlentities($result->id);?>" onclick="return confirm('Are you sure you want to inactive this employee?');"><i class="fa fa-times-circle" style="color:red" title="Inactive"></i></a>
                                                    <?php } else {?>
                                                        <a href="employees.php?id=<?php echo htmlentities($result->id);?>" onclick="return confirm('Are you sure you want to active this employee?');"><i class="fa fa-check" style="color:green" title="Active"></i></a>
                                                    <?php } ?>
                                                    <a href="employees.php?did=<?php echo htmlentities($result->id);?>" onclick="return confirm('WARNING: This will permanently delete this employee. Are you sure?');"><i class="fa fa-trash" style="color:#d9534f; margin-left:8px;" title="Delete Employee"></i></a>
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
