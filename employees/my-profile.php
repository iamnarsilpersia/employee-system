<?php
    session_start();
    error_reporting(0);
    include('../includes/dbconn.php');
    
    if(strlen($_SESSION['emplogin'])==0){   
        header('location:../index.php');
        exit;
    }
    
    $eid = $_SESSION['eid']; 
    
    if(isset($_POST['update'])){
        $fname = $_POST['firstName'];
        $lname = $_POST['lastName'];   
        $gender = $_POST['gender']; 
        $dob = $_POST['dob']; 
        $department = $_POST['department']; 
        $address = $_POST['address']; 
        $city = $_POST['city']; 
        $country = $_POST['country']; 
        $mobileno = $_POST['mobileno']; 
        
        $sql = "UPDATE tblemployees SET FirstName=:fname, LastName=:lname, Gender=:gender, Dob=:dob, Department=:department, Address=:address, City=:city, Country=:country, Phonenumber=:mobileno WHERE id=:eid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':fname', $fname, PDO::PARAM_STR);
        $query->bindParam(':lname', $lname, PDO::PARAM_STR);
        $query->bindParam(':gender', $gender, PDO::PARAM_STR);
        $query->bindParam(':dob', $dob, PDO::PARAM_STR);
        $query->bindParam(':department', $department, PDO::PARAM_STR);
        $query->bindParam(':address', $address, PDO::PARAM_STR);
        $query->bindParam(':city', $city, PDO::PARAM_STR);
        $query->bindParam(':country', $country, PDO::PARAM_STR);
        $query->bindParam(':mobileno', $mobileno, PDO::PARAM_STR);
        $query->bindParam(':eid', $eid, PDO::PARAM_INT);
        $query->execute();
        $msg = "Your record has been updated Successfully";
    } 
    
    // Change password logic
    if(isset($_POST['change'])){
        $current = $_POST['currentpassword'];
        $new = $_POST['newpassword'];
        $confirm = $_POST['confirmpassword'];
        
        $sql = "SELECT Password FROM tblemployees WHERE id=:eid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':eid', $eid, PDO::PARAM_INT);
        $query->execute();
        $employee = $query->fetch(PDO::FETCH_OBJ);
        
        if($employee && password_verify($current, $employee->Password)){
            if($new !== $confirm){
                $error_pwd = "New passwords do not match!";
            } else {
                $hashed = password_hash($new, PASSWORD_DEFAULT);
                $update = $dbh->prepare("UPDATE tblemployees SET Password=:pwd WHERE id=:eid");
                $update->bindParam(':pwd', $hashed, PDO::PARAM_STR);
                $update->bindParam(':eid', $eid, PDO::PARAM_INT);
                
                if($update->execute()){
                    $msg_pwd = "Password changed successfully!";
                } else {
                    $error_pwd = "Error changing password!";
                }
            }
        } else {
            $error_pwd = "Current password is incorrect!";
        }
    }
?>

<?php $page='my-profile'; include('../includes/employee-header.php'); ?>

