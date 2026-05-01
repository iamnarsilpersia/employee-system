<?php
    session_start();
    error_reporting(0);
    include('../includes/dbconn.php');
    include('../includes/functions.php');
    if(strlen($_SESSION['alogin'])==0){   
        header('location:../index.php');
    } else {

 ?>
<?php $page='manage-leave'; include('../includes/admin-header.php'); ?>
            <div class="main-content-inner">
                
                
                <!-- row area start -->
                <div class="row">
                    
                    <!-- trading history area start -->
                    
                                
                                <div class="col-12 mt-5">
                                    <div class="card">

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

                                        <div class="card-body">
                                        <!-- <h4 class="header-title"></h4> -->
                                        <div class="single-table">
                                            <div class="table-responsive">
                                                <table class="table table-hover table-striped table-bordered progress-table text-center">
                                                <thead class="text-uppercase">

                                                <tr>
                                                        <td>S.N</td>
                                                        <td>Employee ID</td>
                                                        <td width="120">Full Name</td>
                                                        <td>Leave Type</td>
                                                        <td>Applied On</td>
                                                        <td>Current Status</td>
                                                        <td></td>
                                                    </tr>
                                                </thead>

                                                <tbody>
                                                <?php 
                                                    
                                                    $sql = "SELECT tblleaves.id as lid,tblemployees.FirstName,tblemployees.LastName,tblemployees.EmpId,tblemployees.id,tblleaves.LeaveType,tblleaves.PostingDate,tblleaves.Status from tblleaves join tblemployees on tblleaves.empid=tblemployees.id order by lid desc";
                                                    $query = $dbh -> prepare($sql);
                                                    $query->execute();
                                                    $results=$query->fetchAll(PDO::FETCH_OBJ);
                                                    $cnt=1;
                                                    if($query->rowCount() > 0){
                                                    foreach($results as $result)
                                                    {         
                                                ?>  

                                        <tr>
                                            <td> <b><?php echo htmlentities($cnt);?></b></td>
                                            <td><?php echo htmlentities($result->EmpId);?></td>
                                            <td><a href="update-employee.php?empid=<?php echo htmlentities($result->id);?>" target="_blank"><?php echo htmlentities($result->FirstName." ".$result->LastName);?></a></td>
                                            <td><?php echo htmlentities($result->LeaveType);?></td>
                                            <td><?php echo htmlentities($result->PostingDate);?></td>
                                            <td><?php $stats=$result->Status;

                                            if($stats==1){
                                             ?>
                                                 <span style="color: green">Approved <i class="fa fa-check-square-o"></i></span>
                                                 <?php } if($stats==2)  { ?>
                                                <span style="color: red">Declined <i class="fa fa-times"></i></span>
                                                 <?php } if($stats==0)  { ?>
                                            <span style="color: blue">Pending <i class="fa fa-spinner"></i></span>
                                            <?php } ?>


                                             </td>

                                            
                                            <td><a href="employeeLeave-details.php?leaveid=<?php echo htmlentities($result->lid);?>" class="btn btn-secondary btn-sm">View Details</a></td>
                                            </tr>
                                                <?php $cnt++;} }?>
                                            </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        </div>
                                    </div>
                                </div>
                          
                    <!-- trading history area end -->
                    
                </div>
                <!-- row area end -->
                
                </div>
                <!-- row area start-->
            </div>
<?php include '../includes/admin-footer.php'; ?>

<?php } ?>
