<?php
// Setup deductions table - one-time setup script
include('../includes/dbconn.php');
include('../includes/functions.php');

if(strlen($_SESSION['alogin'])==0){   
    header('location:../index.php');
    exit;
}

// Create table if not exists
$dbh->exec("CREATE TABLE IF NOT EXISTS deduction_rates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(50) NOT NULL UNIQUE,
    rate DECIMAL(10,2) NOT NULL DEFAULT 0,
    is_percentage TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Insert default values if empty
$check = $dbh->query("SELECT COUNT(*) as cnt FROM deduction_rates");
$row = $check->fetch(PDO::FETCH_ASSOC);
if($row['cnt'] == 0) {
    $dbh->exec("INSERT INTO deduction_rates (type, rate, is_percentage) VALUES 
        ('SSS', 0, 1),
        ('PhilHealth', 0, 1),
        ('Pag-IBIG', 0, 1),
        ('Withholding Tax', 0, 1)");
    echo "Deduction rates table initialized.";
} else {
    echo "Table already exists.";
}
?>
