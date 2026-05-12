<?php
session_start();
include('includes/dbconn.php');

if(isset($_POST['login'])){
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    $sql = "SELECT * FROM admin WHERE UserName=:username";
    $query = $dbh->prepare($sql);
    $query->bindParam(':username',$username,PDO::PARAM_STR);
    $query->execute();
    $admin = $query->fetch(PDO::FETCH_OBJ);
    
    if($admin && password_verify($password, $admin->Password)){
        $_SESSION['alogin'] = $admin->UserName;
        $_SESSION['aid'] = $admin->id;
        header('location:admin/dashboard.php');
        exit();
    }
    
    $sql = "SELECT * FROM tblemployees WHERE EmpId=:username AND Status=1";
    $query = $dbh->prepare($sql);
    $query->bindParam(':username',$username,PDO::PARAM_STR);
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
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Login - Employee Management System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <link rel="shortcut icon" type="image/png" href="assets/images/icon/favicon.ico">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        
        html, body {
            height: 100%;
            width: 100%;
            overflow: hidden;
        }
        
        body {
            font-family:'Segoe UI',system-ui,-apple-system,sans-serif;
            background:#f8f9fa;
        }
        
        .login-wrapper {
            display: flex;
            height: 100vh;
            width: 100vw;
        }
        
        .login-form-side {
            width: 45%;
            min-height: 100vh;
            background:#ffffff;
            display:flex;
            align-items:center;
            justify-content:center;
            padding: 3vh 2vw;
        }
        
        .login-form-wrapper {
            width: 100%;
            max-width: 400px;
            border:1px solid #e0e0e0;
            border-radius:16px;
            padding: 2rem;
            background:#fff;
            box-shadow:0 10px 40px rgba(0,0,0,0.1);
        }
        
        .form-header {
            margin-bottom:2rem;
            text-align:center;
        }
        
        .form-header .logo-icon {
            width:65px;
            height:65px;
            background:linear-gradient(135deg, #7c3aed 0%, #a855f7 100%);
            border-radius:18px;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            margin-bottom:1.5rem;
            font-size:26px;
            color:white;
            box-shadow:0 10px 30px rgba(124,58,237,0.3);
        }
        
        .form-header h2 {
            font-size:1.75rem;
            font-weight:700;
            color:#1a1a2e;
            margin-bottom:0.5rem;
        }
        
        .form-header p {
            color:#6c757d;
            font-size:0.95rem;
        }
        
        .form-floating { margin-bottom:1.25rem; position:relative; }
        
        .form-floating > .form-control {
            padding:1rem 1rem 1rem 2.75rem;
            height:calc(3rem + 2px);
            border-radius:10px;
            border:2px solid #e9ecef;
            transition:all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-size:0.95rem;
            background:#f8f9fa;
        }
        
        .form-floating > .form-control:focus {
            border-color:#7c3aed;
            box-shadow:0 0 0 4px rgba(124, 58, 237, 0.1);
            background:#fff;
        }
        
        .form-floating > label {
            padding:1rem 1rem 1rem 2.75rem;
            font-size:0.95rem;
            color:#6c757d;
        }
        
        .input-group-icon {
            position:absolute;
            left:12px;
            top:50%;
            transform:translateY(-50%);
            color:#6c757d;
            z-index:10;
            font-size:15px;
            transition:color 0.3s ease;
        }
        
        .form-floating > .form-control:focus ~ .input-group-icon {
            color:#7c3aed;
        }
        
        .input-group-icon.right {
            left:auto;
            right:12px;
            cursor:pointer;
        }
        
        .btn-login {
            background:linear-gradient(135deg, #7c3aed 0%, #a855f7 100%);
            color:white;
            border:none;
            padding:0.875rem 1.5rem;
            border-radius:10px;
            font-size:1rem;
            font-weight:600;
            width:100%;
            transition:all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            margin-top:0.5rem;
            letter-spacing:0.5px;
            box-shadow:0 4px 15px rgba(124, 58, 237, 0.3);
        }
        
        .btn-login:hover {
            transform:translateY(-2px);
            box-shadow:0 8px 25px rgba(124, 58, 237, 0.4);
            color:white;
        }
        
        .alert {
            border-radius:10px;
            border:none;
            padding:1rem 1.25rem;
            margin-bottom:1.5rem;
            font-size:0.875rem;
            display:flex;
            align-items:center;
            gap:0.625rem;
        }
        
        .alert-danger {
            background:#fee;
            color:#dc3545;
            border-left:4px solid #dc3545;
        }
        
        .login-hint {
            text-align:center;
            margin-top:1.5rem;
            font-size:0.8rem;
            color:#999;
            padding:1rem;
            background:#f8f9fa;
            border-radius:8px;
            line-height:1.6;
        }
        
        .login-hint i { color:#7c3aed; margin:0 2px; }
        
        .brand-side {
            flex: 1;
            background:linear-gradient(135deg, #6d28d9 0%, #7c3aed 25%, #8b5cf6 50%, #a855f7 75%, #c084fc 100%);
            display:flex;
            align-items:center;
            justify-content:center;
            padding: 2rem;
            position:relative;
            overflow:hidden;
        }
        
        .brand-side::before,
        .brand-side::after {
            content:'';
            position:absolute;
            border-radius:50%;
            opacity:0.08;
            animation:zoomFloat 12s ease-in-out infinite;
            background:white;
        }
        
        .brand-side::before {
            width:350px;
            height:350px;
            top:-80px;
            right:-80px;
        }
        
        .brand-side::after {
            width:300px;
            height:300px;
            bottom:-60px;
            left:-60px;
            animation-delay:6s;
        }
        
        @keyframes zoomFloat {
            0%, 100% { transform:translateY(0) scale(1); opacity:0.08; }
            25% { transform:translateY(-15px) scale(1.05); opacity:0.1; }
            50% { transform:translateY(0) scale(1.02); opacity:0.08; }
            75% { transform:translateY(15px) scale(1.08); opacity:0.12; }
        }
        
        .animated-circle {
            position:absolute;
            border-radius:50%;
            border:2px solid rgba(255, 255, 255, 0.12);
            animation:zoomPulse 10s ease-in-out infinite;
        }
        
        .circle-1 {
            width:150px;
            height:150px;
            top:15%;
            left:15%;
            animation-delay:2s;
            background:radial-gradient(circle, rgba(255, 255, 255, 0.06) 0%, transparent 70%);
        }
        
        .circle-2 {
            width:120px;
            height:120px;
            bottom:20%;
            right:15%;
            animation-delay:4s;
            background:radial-gradient(circle, rgba(255, 255, 255, 0.05) 0%, transparent 70%);
        }
        
        .circle-3 {
            width:80px;
            height:80px;
            top:50%;
            left:50%;
            animation-delay:6s;
            background:radial-gradient(circle, rgba(255, 255, 255, 0.08) 0%, transparent 70%);
        }
        
        @keyframes zoomPulse {
            0%, 100% { transform:scale(1); opacity:0.1; }
            50% { transform:scale(1.2); opacity:0.15; }
        }
        
        .brand-content {
            text-align:center;
            color:white;
            position:relative;
            z-index:10;
            max-width: 450px;
        }
        
        .brand-logo {
            width:90px;
            height:90px;
            background:rgba(255, 255, 255, 0.15);
            border-radius:25px;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            margin-bottom:2rem;
            font-size:40px;
            backdrop-filter:blur(20px);
            animation:pulse 3s ease-in-out infinite;
            box-shadow:0 15px 50px rgba(0, 0, 0, 0.15);
            border:2px solid rgba(255, 255, 255, 0.2);
        }
        
        @keyframes pulse {
            0%, 100% { transform:scale(1); }
            50% { transform:scale(1.05); }
        }
        
        .brand-title {
            font-size:2rem;
            font-weight:800;
            margin-bottom:0.75rem;
            letter-spacing:1px;
            text-shadow:0 2px 15px rgba(0, 0, 0, 0.15);
            background:linear-gradient(135deg, #fff 0%, #e0e0ff 100%);
            -webkit-background-clip:text;
            -webkit-text-fill-color:transparent;
            background-clip:text;
        }
        
        .brand-tagline {
            font-size:1rem;
            margin-bottom:2.5rem;
            opacity:0.95;
            font-weight:300;
            letter-spacing:0.5px;
            line-height:1.5;
        }
        
        .features { text-align:left; }
        
        .feature-item {
            display:flex;
            align-items:flex-start;
            margin-bottom:1.25rem;
            background:rgba(255, 255, 255, 0.1);
            padding:1rem 1.25rem;
            border-radius:12px;
            backdrop-filter:blur(8px);
            border:1px solid rgba(255, 255, 255, 0.15);
            transition:all 0.3s ease;
        }
        
        .feature-item:hover {
            background:rgba(255, 255, 255, 0.15);
            transform:translateX(4px);
        }
        
        .feature-icon {
            width:45px;
            height:45px;
            background:rgba(255, 255, 255, 0.2);
            border-radius:10px;
            display:flex;
            align-items:center;
            justify-content:center;
            margin-right:1rem;
            font-size:18px;
            flex-shrink:0;
            border:1px solid rgba(255, 255, 255, 0.2);
        }
        
        .feature-text h4 {
            font-size:0.95rem;
            font-weight:600;
            margin-bottom:0.25rem;
        }
        
        .feature-text p {
            font-size:0.8rem;
            opacity:0.9;
            margin:0;
            line-height:1.4;
            font-weight:300;
        }
        
        @media (max-width: 992px) {
            .login-wrapper { flex-direction: column; }
            .login-form-side {
                width: 100%;
                min-height: auto;
                padding: 2rem 1.5rem;
                order: 1;
            }
            .brand-side {
                width: 100%;
                min-height: auto;
                padding: 3rem 1.5rem;
                order: 2;
            }
            .features { max-width: 500px; margin: 0 auto; }
        }
        
        @media (max-width: 576px) {
            .login-form-wrapper {
                padding: 1.5rem;
                border-radius: 12px;
            }
            .brand-title { font-size: 1.5rem; }
            .feature-item { padding: 0.875rem 1rem; }
            .brand-logo { width: 70px; height: 70px; font-size: 32px; }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-form-side">
            <div class="login-form-wrapper">
                <div class="form-header">
                    <div class="logo-icon">
                        <i class="fas fa-users-cog"></i>
                    </div>
                    <h2>Welcome Back</h2>
                    <p>Sign in to access your account</p>
                </div>
                
                <?php if(isset($error)){ ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?php echo htmlentities($error); ?></span>
                    </div>
                <?php } ?>
                
                <form method="POST" id="loginForm">
                    <div class="form-floating">
                        <input class="form-control" name="username" id="username" type="text" placeholder="Username / Employee ID" required>
                        <label for="username">Username / Employee ID</label>
                        <div class="input-group-icon">
                            <i class="fas fa-user"></i>
                        </div>
                    </div>
                    
                    <div class="form-floating">
                        <input class="form-control" name="password" id="password" type="password" placeholder="Password" required>
                        <label for="password">Password</label>
                        <div class="input-group-icon right" onclick="togglePass()">
                            <i class="fas fa-eye" id="togglePassword"></i>
                        </div>
                    </div>
                    
                    <button class="btn btn-login" name="login" type="submit">
                        <i class="fas fa-sign-in-alt me-2"></i>LOGIN
                    </button>
                </form>
                
                <div class="login-hint">
                    <i class="fas fa-info-circle"></i>
                    <strong>Admin:</strong> Use your username<br>
                    <strong>Employee:</strong> Use your Employee ID<br>
                    <small>System auto-detects your account type</small>
                </div>
            </div>
        </div>
        
        <div class="brand-side">
            <div class="animated-circle circle-1"></div>
            <div class="animated-circle circle-2"></div>
            <div class="animated-circle circle-3"></div>
            
            <div class="brand-content">
                <div class="brand-logo">
                    <i class="fas fa-users-cog"></i>
                </div>
                <h1 class="brand-title">EMPLOYEE MANAGEMENT SYSTEM</h1>
                <p class="brand-tagline">Streamline your workforce management<br>with modern solutions</p>
                
                <div class="features">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div class="feature-text">
                            <h4>Attendance Tracking</h4>
                            <p>Real-time time in/out monitoring with automated payroll computation</p>
                        </div>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="feature-text">
                            <h4>Leave Management</h4>
                            <p>Streamlined leave application and approval process</p>
                        </div>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="feature-text">
                            <h4>Payroll & Reports</h4>
                            <p>Automated salary computation with SSS, PhilHealth & Pag-IBIG deductions</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function togglePass() {
            const password = document.getElementById('password');
            const icon = document.getElementById('togglePassword');
            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                password.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
        
        document.getElementById('username').focus();
    </script>
</body>
</html>