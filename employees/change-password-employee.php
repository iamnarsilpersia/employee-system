<?php
session_start();
error_reporting(0);
include('../includes/dbconn.php');

if(strlen($_SESSION['emplogin'])==0){   
    header('location:../index.php');
    exit;
}

$eid = $_SESSION['eid'];

if(isset($_POST['change'])){
    $current = $_POST['currentpassword'];
    $new = $_POST['newpassword'];
    $confirm = $_POST['confirmpassword'];
    
    // Get current password
    $sql = "SELECT Password FROM tblemployees WHERE id=:eid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':eid', $eid, PDO::PARAM_INT);
    $query->execute();
    $employee = $query->fetch(PDO::FETCH_OBJ);
    
    if($employee && password_verify($current, $employee->Password)){
        if($new !== $confirm){
            $error = "New passwords do not match!";
        } else {
            $hashed = password_hash($new, PASSWORD_DEFAULT);
            $update = $dbh->prepare("UPDATE tblemployees SET Password=:pwd WHERE id=:eid");
            $update->bindParam(':pwd', $hashed, PDO::PARAM_STR);
            $update->bindParam(':eid', $eid, PDO::PARAM_INT);
            
            if($update->execute()){
                $msg = "Password changed successfully!";
            } else {
                $error = "Error changing password!";
            }
        }
    } else {
        $error = "Current password is incorrect!";
    }
}

$page='profile'; include('../includes/employee-header.php'); 
?>

<div class="main-content-inner">
    <div class="row">
        <div class="col-lg-6 col-ml-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title">Change Password</h4>
                    
                    <?php if(isset($error) && $error){?><div class="alert alert-danger"><?php echo htmlentities($error); ?></div><?php } ?>
                    <?php if(isset($msg) && $msg){?><div class="alert alert-success"><?php echo htmlentities($msg); ?></div><?php } ?>
                    
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
        </div>
    </div>
</div>

<?php include '../includes/employee-footer.php'; ?>
