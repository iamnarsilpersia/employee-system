<?php
session_start();
include('../includes/dbconn.php');
include('../includes/functions.php');

require_admin_login();

$empid = generate_employee_id($dbh);
$msg = $error = "";

if(isset($_POST['add'])){
    $empid      = sanitize_input($_POST['empcode']);
    $fname      = sanitize_input($_POST['firstName']);
    $lname      = sanitize_input($_POST['lastName']);   
    $email      = sanitize_input($_POST['email']); 
    $password   = $_POST['password'];
    $gender     = sanitize_input($_POST['gender']); 
    $dob        = sanitize_input($_POST['dob']); 
    $department = sanitize_input($_POST['department']); 
    $jobtitle   = sanitize_input($_POST['jobtitle']);
    $jobposition = sanitize_input($_POST['jobposition']);
    $address    = sanitize_input($_POST['address']); 
    $city       = sanitize_input($_POST['city']); 
    $country    = sanitize_input($_POST['country']); 
    $mobileno   = sanitize_input($_POST['mobileno']); 
    $status     = 1;

    if(empty($fname) || empty($lname) || empty($email) || empty($password) || empty($gender)) {
        $error = "Please fill in all required fields.";
    } elseif($password !== $_POST['confirmpassword']) {
        $error = "Passwords do not match!";
    } else {
        $password_errors = validate_password($password);
        if(!empty($password_errors)) {
            $error = implode(". ", $password_errors);
        } else {
            $check_sql = "SELECT id FROM tblemployees WHERE EmpId=:empid OR EmailId=:email";
            $check_query = $dbh->prepare($check_sql);
            $check_query->bindParam(':empid', $empid, PDO::PARAM_STR);
            $check_query->bindParam(':email', $email, PDO::PARAM_STR);
            $check_query->execute();
            
            if($check_query->rowCount() > 0) {
                $error = "Employee ID or Email already exists!";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $sql = "INSERT INTO tblemployees(
                    EmpId,FirstName,LastName,EmailId,Password,Gender,Dob,
                    Department,JobTitle,JobPosition,Address,City,Country,Phonenumber,Status
                ) VALUES(
                    :empid,:fname,:lname,:email,:password,:gender,:dob,
                    :department,:jobtitle,:jobposition,:address,:city,:country,:mobileno,:status
                )";

                $query = $dbh->prepare($sql);
                $query->bindParam(':empid', $empid, PDO::PARAM_STR);
                $query->bindParam(':fname', $fname, PDO::PARAM_STR);
                $query->bindParam(':lname', $lname, PDO::PARAM_STR);
                $query->bindParam(':email', $email, PDO::PARAM_STR);
                $query->bindParam(':password', $hashed_password, PDO::PARAM_STR);
                $query->bindParam(':gender', $gender, PDO::PARAM_STR);
                $query->bindParam(':dob', $dob, PDO::PARAM_STR);
                $query->bindParam(':department', $department, PDO::PARAM_STR);
                $query->bindParam(':jobtitle', $jobtitle, PDO::PARAM_STR);
                $query->bindParam(':jobposition', $jobposition, PDO::PARAM_STR);
                $query->bindParam(':address', $address, PDO::PARAM_STR);
                $query->bindParam(':city', $city, PDO::PARAM_STR);
                $query->bindParam(':country', $country, PDO::PARAM_STR);
                $query->bindParam(':mobileno', $mobileno, PDO::PARAM_STR);
                $query->bindParam(':status', $status, PDO::PARAM_INT);
                
                if($query->execute()){
                    $msg = "Employee added successfully! Employee ID: " . $empid;
                    $empid = generate_employee_id($dbh);
                } else {
                    $error = "Error adding employee!";
                }
            }
        }
    }
}

$sql = "SELECT DepartmentName FROM tbldepartments ORDER BY DepartmentName";
$query = $dbh->prepare($sql);
$query->execute();
$departments = $query->fetchAll(PDO::FETCH_OBJ);
?>

