<?php
session_start();
include('includes/dbconn.php');

if(isset($_POST['login'])){
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    // Try admin login first
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
    
    // Try employee login
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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" type="image/png" href="assets/images/icon/favicon.ico">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Segoe UI',system-ui,-apple-system,sans-serif; min-height:100vh; overflow:hidden; background:#f8f9fa; }
        .container-fluid { min-height:100vh; padding:0; }
        .row { min-height:100vh; }
        
        /* Left Side - Login Form */
        .login-form-side {
            background:#ffffff;
            display:flex;
            align-items:center;
            justify-content:center;
            padding:40px 20px;
            animation:slideInLeft 0.8s ease-out;
        }
        @keyframes slideInLeft {
            from { opacity:0; transform:translateX(-30px); }
            to { opacity:1; transform:translateX(0); }
        }
        
        .login-form-wrapper {
            width:100%;
            max-width:380px;
            border:1px solid #e0e0e0;
            border-radius:12px;
            padding:25px;
            background:#fff;
            box-shadow:0 10px 40px rgba(0,0,0,0.1);
        }
        
        .form-header {
            margin-bottom:35px;
            text-align:center;
        }
        
        .form-header .logo-icon {
            width:70px;
            height:70px;
            background:linear-gradient(135deg, #7c3aed 0%, #a855f7 100%);
            border-radius:20px;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            margin-bottom:20px;
            font-size:28px;
            color:white;
            box-shadow:0 10px 30px rgba(124,58,237,0.3);
        }
        
        .form-header h2 {
            font-size:28px;
            font-weight:700;
            color:#1a1a2e;
            margin-bottom:8px;
            letter-spacing:-0.5px;
        }
        
        .form-header p {
            color:#6c757d;
            font-size:15px;
            font-weight:400;
        }
        
        .form-floating { margin-bottom:20px; position:relative; }
        
        .form-floating > .form-control {
            padding:1rem 1rem 1rem 3rem;
            height:calc(3.5rem + 2px);
            border-radius:12px;
            border:2px solid #e9ecef;
            transition:all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-size:15px;
            background:#f8f9fa;
        }
        
        .form-floating > .form-control:focus {
            border-color:#7c3aed;
            box-shadow:0 0 0 4px rgba(124, 58, 237, 0.1);
            background:#fff;
        }
        
        .form-floating > label {
            padding:1rem 1rem 1rem 3rem;
            font-size:15px;
            color:#6c757d;
        }
        
        .input-group-icon {
            position:absolute;
            left:15px;
            top:50%;
            transform:translateY(-50%);
            color:#6c757d;
            z-index:10;
            font-size:16px;
            transition:color 0.3s ease;
        }
        
        .form-floating > .form-control:focus ~ .input-group-icon {
            color:#7c3aed;
        }
        
        .input-group-icon.right {
            left:auto;
            right:15px;
            cursor:pointer;
        }
        
        .btn-login {
            background:linear-gradient(135deg, #7c3aed 0%, #a855f7 100%);
            color:white;
            border:none;
            padding:16px 30px;
            border-radius:12px;
            font-size:16px;
            font-weight:600;
            width:100%;
            transition:all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            margin-top:10px;
            letter-spacing:0.5px;
            box-shadow:0 4px 15px rgba(124, 58, 237, 0.3);
        }
        
        .btn-login:hover {
            transform:translateY(-3px);
            box-shadow:0 12px 30px rgba(124, 58, 237, 0.4);
            color:white;
        }
        
        .alert {
            border-radius:12px;
            border:none;
            padding:16px 20px;
            margin-bottom:25px;
            animation:shake 0.6s ease-in-out;
            font-size:14px;
            display:flex;
            align-items:center;
            gap:10px;
        }
        
        @keyframes shake {
            10%, 90% { transform:translateX(-2px); }
            20%, 80% { transform:translateX(4px); }
            30%, 50%, 70% { transform:translateX(-6px); }
            40%, 60% { transform:translateX(6px); }
        }
        
        .alert-danger {
            background:linear-gradient(135deg, #fee 0%, #fdd 100%);
            color:#dc3545;
            border-left:4px solid #dc3545;
        }
        
        .login-hint {
            text-align:center;
            margin-top:25px;
            font-size:13px;
            color:#999;
            padding:15px;
            background:#f8f9fa;
            border-radius:10px;
            line-height:1.6;
        }
        
        .login-hint i {
            color:#7c3aed;
            margin:0 3px;
        }
        
        /* Right Side - Purple Background */
        .brand-side {
            background:linear-gradient(135deg, #6d28d9 0%, #7c3aed 25%, #8b5cf6 50%, #a855f7 75%, #c084fc 100%);
            display:flex;
            align-items:center;
            justify-content:center;
            padding:60px 50px;
            position:relative;
            overflow:hidden;
            animation:slideInRight 0.8s ease-out;
        }
        
        @keyframes slideInRight {
            from { opacity:0; transform:translateX(30px); }
            to { opacity:1; transform:translateX(0); }
        }
        
        /* Animated Background Elements */
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
            width:400px;
            height:400px;
            top:-100px;
            right:-100px;
            animation-delay:0s;
        }
        
        .brand-side::after {
            width:350px;
            height:350px;
            background:white;
            bottom:-80px;
            left:-80px;
            animation-delay:6s;
        }
        
        @keyframes zoomFloat {
            0%, 100% { transform:translateY(0) scale(1); opacity:0.08; }
            25% { transform:translateY(-20px) scale(1.1); opacity:0.12; }
            50% { transform:translateY(0) scale(1.05); opacity:0.1; }
            75% { transform:translateY(20px) scale(1.15); opacity:0.15; }
        }
        
        .animated-circle {
            position:absolute;
            border-radius:50%;
            border:2px solid rgba(255, 255, 255, 0.12);
            animation:zoomPulse 10s ease-in-out infinite;
        }
        
        .circle-1 {
            width:180px;
            height:180px;
            top:10%;
            left:10%;
            animation-delay:2s;
            background:radial-gradient(circle, rgba(255, 255, 255, 0.08) 0%, transparent 70%);
        }
        
        .circle-2 {
            width:140px;
            height:140px;
            bottom:20%;
            right:15%;
            animation-delay:4s;
            background:radial-gradient(circle, rgba(255, 255, 255, 0.06) 0%, transparent 70%);
        }
        
        .circle-3 {
            width:90px;
            height:90px;
            top:45%;
            left:45%;
            animation-delay:6s;
            background:radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
        }
        
        @keyframes zoomPulse {
            0%, 100% { transform:scale(1); opacity:0.12; }
            50% { transform:scale(1.3); opacity:0.2; }
        }
        
        .brand-content {
            text-align:center;
            color:white;
            position:relative;
            z-index:10;
            animation:fadeInUp 1s ease-out 0.5s both;
        }
        
        @keyframes fadeInUp {
            from { opacity:0; transform:translateY(40px); }
            to { opacity:1; transform:translateY(0); }
        }
        
        .brand-logo {
            width:100px;
            height:100px;
            background:rgba(255, 255, 255, 0.15);
            border-radius:30px;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            margin-bottom:35px;
            font-size:45px;
            backdrop-filter:blur(20px);
            animation:pulse 3s ease-in-out infinite;
            box-shadow:0 20px 60px rgba(0, 0, 0, 0.2);
            border:2px solid rgba(255, 255, 255, 0.2);
        }
        
        @keyframes pulse {
            0%, 100% { transform:scale(1); box-shadow:0 20px 60px rgba(0, 0, 0, 0.2); }
            50% { transform:scale(1.08); box-shadow:0 25px 70px rgba(0, 0, 0, 0.3); }
        }
        
        .brand-title {
            font-size:38px;
            font-weight:800;
            margin-bottom:15px;
            letter-spacing:2px;
            text-shadow:0 4px 20px rgba(0, 0, 0, 0.2);
            background:linear-gradient(135deg, #fff 0%, #e0e0ff 100%);
            -webkit-background-clip:text;
            -webkit-text-fill-color:transparent;
            background-clip:text;
        }
        
        .brand-tagline {
            font-size:17px;
            margin-bottom:60px;
            opacity:0.95;
            font-weight:300;
            letter-spacing:0.5px;
            line-height:1.6;
        }
        
        .features {
            text-align:left;
            max-width:420px;
            margin:0 auto;
        }
        
        .feature-item {
            display:flex;
            align-items:flex-start;
            margin-bottom:30px;
            animation:fadeInLeft 0.8s ease-out both;
            background:rgba(255, 255, 255, 0.1);
            padding:20px;
            border-radius:16px;
            backdrop-filter:blur(10px);
            border:1px solid rgba(255, 255, 255, 0.15);
            transition:all 0.3s ease;
        }
        
        .feature-item:hover {
            background:rgba(255, 255, 255, 0.18);
            transform:translateX(5px);
        }
        
        .feature-item:nth-child(1) { animation-delay:0.8s; }
        .feature-item:nth-child(2) { animation-delay:1.1s; }
        .feature-item:nth-child(3) { animation-delay:1.4s; }
        
        @keyframes fadeInLeft {
            from { opacity:0; transform:translateX(40px); }
            to { opacity:1; transform:translateX(0); }
        }
        
        .feature-icon {
            width:55px;
            height:55px;
            background:rgba(255, 255, 255, 0.2);
            border-radius:14px;
            display:flex;
            align-items:center;
            justify-content:center;
            margin-right:18px;
            font-size:22px;
            flex-shrink:0;
            backdrop-filter:blur(10px);
            border:1px solid rgba(255, 255, 255, 0.25);
        }
        
        .feature-text h4 {
            font-size:18px;
            font-weight:700;
            margin-bottom:6px;
            letter-spacing:0.3px;
        }
        
        .feature-text p {
            font-size:14px;
            opacity:0.9;
            margin:0;
            line-height:1.5;
            font-weight:300;
        }
        
        @media (max-width:768px) {
            .brand-side { display:none; }
            .login-form-side { padding:20px 15px; }
        }
        
        @media (min-width:1400px) {
            .login-form-wrapper { max-width:420px; }
            .brand-title { font-size:42px; }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Left Side - Login Form -->
            <div class="col-lg-6 login-form-side">
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
                        <div class="form-floating mb-3">
                            <input class="form-control" name="username" id="username" type="text" placeholder="Username / Employee ID" required>
                            <label for="username">Username / Employee ID</label>
                            <div class="input-group-icon">
                                <i class="fas fa-user"></i>
                            </div>
                        </div>
                        
                        <div class="form-floating mb-3">
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
            
            <!-- Right Side - Brand -->
            <div class="col-lg-6 brand-side">
                <div class="animated-circle circle-1"></div>
                <div class="animated-circle circle-2"></div>
                <div class="animated-circle circle-3"></div>
                
                <div class="brand-content">
                    <div class="brand-logo">
                        <i class="fas fa-users-cog"></i>
                    </div>
                    <h1 class="brand-title">EMPLOYEE<br>MANAGEMENT<br>SYSTEM</h1>
                    <p class="brand-tagline">Streamline your workforce management<br>with modern solutions</p>
                    
                    <div class="features">
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-user-check"></i>
                            </div>
                            <div class="feature-text">
                                <h4>Attendance Tracking</h4>
                                <p>Real-time time in/out monitoring with automated payroll computation and reporting</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div class="feature-text">
                                <h4>Leave Management</h4>
                                <p>Streamlined leave application and approval process for better productivity</p>
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
        
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            
            if (!username || !password) {
                e.preventDefault();
                alert('Please fill in all fields');
            }
        });
        
        // Auto-focus username field
        document.getElementById('username').focus();
    </script>
</body>
</html>