<?php if(isset($error) && $error){?><div class="alert alert-danger alert-dismissible fade show"><strong>Error: </strong><?php echo htmlentities($error); ?>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
 </div><?php } 
     else if(isset($msg) && $msg){?><div class="alert alert-success alert-dismissible fade show"><strong>Success: </strong><?php echo htmlentities($msg); ?> 
     <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
     </div><?php }?>
     <div class="card">
     <form name="addemp" method="POST">
         <div class="card-body">
             <h4 class="header-title">Update My Profile</h4>
             <p class="text-muted font-14 mb-4">Please make changes on the form below in order to update your profile</p>

             <?php 
                 $eid = $_SESSION['eid'];
                 $sql = "SELECT * FROM tblemployees WHERE id=:eid";
                 $query = $dbh->prepare($sql);
                 $query->bindParam(':eid', $eid, PDO::PARAM_INT);
                 $query->execute();
                 $results = $query->fetchAll(PDO::FETCH_OBJ);
                 if($query->rowCount() > 0){
                     foreach($results as $result)
                     { 
             ?> 

             <div class="form-group">
                 <label for="example-text-input" class="col-form-label">First Name</label>
                 <input class="form-control" name="firstName" value="<?php echo htmlentities($result->FirstName);?>" type="text" required>
             </div>

             <div class="form-group">
                 <label for="example-text-input" class="col-form-label">Last Name</label>
                 <input class="form-control" name="lastName" value="<?php echo htmlentities($result->LastName);?>" type="text" required>
             </div>

             <div class="form-group">
                 <label for="example-email-input" class="col-form-label">Email</label>
                 <input class="form-control" name="email" type="email" value="<?php echo htmlentities($result->EmailId);?>" readonly>
             </div>

             <div class="form-group">
                 <label class="col-form-label">Gender</label>
                 <select class="custom-select" name="gender">
                     <option value="<?php echo htmlentities($result->Gender);?>"><?php echo htmlentities($result->Gender);?></option>
                     <option value="Male">Male</option>
                     <option value="Female">Female</option>
                     <option value="Other">Other</option>
                 </select>
             </div>

             <div class="form-group">
                 <label for="example-date-input" class="col-form-label">D.O.B</label>
                 <input class="form-control" type="date" name="dob" value="<?php echo htmlentities($result->Dob);?>">
             </div>

             <div class="form-group">
                 <label for="example-text-input" class="col-form-label">Contact Number</label>
                 <input class="form-control" name="mobileno" type="tel" value="<?php echo htmlentities($result->Phonenumber);?>" maxlength="11" required>
             </div>

             <div class="form-group">
                 <label for="example-text-input" class="col-form-label">Employee ID</label>
                 <input class="form-control" name="empcode" type="text" readonly value="<?php echo htmlentities($result->EmpId);?>">
             </div>

             <div class="form-group">
                 <label for="example-text-input" class="col-form-label">Country</label>
                 <input class="form-control" name="country" type="text" value="<?php echo htmlentities($result->Country);?>" required>
             </div>

             <div class="form-group">
                 <label for="example-text-input" class="col-form-label">Address</label>
                 <input class="form-control" name="address" type="text" value="<?php echo htmlentities($result->Address);?>" required>
             </div>

             <div class="form-group">
                 <label for="example-text-input" class="col-form-label">City</label>
                 <input class="form-control" name="city" type="text" value="<?php echo htmlentities($result->City);?>" required>
             </div>

             <div class="form-group">
                 <label class="col-form-label">Department</label>
                 <select class="custom-select" name="department">
                     <option value="<?php echo htmlentities($result->Department);?>"><?php echo htmlentities($result->Department);?></option>
                     <?php 
                     $sql2 = "SELECT DepartmentName FROM tbldepartments";
                     $query2 = $dbh->prepare($sql2);
                     $query2->execute();
                     $results2 = $query2->fetchAll(PDO::FETCH_OBJ);
                     foreach($results2 as $resultt){   
                     ?>  
                         <option value="<?php echo htmlentities($resultt->DepartmentName);?>"><?php echo htmlentities($resultt->DepartmentName);?></option>
                     <?php } ?>
                 </select>
             </div>

              <?php 
                  }
              }?>
              <button class="btn btn-primary" name="update" type="submit">MAKE CHANGES</button>
          </div>
      </form>
  </div>

  <div class="card mt-4">
      <div class="card-body">
          <h4 class="header-title">Change Password</h4>
          
          <?php if(isset($error_pwd) && $error_pwd){?><div class="alert alert-danger alert-dismissible fade show"><strong>Error: </strong><?php echo htmlentities($error_pwd); ?>
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
              </button>
          </div><?php } 
              else if(isset($msg_pwd) && $msg_pwd){?><div class="alert alert-success alert-dismissible fade show"><strong>Success: </strong><?php echo htmlentities($msg_pwd); ?> 
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
              </button>
          </div><?php }?>
          
          <form method="POST">
              <div class="form-group">
                  <label>Current Password</label>
                  <input class="form-control" name="currentpassword" type="password" required>
              </div>
              
              <div class="form-group">
                  <label>New Password</label>
                  <input class="form-control" name="newpassword" type="password" required>
              </div>
              
              <div class="form-group">
                  <label>Confirm New Password</label>
                  <input class="form-control" name="confirmpassword" type="password" required>
              </div>
              
              <button class="btn btn-primary" name="change" type="submit">CHANGE PASSWORD</button>
          </form>
      </div>
  </div>

<?php include '../includes/employee-footer.php'; ?>
