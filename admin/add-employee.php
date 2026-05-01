<?php
session_start();
error_reporting(0);
include('../includes/dbconn.php');
include('../includes/functions.php');

if(strlen($_SESSION['alogin'])==0){   
    header('location:../index.php');
    exit;
}

// Generate next Employee ID
$next_id = 1;
$sql = "SELECT MAX(CAST(SUBSTRING(EmpId, 5) AS UNSIGNED)) as max_id FROM tblemployees WHERE EmpId LIKE 'ASTR%'";
$query = $dbh->prepare($sql);
$query->execute();
$result = $query->fetch(PDO::FETCH_ASSOC);
if($result['max_id']) {
    $next_id = $result['max_id'] + 1;
}
$empid = 'ASTR' . str_pad($next_id, 6, '0', STR_PAD_LEFT);

if(isset($_POST['add'])){
    $empid      = $_POST['empcode'];
    $fname      = $_POST['firstName'];
    $lname      = $_POST['lastName'];   
    $email      = $_POST['email']; 
    $password   = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $gender     = $_POST['gender']; 
    $dob        = $_POST['dob']; 
    $department = $_POST['department']; 
    $jobtitle    = $_POST['jobtitle'];
    $jobposition = $_POST['jobposition'];
    $address    = $_POST['address']; 
    $city       = $_POST['city']; 
    $country    = $_POST['country']; 
    $mobileno   = $_POST['mobileno']; 
    $status     = 1;

    // Validate passwords match
    if($_POST['password'] !== $_POST['confirmpassword']){
        $error = "Passwords do not match!";
    } else {
        $sql="INSERT INTO tblemployees(
            EmpId,FirstName,LastName,EmailId,Password,Gender,Dob,
            Department,JobTitle,JobPosition,Address,City,Country,Phonenumber,Status
        ) VALUES(
            :empid,:fname,:lname,:email,:password,:gender,:dob,
            :department,:jobtitle,:jobposition,:address,:city,:country,:mobileno,:status
        )";

        $query = $dbh->prepare($sql);
        $query->bindParam(':empid',$empid,PDO::PARAM_STR);
        $query->bindParam(':fname',$fname,PDO::PARAM_STR);
        $query->bindParam(':lname',$lname,PDO::PARAM_STR);
        $query->bindParam(':email',$email,PDO::PARAM_STR);
        $query->bindParam(':password',$password,PDO::PARAM_STR);
        $query->bindParam(':gender',$gender,PDO::PARAM_STR);
        $query->bindParam(':dob',$dob,PDO::PARAM_STR);
        $query->bindParam(':department',$department,PDO::PARAM_STR);
        $query->bindParam(':jobtitle',$jobtitle,PDO::PARAM_STR);
        $query->bindParam(':jobposition',$jobposition,PDO::PARAM_STR);
        $query->bindParam(':address',$address,PDO::PARAM_STR);
        $query->bindParam(':city',$city,PDO::PARAM_STR);
        $query->bindParam(':country',$country,PDO::PARAM_STR);
        $query->bindParam(':mobileno',$mobileno,PDO::PARAM_STR);
        $query->bindParam(':status',$status,PDO::PARAM_STR);
        
        if($query->execute()){
            $msg="Employee added successfully! Employee ID: ".$empid;
        } else {
            $error="Error adding employee!";
        }
    }
}
?>

<?php $page='employee'; include('../includes/admin-header.php'); ?>

    <!-- page title area start -->
    <div class="page-title-area">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <div class="breadcrumbs-area clearfix">
                    <h4 class="page-title pull-left">Add Employee Section</h4>
                    <ul class="breadcrumbs pull-left"> 
                        <li><a href="employees.php">Employee</a></li>
                        <li><span>Add</span></li>
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
            <div class="col-lg-6 col-ml-12">
                <div class="row">
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
                        <form name="addemp" method="POST" enctype="multipart/form-data">
                            <div class="card-body">
                                <p class="text-muted font-14 mb-4">Please fill up the form in order to add employee records</p>

                                <div class="form-group">
                                    <label for="example-text-input" class="col-form-label">Employee ID</label>
                                    <input class="form-control" name="empcode" type="text" autocomplete="off" required id="empcode" value="<?php echo $empid; ?>" readonly>
                                </div>

                                <div class="form-group">
                                    <label for="example-text-input" class="col-form-label">First Name</label>
                                    <input class="form-control" name="firstName"  type="text" required id="example-text-input">
                                </div>

                                <div class="form-group">
                                    <label for="example-text-input" class="col-form-label">Last Name</label>
                                    <input class="form-control" name="lastName" type="text" autocomplete="off" required id="example-text-input">
                                </div>

                                <div class="form-group">
                                    <label for="example-email-input" class="col-form-label">Email</label>
                                    <input class="form-control" name="email" type="email"  autocomplete="off" required id="example-email-input">
                                </div>

                                <div class="form-group">
                                    <label class="col-form-label">Preferred Department</label>
                                    <select class="custom-select" name="department" autocomplete="off">
                                        <option value="">Choose..</option>
                                        <?php 
                                        $sql = "SELECT DepartmentName FROM tbldepartments";
                                        $query = $dbh -> prepare($sql);
                                        $query->execute();
                                        $results=$query->fetchAll(PDO::FETCH_OBJ);
                                        if($query->rowCount() > 0){
                                            foreach($results as $result){ ?> 
                                        <option value="<?php echo htmlentities($result->DepartmentName);?>"><?php echo htmlentities($result->DepartmentName);?></option>
                                        <?php }} ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label class="col-form-label">Job Title</label>
                                    <input class="form-control" name="jobtitle" type="text" autocomplete="off" required>
                                </div>

                                <div class="form-group">
                                    <label class="col-form-label">Job Position</label>
                                    <input class="form-control" name="jobposition" type="text" autocomplete="off" required>
                                </div>

                                <div class="form-group">
                                    <label class="col-form-label">Gender</label>
                                    <select class="custom-select" name="gender" autocomplete="off">
                                        <option value="">Choose..</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="example-date-input" class="col-form-label">D.O.B</label>
                                    <input class="form-control" type="date" name="dob" id="birthdate" >
                                </div>

                                <div class="form-group">
                                    <label for="example-text-input" class="col-form-label">Contact Number</label>
                                    <input class="form-control" name="mobileno" type="tel"  maxlength="10" autocomplete="off" required>
                                </div>

                                <div class="form-group">
                                    <label for="example-text-input" class="col-form-label">Country</label>
                                    <select class="form-control" name="country" required>
                                        <option value="">Choose Country</option>
                                        <option value="Philippines">Philippines</option>
                                        <option value="United States">United States</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="example-text-input" class="col-form-label">Address</label>
                                    <input class="form-control" name="address" type="text"   autocomplete="off" required>
                                </div>

                                <div class="form-group">
                                    <label for="example-text-input" class="col-form-label">City</label>
                                    <input class="form-control" name="city" type="text"   autocomplete="off" required>
                                </div>

                                <h4>Set Password for Employee Login</h4>

                                <div class="form-group">
                                    <label for="example-text-input" class="col-form-label">Password</label>
                                    <input class="form-control" name="password" type="password" autocomplete="off" required>
                                </div>

                                <div class="form-group">
                                    <label for="example-text-input" class="col-form-label">Confirmation Password</label>
                                    <input class="form-control" name="confirmpassword" type="password" autocomplete="off" required>
                                </div>

                                <button class="btn btn-primary" name="add" id="update" type="submit">PROCEED</button>
                            </div>
                        </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include '../includes/admin-footer.php'; ?>
