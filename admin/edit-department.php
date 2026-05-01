<?php
    session_start();
    error_reporting(0);
    include('../includes/dbconn.php');
    include('../includes/functions.php');
    if(strlen($_SESSION['alogin'])==0){   
    header('location:../index.php');
    } else {
    if(isset($_POST['update'])){

        $did=intval($_GET['deptid']);    
        $deptname=$_POST['departmentname'];
        $deptshortname=$_POST['departmentshortname'];
        $deptcode=$_POST['deptcode'];   
        $sql="UPDATE tbldepartments set DepartmentName=:deptname,DepartmentCode=:deptcode,DepartmentShortName=:deptshortname where id=:did";
        $query = $dbh->prepare($sql);
        $query->bindParam(':deptname',$deptname,PDO::PARAM_STR);
        $query->bindParam(':deptcode',$deptcode,PDO::PARAM_STR);
        $query->bindParam(':deptshortname',$deptshortname,PDO::PARAM_STR);
        $query->bindParam(':did',$did,PDO::PARAM_STR);
        $query->execute();
        $msg="Department updated Successfully";
    }

?>

<?php $page='edit-department'; include('../includes/admin-header.php'); ?>
            <div class="main-content-inner">
                
                
                <!-- row area start -->
                <div class="row">
                    <!-- Dark table start -->
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
                                
                                <form method="POST">
                                 <div class="card-body">
                                        
                                        <p class="text-muted font-14 mb-4">Please make changes on the form below in order to update department</p>

                                        <?php 
                                            $did=intval($_GET['deptid']);
                                            $sql = "SELECT * from tbldepartments WHERE id=:did";
                                            $query = $dbh -> prepare($sql);
                                            $query->bindParam(':did',$did,PDO::PARAM_STR);
                                            $query->execute();
                                            $results=$query->fetchAll(PDO::FETCH_OBJ);
                                            $cnt=1;
                                            if($query->rowCount() > 0)
                                            {
                                            foreach($results as $result)
                                            {               ?> 
                                    

                                        <div class="form-group">
                                            <label for="example-text-input" class="col-form-label">Department Name</label>
                                            <input class="form-control" name="departmentname" type="text" required id="example-text-input" value="<?php echo htmlentities($result->DepartmentName);?>">
                                        </div>

                                        <div class="form-group">
                                            <label for="example-text-input" class="col-form-label">Shortform</label>
                                            <input class="form-control" name="departmentshortname" type="text" autocomplete="off" required id="example-text-input" value="<?php echo htmlentities($result->DepartmentShortName);?>">
                                        </div>

                                        <div class="form-group">
                                            <label for="example-email-input" class="col-form-label">Code</label>
                                            <input class="form-control" name="deptcode" type="text" autocomplete="off" required id="example-email-input" value="<?php echo htmlentities($result->DepartmentCode);?>">
                                        </div>

                                        <?php }
                                        }?>

                                        <button class="btn btn-primary" name="update" id="update" type="submit">MAKE CHANGES</button>
                                        
                                    </div>
                         </form>
                         </div>
                    </div>
                    <!-- Dark table end -->
                    
                </div>
                <!-- row area end -->
                
                </div>
                <!-- row area start-->
            </div>
<?php include '../includes/admin-footer.php'; ?>

<?php } ?>
