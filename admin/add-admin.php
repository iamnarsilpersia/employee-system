<?php
session_start();
error_reporting(0);
include('../includes/dbconn.php');
include('../includes/functions.php');

if(strlen($_SESSION['alogin'])==0){
    header('location:../index.php');
    exit;
}

if(isset($_POST['add'])){
    $fullname=$_POST['fullname'];
    $email=$_POST['email'];
    $username=$_POST['username'];

    // Validate passwords match
    if($_POST['password'] !== $_POST['confirmpassword']){
        $error = "Passwords do not match!";
    } else {
        // Check for duplicate username
        $sql = "SELECT id FROM admin WHERE UserName=:username";
        $query = $dbh->prepare($sql);
        $query->bindParam(':username',$username,PDO::PARAM_STR);
        $query->execute();

        if($query->rowCount() > 0){
            $error = "Username already exists!";
        } else {
            // Check for duplicate email
            $sql = "SELECT id FROM admin WHERE email=:email";
            $query = $dbh->prepare($sql);
            $query->bindParam(':email',$email,PDO::PARAM_STR);
            $query->execute();

            if($query->rowCount() > 0){
                $error = "Email already exists!";
            } else {
                $password=password_hash($_POST['password'], PASSWORD_DEFAULT);

                $sql="INSERT INTO admin(fullname,email,Password,UserName) VALUES(:fullname,:email,:password,:username)";
                $query = $dbh->prepare($sql);
                $query->bindParam(':fullname',$fullname,PDO::PARAM_STR);
                $query->bindParam(':email',$email,PDO::PARAM_STR);
                $query->bindParam(':password',$password,PDO::PARAM_STR);
                $query->bindParam(':username',$username,PDO::PARAM_STR);

                if($query->execute()){
                    $msg="New admin has been added Successfully";
                } else {
                    $error="ERROR adding admin";
                }
            }
        }
    }
}

$page='add-admin';
include('../includes/admin-header.php');
?>

<div class="main-content-inner">
    <div class="row">
        <div class="col-lg-6 col-ml-12">
            <div class="row">
                <div class="col-12 mt-5">
                    <?php if($error){?><div class="alert alert-danger alert-dismissible fade show"><strong>Error: </strong><?php echo htmlentities($error); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div><?php }
                    else if($msg){?><div class="alert alert-success alert-dismissible fade show"><strong>Success: </strong><?php echo htmlentities($msg); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div><?php }?>
                    <div class="card">
                        <form name="addemp" method="POST">
                            <div class="card-body">
                                <p class="text-muted font-14 mb-4">Please fill up the form to add a new system administrator</p>

                                <div class="form-group">
                                    <label for="example-text-input" class="col-form-label">Full Name</label>
                                    <input class="form-control" name="fullname" type="text" required id="example-text-input">
                                </div>

                                <div class="form-group">
                                    <label for="example-email-input" class="col-form-label">Email ID</label>
                                    <input class="form-control" name="email" type="email" autocomplete="off" required id="example-email-input">
                                </div>

                                <div class="form-group">
                                    <label for="example-text-input" class="col-form-label">Username</label>
                                    <input class="form-control" name="username" type="text" autocomplete="off" required id="example-text-input">
                                </div>

                                <h4>Setting Passwords</h4>

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