<?php $page='employee'; include('../includes/admin-header.php'); ?>

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
                <?php include '../includes/admin-profile-section.php'; ?>
            </div>
        </div>
    </div>

    <div class="main-content-inner">
        <div class="row">
            <div class="col-lg-6 col-ml-12">
                <div class="row">
                    <div class="col-12 mt-5">
                        <?php if($error){ ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <strong>Error: </strong><?php echo htmlentities($error); ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <?php } else if($msg){ ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <strong>Success: </strong><?php echo htmlentities($msg); ?> 
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <?php } ?>
                        
                        <div class="card">
                        <form name="addemp" method="POST" onsubmit="return validateForm(this);">
                            <div class="card-body">
                                <p class="text-muted font-14 mb-4">Please fill up the form in order to add employee records</p>

                                <div class="form-group">
                                    <label class="col-form-label">Employee ID</label>
                                    <input class="form-control" name="empcode" type="text" autocomplete="off" required id="empcode" value="<?php echo htmlentities($empid); ?>" readonly>
                                </div>

                                <div class="form-group">
                                    <label class="col-form-label">First Name</label>
                                    <input class="form-control" name="firstName" type="text" required>
                                </div>

                                <div class="form-group">
                                    <label class="col-form-label">Last Name</label>
                                    <input class="form-control" name="lastName" type="text" autocomplete="off" required>
                                </div>

                                <div class="form-group">
                                    <label class="col-form-label">Email</label>
                                    <input class="form-control" name="email" type="email" autocomplete="off" required>
                                </div>

                                <div class="form-group">
                                    <label class="col-form-label">Preferred Department</label>
                                    <select class="custom-select" name="department" autocomplete="off">
                                        <option value="">Choose..</option>
                                        <?php foreach($departments as $dept) { ?> 
                                        <option value="<?php echo htmlentities($dept->DepartmentName); ?>"><?php echo htmlentities($dept->DepartmentName); ?></option>
                                        <?php } ?>
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
                                    <select class="custom-select" name="gender" autocomplete="off" required>
                                        <option value="">Choose..</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label class="col-form-label">D.O.B</label>
                                    <input class="form-control" type="date" name="dob" id="birthdate">
                                </div>

                                <div class="form-group">
                                    <label class="col-form-label">Contact Number (Philippine Format)</label>
                                    <input class="form-control" name="mobileno" type="tel" pattern="[0-9]{11}" maxlength="11" autocomplete="off" required>
                                    <small class="text-muted">Format: 09123456789 (11 digits)</small>
                                </div>

                                <div class="form-group">
                                    <label class="col-form-label">Country</label>
                                    <select class="form-control" name="country" required>
                                        <option value="">Choose Country</option>
                                        <option value="Philippines" selected>Philippines</option>
                                        <option value="United States">United States</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label class="col-form-label">Address</label>
                                    <input class="form-control" name="address" type="text" autocomplete="off" required>
                                </div>

                                <div class="form-group">
                                    <label class="col-form-label">City</label>
                                    <input class="form-control" name="city" type="text" autocomplete="off" required>
                                </div>

                                <h4>Set Password for Employee Login</h4>
                                <div class="alert alert-info">
                                    <small>Password must be at least 8 characters with uppercase, lowercase, and number.</small>
                                </div>

                                <div class="form-group">
                                    <label class="col-form-label">Password</label>
                                    <input class="form-control" name="password" type="password" id="password" autocomplete="off" required>
                                </div>

                                <div class="form-group">
                                    <label class="col-form-label">Confirmation Password</label>
                                    <input class="form-control" name="confirmpassword" type="password" autocomplete="off" required>
                                </div>

                                <button class="btn btn-primary" name="add" type="submit">PROCEED</button>
                            </div>
                        </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
function validateForm(form) {
    var password = form.password.value;
    var confirm = form.confirmpassword.value;
    
    if(password !== confirm) {
        alert('Passwords do not match!');
        return false;
    }
    
    if(password.length < 8) {
        alert('Password must be at least 8 characters');
        return false;
    }
    
    if(!/[A-Z]/.test(password)) {
        alert('Password must contain at least one uppercase letter');
        return false;
    }
    
    if(!/[a-z]/.test(password)) {
        alert('Password must contain at least one lowercase letter');
        return false;
    }
    
    if(!/[0-9]/.test(password)) {
        alert('Password must contain at least one number');
        return false;
    }
    
    return true;
}
</script>

<?php include '../includes/admin-footer.php'; ?>