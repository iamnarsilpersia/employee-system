<?php
    session_start();
    error_reporting(0);
    include('../includes/dbconn.php');
    include('../includes/functions.php');
    if(strlen($_SESSION['alogin'])==0){   
    header('location:../index.php');
    } else {

    // code for update the read notification status
    $isread=1;
    $did=intval($_GET['leaveid']);
    date_default_timezone_set('Asia/Manila');
    $admremarkdate=date('Y-m-d H:i:s');
    $sql="UPDATE tblleaves SET IsRead=:isread WHERE id=:did";
    $query = $dbh->prepare($sql);
    $query->bindParam(':isread',$isread,PDO::PARAM_INT);
    $query->bindParam(':did',$did,PDO::PARAM_INT);
    $query->execute();

    // code for action taken on leave
    if(isset($_POST['update'])){ 
    $did=intval($_GET['leaveid']);
    $description=$_POST['description'];
    $status=intval($_POST['status']);   
    date_default_timezone_set('Asia/Manila');
    $admremarkdate=date('Y-m-d H:i:s');

    $sql="UPDATE tblleaves SET AdminRemark=:description,Status=:status,AdminRemarkDate=:admremarkdate WHERE id=:did";
    $query = $dbh->prepare($sql);
    $query->bindParam(':description',$description,PDO::PARAM_STR);
    $query->bindParam(':status',$status,PDO::PARAM_INT);
    $query->bindParam(':admremarkdate',$admremarkdate,PDO::PARAM_STR);
    $query->bindParam(':did',$did,PDO::PARAM_INT);
    $query->execute();
    $msg="Leave updated Successfully";
    
    // Get employee info and notify
    $leaveSql = "SELECT empid, LeaveType, FromDate, ToDate FROM tblleaves WHERE id=:did";
    $leaveQ = $dbh->prepare($leaveSql);
    $leaveQ->bindParam(':did', $did, PDO::PARAM_INT);
    $leaveQ->execute();
    $leave = $leaveQ->fetch(PDO::FETCH_OBJ);
    
    $title = $status == 1 ? "Leave Approved" : "Leave Declined";
    $message = "Your ".$leave->LeaveType." leave (".$leave->FromDate." to ".$leave->ToDate.") has been " . ($status == 1 ? "approved" : "declined");
    
    $notifSql = "INSERT INTO tblnotifications (user_type, user_id, title, message, link) VALUES ('employee', :empid, :title, :message, 'leave-history.php')";
    $notifQ = $dbh->prepare($notifSql);
    $notifQ->bindParam(':empid', $leave->empid, PDO::PARAM_INT);
    $notifQ->bindParam(':title', $title, PDO::PARAM_STR);
    $notifQ->bindParam(':message', $message, PDO::PARAM_STR);
    $notifQ->execute();
    } ?>

<?php $page='employeeLeave-details'; include('../includes/admin-header.php'); ?>
            <div class="main-content-inner">
               
                
                <!-- row area start -->
                <div class="row">
                    
                    <!-- Striped table start -->
                    <div class="col-lg-12 mt-5">
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
                        <div class="card">
                            <div class="card-body">
                                
                                <div class="single-table">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover text-center">
                                            
                                            <tbody>

                                            <?php 
                                            $lid=intval($_GET['leaveid']);
                                            $sql = "SELECT tblleaves.id as lid,tblemployees.FirstName,tblemployees.LastName,tblemployees.EmpId,tblemployees.id,tblemployees.Gender,tblemployees.Phonenumber,tblemployees.EmailId,tblleaves.LeaveType,tblleaves.ToDate,tblleaves.FromDate,tblleaves.Description,tblleaves.PostingDate,tblleaves.Status,tblleaves.AdminRemark,tblleaves.AdminRemarkDate from tblleaves join tblemployees on tblleaves.empid=tblemployees.id where tblleaves.id=:lid";
                                            $query = $dbh -> prepare($sql);
                                            $query->bindParam(':lid',$lid,PDO::PARAM_STR);
                                            $query->execute();
                                            $results=$query->fetchAll(PDO::FETCH_OBJ);
                                            $cnt=1;
                                            if($query->rowCount() > 0)
                                            {
                                            foreach($results as $result)
                                            {         
                                                ?>

                                                <tr>

                                                <td ><b>Employee ID:</b></td>
                                              <td colspan="1"><?php echo htmlentities($result->EmpId);?></td>
                                            <td> <b>Employee Name:</b></td>
                                              <td colspan="1"><a href="update-employee.php?empid=<?php echo htmlentities($result->id);?>" target="_blank">
                                                <?php echo htmlentities($result->FirstName." ".$result->LastName);?></a></td>

                                              <td ><b>Gender :</b></td>
                                              <td colspan="1"><?php echo htmlentities($result->Gender);?></td>
                                          </tr>

                                          <tr>
                                             <td ><b>Employee Email:</b></td>
                                            <td colspan="1"><?php echo htmlentities($result->EmailId);?></td>
                                             <td ><b>Employee Contact:</b></td>
                                            <td colspan="1"><?php echo htmlentities($result->Phonenumber);?></td>

                                            <td ><b>Leave Type:</b></td>
                                            <td colspan="1"><?php echo htmlentities($result->LeaveType);?></td>
                                            
                                        </tr>

                                    <tr>
                                             
                                             <td ><b>Leave From:</b></td>
                                            <td colspan="1"><?php echo htmlentities($result->FromDate);?></td>
                                            <td><b>Leave Upto:</b></td>
                                            <td colspan="1"><?php echo htmlentities($result->ToDate);?></td>
                                            
                                        </tr>

                                    

                                <tr>
                                <td ><b>Leave Applied:</b></td>
                                <td><?php echo htmlentities($result->PostingDate);?></td>

                                <td ><b>Status:</b></td>
                                <td><?php $stats=$result->Status;
                                if($stats==1){
                                ?>
                                    <span style="color: green">Approved</span>
                                    <?php } if($stats==2)  { ?>
                                    <span style="color: red">Declined</span>
                                    <?php } if($stats==0)  { ?>
                                    <span style="color: blue">Pending</span>
                                    <?php } ?>
                                    </td>

                                    
                                </tr>

                                <tr>
                                     <td ><b>Leave Conditions: </b></td>
                                     <td colspan="5"><?php echo htmlentities($result->Description);?></td>
                                          
                                </tr>

                                <tr>
                                    <td ><b>Admin Remark: </b></td>
                                    <td colspan="12"><?php
                                    if($result->AdminRemark==""){
                                    echo "Waiting for Action";  
                                    }
                                    else{
                                    echo htmlentities($result->AdminRemark);
                                    }
                                    ?></td>
                                </tr>

                                <tr>
                                <td ><b>Admin Action On: </b></td>
                                    <td><?php
                                    if($result->AdminRemarkDate==""){
                                    echo "NA";  
                                    }
                                    else{
                                    echo htmlentities($result->AdminRemarkDate);
                                    }
                                    ?></td>
                                </tr>

                                
                                <?php 
                                if($stats==0)
                                {

                                ?>
                            <tr>
                            <td colspan="12">
                            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#exampleModal">SET ACTION</button>
                            </td>
                            </tr>
                            <?php } ?>
                                          <?php $cnt++;} }?>
                                    </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal -->
                    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">SET ACTION</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <form method="POST" name="adminaction">
                        <div class="modal-body">
                        
                            <select class="custom-select" name="status" required="">
                                <option value="">Choose...</option>
                                <option value="1">Approve</option>
                                <option value="2">Decline</option>
                            </select>
                            <br><br>
                            <textarea id="textarea1" name="description" class="form-control" placeholder="Description" rows="5" maxlength="500" required></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-success" name="update">Apply</button>
                        </div>
                        </form>
                        </div>
                    </div>
                    </div>
                    <!-- Striped table end -->
                    
                </div>
                <!-- row area end -->
                
                </div>
                <!-- row area start-->
            </div>
<?php include '../includes/admin-footer.php'; ?>

<?php } ?>
