<?php
date_default_timezone_set('Asia/Manila');

define('HOURS_PER_DAY', 8);

// Default values (will be overridden by database if available)
define('DEFAULT_DAILY_RATE', 500);
define('DEFAULT_OT_RATE', 50);

// Default deduction rates
define('DEFAULT_SSS_RATE', 0);
define('DEFAULT_PHILHEALTH_RATE', 0);
define('DEFAULT_PAGIBIG_RATE', 0);
define('DEFAULT_TAX_RATE', 0);

// Try to load rates from database
try {
    // Use existing connection from dbconn.php if available
    global $dbh;
    
    // If dbconn connection exists, use it; otherwise create new one
    if(!isset($dbh) || $dbh === null) {
        $config_dbh = new PDO("mysql:host=localhost;dbname=employeeleavedb", 'root', '', array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
    } else {
        $config_dbh = $dbh;
    }
    $config_dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create deduction_rates table if not exists
    $config_dbh->exec("CREATE TABLE IF NOT EXISTS deduction_rates (
        id INT AUTO_INCREMENT PRIMARY KEY,
        type VARCHAR(50) NOT NULL UNIQUE,
        rate DECIMAL(10,2) NOT NULL DEFAULT 0,
        is_percentage TINYINT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Insert default values if empty
    $check = $config_dbh->query("SELECT COUNT(*) FROM deduction_rates");
    if($check->fetchColumn() == 0) {
        $config_dbh->exec("INSERT INTO deduction_rates (type, rate, is_percentage) VALUES 
            ('SSS', 0, 1),
            ('PhilHealth', 0, 1),
            ('Pag-IBIG', 0, 1),
            ('Withholding Tax', 0, 1)");
    }

    $config_sql = "SELECT daily_rate, ot_rate FROM settings WHERE id=1";
    $config_query = $config_dbh->prepare($config_sql);
    $config_query->execute();
    $config_row = $config_query->fetch(PDO::FETCH_OBJ);

    if($config_row){
        define('DAILY_RATE', floatval($config_row->daily_rate));
        define('OT_RATE', floatval($config_row->ot_rate));
    } else {
        define('DAILY_RATE', DEFAULT_DAILY_RATE);
        define('OT_RATE', DEFAULT_OT_RATE);

        $insert_sql = "INSERT INTO settings (id, daily_rate, ot_rate) VALUES (1, 500, 50)";
        $config_dbh->exec($insert_sql);
    }

    // Load deduction rates
    $deduction_sql = "SELECT type, rate FROM deduction_rates";
    $deduction_query = $config_dbh->prepare($deduction_sql);
    $deduction_query->execute();
    
    $deductions = [];
    while($row = $deduction_query->fetch(PDO::FETCH_OBJ)){
        $deductions[$row->type] = $row->rate;
    }
    
    define('SSS_RATE', isset($deductions['SSS']) ? floatval($deductions['SSS']) : DEFAULT_SSS_RATE);
    define('PHILHEALTH_RATE', isset($deductions['PhilHealth']) ? floatval($deductions['PhilHealth']) : DEFAULT_PHILHEALTH_RATE);
    define('PAGIBIG_RATE', isset($deductions['Pag-IBIG']) ? floatval($deductions['Pag-IBIG']) : DEFAULT_PAGIBIG_RATE);
    define('TAX_RATE', isset($deductions['Withholding Tax']) ? floatval($deductions['Withholding Tax']) : DEFAULT_TAX_RATE);

} catch (PDOException $e) {
    define('DAILY_RATE', DEFAULT_DAILY_RATE);
    define('OT_RATE', DEFAULT_OT_RATE);
    define('SSS_RATE', DEFAULT_SSS_RATE);
    define('PHILHEALTH_RATE', DEFAULT_PHILHEALTH_RATE);
    define('PAGIBIG_RATE', DEFAULT_PAGIBIG_RATE);
    define('TAX_RATE', DEFAULT_TAX_RATE);
}
