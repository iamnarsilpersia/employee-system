<?php
session_start();

// Only allow access if not logged in
if(isset($_SESSION['alogin']) || isset($_SESSION['emplogin'])){
    die("Please logout first before running setup.");
}

echo "<h2>Employee Management System - Setup</h2>";

// Step 1: Database connection
include('includes/dbconn.php');

if(!$dbh){
    die("Database connection failed!");
}

echo "<p>✓ Database connected</p>";

// Step 2: Create tables if not exist
try {
    // Create settings table
    $dbh->exec("CREATE TABLE IF NOT EXISTS settings (
        id INT PRIMARY KEY,
        daily_rate DECIMAL(10,2) DEFAULT 500,
        ot_rate DECIMAL(10,2) DEFAULT 50
    )");
    
    // Create deduction_rates table
    $dbh->exec("CREATE TABLE IF NOT EXISTS deduction_rates (
        id INT AUTO_INCREMENT PRIMARY KEY,
        type VARCHAR(50) NOT NULL UNIQUE,
        rate DECIMAL(10,2) NOT NULL DEFAULT 0,
        is_percentage TINYINT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Create tbltime_logs table
    $dbh->exec("CREATE TABLE IF NOT EXISTS tbltime_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        EmpID VARCHAR(100) NOT NULL,
        DateWorked DATE NOT NULL,
        TimeIn TIME DEFAULT NULL,
        TimeOut TIME DEFAULT NULL,
        HoursWorked DECIMAL(10,2) DEFAULT 0,
        Status VARCHAR(50) DEFAULT 'Pending',
        KEY `EmpID` (`EmpID`),
        FOREIGN KEY (`EmpID`) REFERENCES `tblemployees`(`EmpId`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1");
    
    // Create tblnotifications table
    $dbh->exec("CREATE TABLE IF NOT EXISTS tblnotifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_type VARCHAR(50) NOT NULL,
        user_id INT DEFAULT 0,
        title VARCHAR(255) NOT NULL,
        message TEXT,
        link VARCHAR(255) DEFAULT NULL,
        is_read TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1");
    
    // Insert default settings
    $check = $dbh->query("SELECT COUNT(*) FROM settings");
    if($check->fetchColumn() == 0) {
        $dbh->exec("INSERT INTO settings (id, daily_rate, ot_rate) VALUES (1, 500.00, 50.00)");
    }
    
    // Insert default deduction rates
    $check = $dbh->query("SELECT COUNT(*) FROM deduction_rates");
    if($check->fetchColumn() == 0) {
        $dbh->exec("INSERT INTO deduction_rates (type, rate, is_percentage) VALUES 
            ('SSS', 0, 1),
            ('PhilHealth', 0, 1),
            ('Pag-IBIG', 0, 1),
            ('Withholding Tax', 0, 1)");
    }
    
    echo "<p>✓ Database tables created/verified</p>";
    
} catch(Exception $e) {
    echo "<p style='color:red;'>✗ Error creating tables: " . $e->getMessage() . "</p>";
}

// Step 3: Update password hashing
if(isset($_POST['update_passwords'])){
    echo "<h3>Updating Passwords...</h3>";
    
    // Update admin passwords
    $admins = $dbh->query("SELECT id, UserName, Password FROM admin")->fetchAll(PDO::FETCH_OBJ);
    $default_passwords = [
        'admin' => 'Test@123',
        'bruno' => 'password',
        'greenwood' => 'password'
    ];
    
    foreach($admins as $admin){
        $new_password = $default_passwords[$admin->UserName] ?? 'password';
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        
        $update = $dbh->prepare("UPDATE admin SET Password=:pwd WHERE id=:id");
        $update->bindParam(':pwd', $hashed, PDO::PARAM_STR);
        $update->bindParam(':id', $admin->id, PDO::PARAM_INT);
        
        if($update->execute()){
            echo "<p style='color:green;'>✓ Updated admin: {$admin->UserName}</p>";
        }
    }
    
    // Update employee passwords
    $employees = $dbh->query("SELECT id, EmpId, Password FROM tblemployees")->fetchAll(PDO::FETCH_OBJ);
    $default_emp_passwords = [
        'ASTR001245' => 'password',
        'ASTR001369' => 'password',
        'ASTR004699' => 'password',
        'ASTR002996' => 'password',
        'ASTR001439' => 'password',
        'ASTR006946' => 'password',
        'ASTR000084' => 'password',
        'ASTR012447' => 'password'
    ];
    
    foreach($employees as $emp){
        $new_password = $default_emp_passwords[$emp->EmpId] ?? 'password';
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        
        $update = $dbh->prepare("UPDATE tblemployees SET Password=:pwd WHERE id=:id");
        $update->bindParam(':pwd', $hashed, PDO::PARAM_STR);
        $update->bindParam(':id', $emp->id, PDO::PARAM_INT);
        
        if($update->execute()){
            echo "<p style='color:green;'>✓ Updated employee: {$emp->EmpId}</p>";
        }
    }
    
    echo "<hr><p><strong>All passwords updated!</strong></p>";
    echo "<ul>";
    echo "<li>Admin 'admin' / Password: Test@123</li>";
    echo "<li>All employees / Password: password</li>";
    echo "</ul>";
}

// Step 4: Test login links
echo "<hr>";
echo "<h3>Test the System</h3>";
echo "<p><a href='index.php' style='padding:10px 20px; background:#007bff; color:white; text-decoration:none; border-radius:5px;'>Go to Login Page</a></p>";
echo "<p><strong>Admin Login:</strong> Username: admin | Password: Test@123</p>";
echo "<p><strong>Employee Login:</strong> Employee ID: ASTR001245 | Password: password</p>";
?>

<!DOCTYPE html>
<html>
<head>
    <title>EMS Setup</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        p { margin: 5px 0; }
    </style>
</head>
<body>
    <hr>
    <h3>Update Passwords</h3>
    <form method="POST" onsubmit="return confirm('This will update all passwords to use secure hashing. Continue?');">
        <button type="submit" name="update_passwords" style="padding:10px 20px; background:#28a745; color:white; border:none; cursor:pointer;">
            Update All Passwords
        </button>
    </form>
</body>
</html>
