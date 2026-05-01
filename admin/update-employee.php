<?php
    session_start();
    error_reporting(0);
    include('../includes/dbconn.php');
    include('../includes/functions.php');
    if(strlen($_SESSION['alogin'])==0){   
    header('location:../index.php');
    } else {
    $eid=intval($_GET['empid']);
    
    if(isset($_POST['update'])){
        $fname=$_POST['firstName'];
        $lname=$_POST['lastName'];   
        $gender=$_POST['gender']; 
        $dob=$_POST['dob']; 
        $department=$_POST['department']; 
        $address=$_POST['address']; 
        $city=$_POST['city']; 
        $country=$_POST['country']; 
        $mobileno=$_POST['mobileno'];

        $sql="UPDATE tblemployees SET FirstName=:fname,LastName=:lname,Gender=:gender,Dob=:dob,Department=:department,Address=:address,City=:city,Country=:country,Phonenumber=:mobileno WHERE id=:eid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':fname',$fname,PDO::PARAM_STR);
        $query->bindParam(':lname',$lname,PDO::PARAM_STR);
        $query->bindParam(':gender',$gender,PDO::PARAM_STR);
        $query->bindParam(':dob',$dob,PDO::PARAM_STR);
        $query->bindParam(':department',$department,PDO::PARAM_STR);
        $query->bindParam(':address',$address,PDO::PARAM_STR);
        $query->bindParam(':city',$city,PDO::PARAM_STR);
        $query->bindParam(':country',$country,PDO::PARAM_STR);
        $query->bindParam(':mobileno',$mobileno,PDO::PARAM_STR);
        $query->bindParam(':eid',$eid,PDO::PARAM_STR);
        $query->execute();

        $msg="Employee record updated Successfully";
    }

    // Handle password change
    if(isset($_POST['changepassword'])){
        $newpassword = $_POST['newpassword'];
        $confirmpassword = $_POST['confirmpassword'];
        if($newpassword !== $confirmpassword){
            $error = "Passwords do not match";
        } else {
            update_employee_password($dbh, $eid, $newpassword);
            $msg="Employee password updated successfully";
        }
    }
 ?>
 
<?php $page='employee'; include('../includes/admin-header.php'); ?>

    <!-- page title area start -->
    <div class="page-title-area">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <div class="breadcrumbs-area clearfix">
                    <h4 class="page-title pull-left">Update Employee Section</h4>
                    <ul class="breadcrumbs pull-left"> 
                        <li><a href="employees.php">Employee</a></li>
                        <li><span>Update</span></li>
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
                
                
                <!-- row area start -->
                <div class="row">
                <div class="col-lg-6 col-ml-12">
                        <div class="row">
                            <!-- Input form start -->
                            <div class="col-12 mt-5">
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
                                <form name="addemp" method="POST">

                                    <div class="card-body">
                                        
                                        <p class="text-muted font-14 mb-4">Please make changes on the form below in order to update your profile</p>

                                        <?php 
                                            $eid=intval($_GET['empid']);
                                            $sql = "SELECT * from  tblemployees where id=:eid";
                                            $query = $dbh -> prepare($sql);
                                            $query -> bindParam(':eid',$eid, PDO::PARAM_STR);
                                            $query->execute();
                                            $results=$query->fetchAll(PDO::FETCH_OBJ);
                                            $cnt=1;
                                            if($query->rowCount() > 0)
                                            {
                                            foreach($results as $result)
                                            {               ?> 
                                    

                                        <div class="form-group">
                                            <label for="example-text-input" class="col-form-label">First Name</label>
                                            <input class="form-control" name="firstName" value="<?php echo htmlentities($result->FirstName);?>"  type="text" required id="example-text-input">
                                        </div>

                                        <div class="form-group">
                                            <label for="example-text-input" class="col-form-label">Last Name</label>
                                            <input class="form-control" name="lastName" value="<?php echo htmlentities($result->LastName);?>" type="text" autocomplete="off" required id="example-text-input">
                                        </div>

                                        <div class="form-group">
                                            <label for="example-email-input" class="col-form-label">Email</label>
                                            <input class="form-control" name="email" type="email"  value="<?php echo htmlentities($result->EmailId);?>" readonly autocomplete="off" required id="example-email-input">
                                        </div>

                                        <div class="form-group">
                                            <label class="col-form-label">Gender</label>
                                            <select class="custom-select" name="gender" autocomplete="off">
                                                <option value="<?php echo htmlentities($result->Gender);?>"><?php echo htmlentities($result->Gender);?></option>
                                                <option value="Male">Male</option>
                                                <option value="Female">Female</option>
                                                <option value="Other">Other</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="example-date-input" class="col-form-label">D.O.B</label>
                                            <input class="form-control" type="date" name="dob" id="birthdate" value="<?php echo htmlentities($result->Dob);?>">
                                        </div>

                                        <div class="form-group">
                                            <label for="example-text-input" class="col-form-label">Contact Number</label>
                                            <input class="form-control" name="mobileno" type="tel" value="<?php echo htmlentities($result->Phonenumber);?>" maxlength="10" autocomplete="off" required>
                                        </div>

                                        <div class="form-group">
                                            <label for="example-text-input" class="col-form-label">Employee ID</label>
                                            <input class="form-control" name="empcode" type="text" autocomplete="off" readonly required value="<?php echo htmlentities($result->EmpId);?>" id="example-text-input">
                                        </div>

                                        <div class="form-group">
                                            <label for="example-text-input" class="col-form-label">Country</label>
                                            <input class="form-control" name="country" type="text"  value="<?php echo htmlentities($result->Country);?>" autocomplete="off" required id="example-text-input">
                                        </div>

                                        <div class="form-group">
                                            <label for="example-text-input" class="col-form-label">Address</label>
                                            <input class="form-control" name="address" type="text"  value="<?php echo htmlentities($result->Address);?>" autocomplete="off" required>
                                        </div>

                                        <div class="form-group">
                                            <label for="example-text-input" class="col-form-label">City</label>
                                            <input class="form-control" name="city" type="text"  value="<?php echo htmlentities($result->City);?>" autocomplete="off" required>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-form-label">Your Leave Type</label>
                                            <select class="custom-select" name="department" autocomplete="off">
                                            <option value="<?php echo htmlentities($result->Department);?>"><?php echo htmlentities($result->Department);?></option>

                                            <?php $sql = "SELECT DepartmentName from tbldepartments";
                                                $query = $dbh -> prepare($sql);
                                                $query->execute();
                                                $results=$query->fetchAll(PDO::FETCH_OBJ);
                                                $cnt=1;
                                                if($query->rowCount() > 0){
                                                foreach($results as $resultt)
                                                {   
                                            ?>  
                                                <option value="<?php echo htmlentities($resultt->DepartmentName);?>"><?php echo htmlentities($resultt->DepartmentName);?></option>
                                        <?php }} ?>
                                        </select>
                                        </div>

                                        <?php }
                                        }?>

                                        <button class="btn btn-primary" name="update" id="update" type="submit">MAKE CHANGES</button>

                                     </div>
                                 </form>

                                 <!-- Password Change Section -->
                                 <div class="card-body mt-4" style="border-top: 1px solid #eee;">
                                     <h4 class="mb-3">Change Employee Password</h4>
                                     <form method="POST">
                                         <div class="form-group">
                                             <label for="newpassword" class="col-form-label">New Password</label>
                                             <input class="form-control" name="newpassword" type="password" autocomplete="off" required id="newpassword">
                                         </div>

                                         <div class="form-group">
                                             <label for="confirmpassword" class="col-form-label">Confirm Password</label>
                                             <input class="form-control" name="confirmpassword" type="password" autocomplete="off" required id="confirmpassword">
                                         </div>

                                         <button class="btn btn-warning" name="changepassword" type="submit">CHANGE PASSWORD</button>
                                     </form>
                                 </div>
                                 </div>
                            </div>
                            
                        </div>
                    </div>
                    <!-- Input Form Ending point -->
                    
                </div>
                <!-- row area end -->
                
                </div>
                <!-- row area start-->
            </div>
            <?php include '../includes/admin-footer.php'; ?>

<?php } ?>
