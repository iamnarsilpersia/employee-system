<?php
session_start();
error_reporting(0);
include('../includes/dbconn.php');
include('../includes/functions.php');

if(strlen($_SESSION['emplogin'])==0){   
    header('location:../index.php');
    exit;
}

$empid = $_SESSION['eid'];
?>

<?php $page='leave-history'; include('../includes/employee-header.php'); ?>

    <div class="main-content-inner">
        <div class="row">
            <div class="col-12 mt-5">
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title">My Leave History</h4>
                        
                        <div class="data-tables datatable-dark">
                            <table id="dataTable3" class="table table-hover table-striped text-center">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Leave Type</th>
                                        <th>From</th>
                                        <th>To</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        <th>Applied On</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $sql = "SELECT * FROM tblleaves WHERE empid=:empid ORDER BY PostingDate DESC";
                                    $query = $dbh->prepare($sql);
                                    $query->bindParam(':empid', $empid, PDO::PARAM_STR);
                                    $query->execute();
                                    $results = $query->fetchAll(PDO::FETCH_OBJ);
                                    $cnt = 1;
                                    if($query->rowCount() > 0){
                                        foreach($results as $result){ ?>  
                                            <tr>
                                                <td><?php echo htmlentities($cnt); ?></td>
                                                <td><?php echo htmlentities($result->LeaveType); ?></td>
                                                <td><?php echo htmlentities($result->FromDate); ?></td>
                                                <td><?php echo htmlentities($result->ToDate); ?></td>
                                                <td><?php echo htmlentities(substr($result->Description, 0, 50)); ?>...</td>
                                                <td>
                                                    <?php if($result->Status==1){?>
                                                        <span class="badge badge-pill badge-success">Approved</span>
                                                    <?php } elseif($result->Status==2){?>
                                                        <span class="badge badge-pill badge-danger">Declined</span>
                                                    <?php } else {?>
                                                        <span class="badge badge-pill badge-warning">Pending</span>
                                                    <?php } ?>
                                                </td>
                                                <td><?php echo htmlentities($result->PostingDate); ?></td>
                                            </tr>
                                        <?php $cnt++; }
                                    } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include '../includes/employee-footer.php'; ?>
