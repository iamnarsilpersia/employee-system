<?php
    session_start();
    error_reporting(0);
    include('../includes/dbconn.php');
    include('../includes/functions.php');
    if(strlen($_SESSION['alogin'])==0){   
    header('location:../index.php');
    } else {
if(isset($_POST['update'])){
    $lid=intval($_GET['lid']);
    $leavetype=$_POST['leavetype'];
    $description=$_POST['description'];
    $sql="UPDATE tblleavetype SET LeaveType=:leavetype,Description=:description WHERE id=:lid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':leavetype',$leavetype,PDO::PARAM_STR);
    $query->bindParam(':description',$description,PDO::PARAM_STR);
    $query->bindParam(':lid',$lid,PDO::PARAM_INT);
    $query->execute();

    $msg="Leave type updated successfully";

    }

?>

<?php $page='edit-leaveType'; include('../includes/admin-header.php'); ?>
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
                                        
                                        <p class="text-muted font-14 mb-4">Please make changes on the form below in order to update leave type</p>

                                        <?php
                                            $lid=intval($_GET['lid']);
                                            $sql = "SELECT * from tblleavetype where id=:lid";
                                            $query = $dbh -> prepare($sql);
                                            $query->bindParam(':lid',$lid,PDO::PARAM_STR);
                                            $query->execute();
                                            $results=$query->fetchAll(PDO::FETCH_OBJ);
                                            $cnt=1;
                                            if($query->rowCount() > 0)
                                            {
                                            foreach($results as $result)
                                            {               ?> 
                                    

                                        <div class="form-group">
                                            <label for="example-text-input" class="col-form-label">Leave Type</label>
                                            <input class="form-control" name="leavetype" type="text" required id="example-text-input" value="<?php echo htmlentities($result->LeaveType);?>" required>
                                        </div>

                                        <div class="form-group">
                                            <label for="example-text-input" class="col-form-label">Short Description</label>
                                            <input class="form-control" name="description" type="text" autocomplete="off" required id="example-text-input" value="<?php echo htmlentities($result->Description);?>" required>
                                                
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
