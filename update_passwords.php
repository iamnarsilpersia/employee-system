<?php
session_start();
// Only allow if no sessions are active (fresh run)
if(isset($_SESSION['alogin']) || isset($_SESSION['emplogin'])){
    die("Please logout first before running this script.");
}

include('includes/dbconn.php');

echo "<h2>Password Update Script</h2>";
echo "<p>This script will update all MD5 passwords to bcrypt hashing.</p>";

// Default passwords for testing
$default_passwords = [
    'admin' => 'Test@123',
    'bruno' => 'password',
    'greenwood' => 'password',
    'ASTR001245' => 'password',
    'ASTR001369' => 'password',
    'ASTR004699' => 'password',
    'ASTR002996' => 'password',
    'ASTR001439' => 'password',
    'ASTR006946' => 'password',
    'ASTR000084' => 'password',
    'ASTR012447' => 'password'
];

if(isset($_POST['update'])){
    $updated = 0;
    $errors = 0;
    
    // Update admin passwords
    $sql = "SELECT id, UserName, Password FROM admin";
    $query = $dbh->prepare($sql);
    $query->execute();
    $admins = $query->fetchAll(PDO::FETCH_OBJ);
    
    foreach($admins as $admin){
        $password = $default_passwords[$admin->UserName] ?? 'password';
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        
        $update = $dbh->prepare("UPDATE admin SET Password=:pwd WHERE id=:id");
        $update->bindParam(':pwd', $hashed, PDO::PARAM_STR);
        $update->bindParam(':id', $admin->id, PDO::PARAM_INT);
        
        if($update->execute()){
            $updated++;
            echo "<p style='color:green;'>Updated admin: {$admin->UserName}</p>";
        } else {
            $errors++;
            echo "<p style='color:red;'>Error updating admin: {$admin->UserName}</p>";
        }
    }
    
    // Update employee passwords
    $sql = "SELECT id, EmpId, Password FROM tblemployees";
    $query = $dbh->prepare($sql);
    $query->execute();
    $employees = $query->fetchAll(PDO::FETCH_OBJ);
    
    foreach($employees as $emp){
        $password = $default_passwords[$emp->EmpId] ?? 'password';
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        
        $update = $dbh->prepare("UPDATE tblemployees SET Password=:pwd WHERE id=:id");
        $update->bindParam(':pwd', $hashed, PDO::PARAM_STR);
        $update->bindParam(':id', $emp->id, PDO::PARAM_INT);
        
        if($update->execute()){
            $updated++;
            echo "<p style='color:green;'>Updated employee: {$emp->EmpId}</p>";
        } else {
            $errors++;
            echo "<p style='color:red;'>Error updating employee: {$emp->EmpId}</p>";
        }
    }
    
    echo "<hr><h3>Summary</h3>";
    echo "<p>Updated: $updated records</p>";
    echo "<p>Errors: $errors</p>";
    echo "<p><strong>Done! You can now login with:</strong></p>";
    echo "<ul>";
    echo "<li>Admin: admin / Test@123</li>";
    echo "<li>Employee: ASTR001245 / password</li>";
    echo "</ul>";
    echo "<p><a href='index.php'>Go to Login</a></p>";
} else {
    echo "<form method='POST'>";
    echo "<p><strong>Warning:</strong> This will update all passwords to use bcrypt hashing.</p>";
    echo "<p>Default passwords will be set to:</p>";
    echo "<ul>";
    echo "<li>Admin 'admin': Test@123</li>";
    echo "<li>All others: password</li>";
    echo "</ul>";
    echo "<button type='submit' name='update' style='padding:10px 20px; background:red; color:white; border:none; cursor:pointer;'>Update Passwords Now</button>";
    echo "</form>";
}
?>
