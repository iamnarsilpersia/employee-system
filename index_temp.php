<?php
session_start();
include('includes/dbconn.php');

if(isset($_POST['login'])){
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    // Try admin login first
    $sql = "SELECT * FROM admin WHERE UserName=:username";
    $query = $dbh->prepare($sql);
    $query->bindParam(':username', $username, PDO::PARAM_STR);
    $query->execute();
    $admin = $query->fetch(PDO::FETCH_OBJ);
    
    if($admin && password_verify($password, $admin->Password)){
        $_SESSION['alogin'] = $admin->UserName;
        $_SESSION['aid'] = $admin->id;
        header('location:admin/dashboard.php');
        exit();
    }
    
    // Try employee login
    $sql = "SELECT * FROM tblemployees WHERE EmpId=:username AND Status=1";
    $query = $dbh->prepare($sql);
    $query->bindParam(':username', $username, PDO::PARAM_STR);
    $query->execute();
    $employee = $query->fetch(PDO::FETCH_OBJ);
    
    if($employee && password_verify($password, $employee->Password)){
        $_SESSION['emplogin'] = $employee->EmpId;
        $_SESSION['eid'] = $employee->id;
        header('location:employees/payroll.php');
        exit();
    }
    
    $error = "Invalid credentials or account inactive";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Management System - Login</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/sb-admin-2.min.css">
    <style>
        body { background: #f8f9fc; }
        .login-card { max-width: 400px; margin: 100px auto; }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-card">
            <div class="card shadow">
                <div class="card-header bg-primary text-white text-center py-4">
                    <h4>Employee Management System</h4>
                </div>
                <div class="card-body p-4">
                    <?php if(isset($error) && $error){ ?>
                        <div class="alert alert-danger"><?php echo htmlentities($error); ?></div>
                    <?php } ?>
                    
                    <form method="POST">
                        <div class="form-group">
                            <label>Username / Employee ID</label>
                            <input class="form-control form-control-lg" type="text" name="username" required autofocus>
                        </div>
                        
                        <div class="form-group">
                            <label>Password</label>
                            <input class="form-control form-control-lg" type="password" name="password" required>
                        </div>
                        
                        <button class="btn btn-primary btn-lg btn-block" name="login" type="submit">LOGIN</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
