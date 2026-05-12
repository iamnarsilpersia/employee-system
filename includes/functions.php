<?php

function require_admin_login() {
    if(empty($_SESSION['alogin'])) {
        header('location:../index.php');
        exit;
    }
}

function require_employee_login() {
    if(empty($_SESSION['emplogin'])) {
        header('location:../index.php');
        exit;
    }
}

function get_employee_db_id($dbh, $empid) {
    $sql = "SELECT id FROM tblemployees WHERE EmpId=:empid LIMIT 1";
    $query = $dbh->prepare($sql);
    $query->bindParam(':empid', $empid, PDO::PARAM_STR);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_OBJ);
    return $result ? $result->id : null;
}

function get_employee_by_id($dbh, $eid) {
    $sql = "SELECT * FROM tblemployees WHERE id=:eid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':eid', $eid, PDO::PARAM_INT);
    $query->execute();
    return $query->fetch(PDO::FETCH_OBJ);
}

function get_employee_by_empid($dbh, $empid) {
    $sql = "SELECT * FROM tblemployees WHERE EmpId=:empid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':empid', $empid, PDO::PARAM_STR);
    $query->execute();
    return $query->fetch(PDO::FETCH_OBJ);
}

function get_all_employees($dbh) {
    $sql = "SELECT EmpId,FirstName,LastName,Department,Status,RegDate,id FROM tblemployees ORDER BY FirstName, LastName";
    $query = $dbh->prepare($sql);
    $query->execute();
    return $query->fetchAll(PDO::FETCH_OBJ);
}

function get_all_departments($dbh) {
    $sql = "SELECT DepartmentName FROM tbldepartments ORDER BY DepartmentName";
    $query = $dbh->prepare($sql);
    $query->execute();
    return $query->fetchAll(PDO::FETCH_OBJ);
}

function toggle_employee_status($dbh, $id, $status) {
    $sql = "UPDATE tblemployees SET Status=:status WHERE id=:id";
    $query = $dbh->prepare($sql);
    $query->bindParam(':id', $id, PDO::PARAM_INT);
    $query->bindParam(':status', $status, PDO::PARAM_INT);
    return $query->execute();
}

function delete_employee($dbh, $id) {
    $sql = "DELETE FROM tblemployees WHERE id=:id";
    $query = $dbh->prepare($sql);
    $query->bindParam(':id', $id, PDO::PARAM_INT);
    return $query->execute();
}

function update_employee_password($dbh, $eid, $password) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $sql = "UPDATE tblemployees SET Password=:password WHERE id=:eid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':password', $hashed_password, PDO::PARAM_STR);
    $query->bindParam(':eid', $eid, PDO::PARAM_INT);
    return $query->execute();
}

function generate_employee_id($dbh) {
    $sql = "SELECT MAX(CAST(SUBSTRING(EmpId, 5) AS UNSIGNED)) as max_id FROM tblemployees WHERE EmpId LIKE 'ASTR%'";
    $query = $dbh->prepare($sql);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_ASSOC);
    $next_id = ($result && $result['max_id']) ? $result['max_id'] + 1 : 1;
    return 'ASTR' . str_pad($next_id, 6, '0', STR_PAD_LEFT);
}

function delete_department($dbh, $id) {
    $sql = "DELETE FROM tbldepartments WHERE id=:id";
    $query = $dbh->prepare($sql);
    $query->bindParam(':id', $id, PDO::PARAM_INT);
    return $query->execute();
}

function delete_leave_type($dbh, $id) {
    $sql = "DELETE FROM tblleavetype WHERE id=:id";
    $query = $dbh->prepare($sql);
    $query->bindParam(':id', $id, PDO::PARAM_INT);
    return $query->execute();
}

function update_leave_type($dbh, $id, $leaveType, $maxPerMonth) {
    $sql = "UPDATE tblleavetype SET LeaveType=:type, max_per_month=:max WHERE id=:id";
    $query = $dbh->prepare($sql);
    $query->bindParam(':id', $id, PDO::PARAM_INT);
    $query->bindParam(':type', $leaveType, PDO::PARAM_STR);
    $query->bindParam(':max', $maxPerMonth, PDO::PARAM_INT);
    return $query->execute();
}

function get_leave_usage_this_month($dbh, $empid, $leaveType) {
    $month = date('n');
    $year = date('Y');
    $sql = "SELECT COUNT(*) FROM tblleaves WHERE empid=:empid AND LeaveType=:type AND MONTH(FromDate)=:m AND YEAR(FromDate)=:y AND Status!=2";
    $query = $dbh->prepare($sql);
    $query->bindParam(':empid', $empid, PDO::PARAM_INT);
    $query->bindParam(':type', $leaveType, PDO::PARAM_STR);
    $query->bindParam(':m', $month, PDO::PARAM_INT);
    $query->bindParam(':y', $year, PDO::PARAM_INT);
    $query->execute();
    return $query->fetchColumn();
}

function get_total_leave_usage_this_month($dbh, $empid) {
    $month = date('n');
    $year = date('Y');
    $sql = "SELECT COUNT(*) FROM tblleaves WHERE empid=:empid AND MONTH(FromDate)=:m AND YEAR(FromDate)=:y AND Status!=2";
    $query = $dbh->prepare($sql);
    $query->bindParam(':empid', $empid, PDO::PARAM_INT);
    $query->bindParam(':m', $month, PDO::PARAM_INT);
    $query->bindParam(':y', $year, PDO::PARAM_INT);
    $query->execute();
    return $query->fetchColumn();
}

function get_global_leave_limit($dbh) {
    $sql = "SELECT leave_limit FROM settings WHERE id=1";
    $query = $dbh->prepare($sql);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_OBJ);
    return $result ? ($result->leave_limit ?? 0) : 0;
}

function delete_admin($dbh, $id) {
    $sql = "DELETE FROM admin WHERE id=:id";
    $query = $dbh->prepare($sql);
    $query->bindParam(':id', $id, PDO::PARAM_INT);
    return $query->execute();
}

function validate_password($password) {
    $errors = [];
    if(strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters";
    }
    if(!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    }
    if(!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter";
    }
    if(!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number";
    }
    return $errors;
}

function sanitize_input($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function initialize_database_tables($dbh) {
    $tables = [
        "CREATE TABLE IF NOT EXISTS tbltime_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            EmpID VARCHAR(50) NOT NULL,
            DateWorked DATE NOT NULL,
            TimeIn TIME NULL,
            TimeOut TIME NULL,
            HoursWorked DECIMAL(10,2) DEFAULT 0,
            Status VARCHAR(20) DEFAULT 'Pending',
            INDEX (EmpID),
            INDEX (DateWorked)
        )",
        "CREATE TABLE IF NOT EXISTS tbl_document_types (
            id INT AUTO_INCREMENT PRIMARY KEY,
            type_name VARCHAR(100) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS tbl_employee_documents (
            id INT AUTO_INCREMENT PRIMARY KEY,
            emp_id INT NOT NULL,
            doc_type_id INT NOT NULL,
            file_name VARCHAR(255) NOT NULL,
            original_name VARCHAR(255) NOT NULL,
            description TEXT,
            status VARCHAR(20) DEFAULT 'Pending',
            admin_reviewed TINYINT DEFAULT 0,
            admin_reviewed_on DATETIME DEFAULT NULL,
            uploaded_on DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX (emp_id)
        )",
        "CREATE TABLE IF NOT EXISTS settings (
            id INT PRIMARY KEY,
            daily_rate DECIMAL(10,2) DEFAULT 500,
            ot_rate DECIMAL(10,2) DEFAULT 50
        )",
        "CREATE TABLE IF NOT EXISTS deduction_rates (
            id INT AUTO_INCREMENT PRIMARY KEY,
            type VARCHAR(50) NOT NULL UNIQUE,
            rate DECIMAL(10,2) NOT NULL DEFAULT 0,
            is_percentage TINYINT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )"
    ];

    foreach($tables as $table_sql) {
        $dbh->exec($table_sql);
    }

    $check = $dbh->query("SELECT COUNT(*) FROM tbl_document_types")->fetchColumn();
    if($check == 0) {
        $dbh->exec("INSERT INTO tbl_document_types (type_name) VALUES 
            ('Resume/CV'), ('Diploma'), ('Birth Certificate'),
            ('Government ID'), ('Tax Documents'), ('Other')");
    }

    $check = $dbh->query("SELECT COUNT(*) FROM settings")->fetchColumn();
    if($check == 0) {
        $dbh->exec("INSERT INTO settings (id, daily_rate, ot_rate, leave_limit) VALUES (1, 500, 50, 0)");
    }

    try {
        $dbh->exec("ALTER TABLE settings ADD COLUMN IF NOT EXISTS leave_limit INT DEFAULT 0");
    } catch (Exception $e) {}

    $check = $dbh->query("SELECT COUNT(*) FROM deduction_rates")->fetchColumn();
    if($check == 0) {
        $dbh->exec("INSERT INTO deduction_rates (type, rate, is_percentage) VALUES
            ('SSS', 0, 1), ('PhilHealth', 0, 1), ('Pag-IBIG', 0, 1), ('Withholding Tax', 0, 1)");
    }

    try {
        $dbh->exec("ALTER TABLE tblleavetype ADD COLUMN IF NOT EXISTS max_per_month INT DEFAULT 999");
    } catch (Exception $e) {}
}

function time_in($dbh, $empid) {
    $today = date('Y-m-d');
    $check_sql = "SELECT * FROM tbltime_logs WHERE EmpID=:empid AND DateWorked=:date ORDER BY id DESC LIMIT 1";
    $check_query = $dbh->prepare($check_sql);
    $check_query->bindParam(':empid', $empid, PDO::PARAM_STR);
    $check_query->bindParam(':date', $today, PDO::PARAM_STR);
    $check_query->execute();
    $existing = $check_query->fetch(PDO::FETCH_OBJ);

    if($existing && $existing->TimeOut == NULL) {
        return ['success' => false, 'message' => "You are already timed in. Please time out first."];
    }

    if($existing && $existing->TimeIn) {
        $timeInTs = strtotime($existing->TimeIn);
        $timeOutTs = strtotime($existing->TimeOut ?: date('H:i:s'));
        $eightHoursLater = $timeInTs + (8 * 3600);
        if(time() < $eightHoursLater) {
            $remaining = ceil(($eightHoursLater - time()) / 60);
            return ['success' => false, 'message' => "You must wait " . $remaining . " more minutes before you can time in again."];
        }
    }

    $timeIn = date('H:i:s');
    $insert_sql = "INSERT INTO tbltime_logs (EmpID, DateWorked, TimeIn, Status) VALUES(:empid, :date, :timein, 'Pending')";
    $insert_query = $dbh->prepare($insert_sql);
    $insert_query->bindParam(':empid', $empid, PDO::PARAM_STR);
    $insert_query->bindParam(':date', $today, PDO::PARAM_STR);
    $insert_query->bindParam(':timein', $timeIn, PDO::PARAM_STR);

    if($insert_query->execute()) {
        return ['success' => true, 'message' => "Time In recorded at " . date('h:i A', strtotime($timeIn))];
    }
    return ['success' => false, 'message' => "Failed to record Time In. Please try again."];
}

function time_out($dbh, $empid, $allow_early = false) {
    $today = date('Y-m-d');
    $sql = "SELECT * FROM tbltime_logs WHERE EmpID=:empid AND DateWorked=:date ORDER BY id DESC LIMIT 1";
    $query = $dbh->prepare($sql);
    $query->bindParam(':empid', $empid, PDO::PARAM_STR);
    $query->bindParam(':date', $today, PDO::PARAM_STR);
    $query->execute();
    $record = $query->fetch(PDO::FETCH_OBJ);

    if(!$record) {
        return ['success' => false, 'message' => "No Time In record found for today."];
    }

    if($record->TimeOut != NULL) {
        return ['success' => false, 'message' => "You have already timed out today."];
    }

    if(!$allow_early && $record->TimeIn) {
        $timeInTs = strtotime($record->TimeIn);
        $eightHoursLater = $timeInTs + (8 * 3600);
        if(time() < $eightHoursLater) {
            return ['success' => false, 'message' => "You must wait until 8 hours have passed to time out. Use 'Time Out Anyway' if needed."];
        }
    }

    $timeOut = date('H:i:s');
    $hoursWorked = 0;
    if($record->TimeIn) {
        $timeIn = strtotime($record->TimeIn);
        $timeOutTs = strtotime($timeOut);
        $hoursWorked = round(($timeOutTs - $timeIn) / 3600, 2);
    }

    $update_sql = "UPDATE tbltime_logs SET TimeOut=:timeout, HoursWorked=:hours WHERE id=:id";
    $update_query = $dbh->prepare($update_sql);
    $update_query->bindParam(':timeout', $timeOut, PDO::PARAM_STR);
    $update_query->bindParam(':hours', $hoursWorked, PDO::PARAM_STR);
    $update_query->bindParam(':id', $record->id, PDO::PARAM_INT);

    if($update_query->execute()) {
        return ['success' => true, 'message' => "Time Out recorded at " . date('h:i A', strtotime($timeOut)) . ". Hours worked: " . $hoursWorked];
    }
    return ['success' => false, 'message' => "Failed to record Time Out. Please try again."];
}

function get_today_time_record($dbh, $empid) {
    $today = date('Y-m-d');
    $sql = "SELECT * FROM tbltime_logs WHERE EmpID=:empid AND DateWorked=:date ORDER BY id DESC LIMIT 1";
    $query = $dbh->prepare($sql);
    $query->bindParam(':empid', $empid, PDO::PARAM_STR);
    $query->bindParam(':date', $today, PDO::PARAM_STR);
    $query->execute();
    return $query->fetch(PDO::FETCH_OBJ);
}

function get_monthly_time_records($dbh, $empid, $month, $year) {
    $from = date("Y-m-01", strtotime("$year-$month-01"));
    $to = date("Y-m-t", strtotime($from));
    $sql = "SELECT * FROM tbltime_logs WHERE EmpID=:empid AND DateWorked BETWEEN :f AND :t ORDER BY DateWorked DESC";
    $query = $dbh->prepare($sql);
    $query->bindParam(':empid', $empid, PDO::PARAM_STR);
    $query->bindParam(':f', $from, PDO::PARAM_STR);
    $query->bindParam(':t', $to, PDO::PARAM_STR);
    $query->execute();
    return $query->fetchAll(PDO::FETCH_OBJ);
}

function calculate_payroll($records, $daily_rate) {
    $total_hours = 0;
    $total_pay = 0;
    foreach($records as $row) {
        $pay = ($row->HoursWorked * $daily_rate) / 8;
        $total_hours += $row->HoursWorked;
        $total_pay += $pay;
    }
    return ['total_hours' => $total_hours, 'total_pay' => $total_pay];
}

function get_deduction_rates($dbh) {
    $deductions = $dbh->query("SELECT * FROM deduction_rates")->fetchAll(PDO::FETCH_OBJ);
    $rates = [
        'SSS' => 0,
        'PhilHealth' => 0,
        'Pag-IBIG' => 0,
        'Withholding Tax' => 0
    ];
    foreach($deductions as $d) {
        if(isset($rates[$d->type])) {
            $rates[$d->type] = floatval($d->rate);
        }
    }
    return $rates;
}

function calculate_philippine_tax($annual_taxable_income) {
    if ($annual_taxable_income <= 0) {
        return 0;
    }
    
    $tax = 0;
    
    if ($annual_taxable_income <= 20000) {
        $tax = 0;
    } elseif ($annual_taxable_income <= 30000) {
        $tax = ($annual_taxable_income - 20000) * 0.20;
    } elseif ($annual_taxable_income <= 40000) {
        $tax = 2000 + ($annual_taxable_income - 30000) * 0.25;
    } elseif ($annual_taxable_income <= 80000) {
        $tax = 4500 + ($annual_taxable_income - 40000) * 0.30;
    } elseif ($annual_taxable_income <= 130000) {
        $tax = 16500 + ($annual_taxable_income - 80000) * 0.30;
    } elseif ($annual_taxable_income <= 250000) {
        $tax = 31500 + ($annual_taxable_income - 130000) * 0.32;
    } elseif ($annual_taxable_income <= 500000) {
        $tax = 69500 + ($annual_taxable_income - 250000) * 0.35;
    } else {
        $tax = 157500 + ($annual_taxable_income - 500000) * 0.35;
    }
    
    return round($tax, 2);
}

function get_sss_contribution($monthly_salary) {
    if ($monthly_salary <= 0) return 0;
    
    $ee = 0;
    
    if ($monthly_salary <= 3250) {
        $ee = 135;
    } elseif ($monthly_salary <= 3750) {
        $ee = 157.50;
    } elseif ($monthly_salary <= 4250) {
        $ee = 180;
    } elseif ($monthly_salary <= 4750) {
        $ee = 202.50;
    } elseif ($monthly_salary <= 5250) {
        $ee = 225;
    } elseif ($monthly_salary <= 5750) {
        $ee = 247.50;
    } elseif ($monthly_salary <= 6250) {
        $ee = 270;
    } elseif ($monthly_salary <= 6750) {
        $ee = 292.50;
    } elseif ($monthly_salary <= 7250) {
        $ee = 315;
    } elseif ($monthly_salary <= 7750) {
        $ee = 337.50;
    } elseif ($monthly_salary <= 8250) {
        $ee = 360;
    } elseif ($monthly_salary <= 8750) {
        $ee = 382.50;
    } elseif ($monthly_salary <= 9250) {
        $ee = 405;
    } elseif ($monthly_salary <= 9750) {
        $ee = 427.50;
    } elseif ($monthly_salary <= 10250) {
        $ee = 450;
    } elseif ($monthly_salary <= 10750) {
        $ee = 472.50;
    } elseif ($monthly_salary <= 11250) {
        $ee = 495;
    } elseif ($monthly_salary <= 11750) {
        $ee = 517.50;
    } elseif ($monthly_salary <= 12250) {
        $ee = 540;
    } elseif ($monthly_salary <= 12750) {
        $ee = 562.50;
    } elseif ($monthly_salary <= 13250) {
        $ee = 585;
    } elseif ($monthly_salary <= 13750) {
        $ee = 607.50;
    } elseif ($monthly_salary <= 14250) {
        $ee = 630;
    } elseif ($monthly_salary <= 14750) {
        $ee = 652.50;
    } elseif ($monthly_salary <= 15250) {
        $ee = 675;
    } elseif ($monthly_salary <= 15750) {
        $ee = 697.50;
    } elseif ($monthly_salary <= 16250) {
        $ee = 720;
    } elseif ($monthly_salary <= 16750) {
        $ee = 742.50;
    } elseif ($monthly_salary <= 17250) {
        $ee = 765;
    } elseif ($monthly_salary <= 17750) {
        $ee = 787.50;
    } elseif ($monthly_salary <= 18250) {
        $ee = 810;
    } elseif ($monthly_salary <= 18750) {
        $ee = 832.50;
    } elseif ($monthly_salary <= 19250) {
        $ee = 855;
    } elseif ($monthly_salary <= 19750) {
        $ee = 877.50;
    } elseif ($monthly_salary <= 20250) {
        $ee = 900;
    } elseif ($monthly_salary <= 20750) {
        $ee = 922.50;
    } elseif ($monthly_salary <= 21250) {
        $ee = 945;
    } elseif ($monthly_salary <= 21750) {
        $ee = 967.50;
    } elseif ($monthly_salary <= 22250) {
        $ee = 990;
    } elseif ($monthly_salary <= 22750) {
        $ee = 1012.50;
    } elseif ($monthly_salary <= 23250) {
        $ee = 1035;
    } elseif ($monthly_salary <= 23750) {
        $ee = 1057.50;
    } elseif ($monthly_salary <= 24250) {
        $ee = 1080;
    } elseif ($monthly_salary <= 24750) {
        $ee = 1102.50;
    } elseif ($monthly_salary <= 25250) {
        $ee = 1125;
    } elseif ($monthly_salary <= 25750) {
        $ee = 1147.50;
    } elseif ($monthly_salary <= 26250) {
        $ee = 1170;
    } elseif ($monthly_salary <= 26750) {
        $ee = 1192.50;
    } elseif ($monthly_salary <= 27250) {
        $ee = 1215;
    } elseif ($monthly_salary <= 27750) {
        $ee = 1237.50;
    } elseif ($monthly_salary <= 28250) {
        $ee = 1260;
    } elseif ($monthly_salary <= 28750) {
        $ee = 1282.50;
    } elseif ($monthly_salary <= 29250) {
        $ee = 1305;
    } elseif ($monthly_salary <= 29750) {
        $ee = 1327.50;
    } elseif ($monthly_salary <= 30250) {
        $ee = 1350;
    } elseif ($monthly_salary <= 30750) {
        $ee = 1372.50;
    } elseif ($monthly_salary <= 31250) {
        $ee = 1395;
    } elseif ($monthly_salary <= 31750) {
        $ee = 1417.50;
    } elseif ($monthly_salary <= 32250) {
        $ee = 1440;
    } elseif ($monthly_salary <= 32750) {
        $ee = 1462.50;
    } else {
        $ee = 1485;
    }
    
    return $ee;
}

function get_philhealth_contribution($monthly_salary) {
    if ($monthly_salary <= 0) return 0;
    
    if ($monthly_salary >= 100000) {
        $premium = 4500;
    } else {
        $premium = $monthly_salary * 0.045;
    }
    
    return round($premium / 2, 2);
}

function get_pagibig_contribution($monthly_salary) {
    if ($monthly_salary <= 0) return 0;
    
    if ($monthly_salary <= 1500) {
        return $monthly_salary * 0.01;
    } else {
        $contribution = min($monthly_salary * 0.02, 100);
    }
    
    return round($contribution, 2);
}

function calculate_monthly_deductions($monthly_salary) {
    $sss = get_sss_contribution($monthly_salary);
    $philhealth = get_philhealth_contribution($monthly_salary);
    $pagibig = get_pagibig_contribution($monthly_salary);
    
    $annual_taxable = ($monthly_salary - $sss - $philhealth - $pagibig) * 12;
    $annual_tax = calculate_philippine_tax($annual_taxable);
    $monthly_tax = round($annual_tax / 12, 2);
    
    return [
        'sss' => $sss,
        'philhealth' => $philhealth,
        'pagibig' => $pagibig,
        'withholding_tax' => $monthly_tax,
        'total_deductions' => $sss + $philhealth + $pagibig + $monthly_tax
    ];
}

function calculate_employee_payroll($monthly_gross, $daily_rate) {
    $deductions = calculate_monthly_deductions($monthly_gross);
    
    $net_pay = $monthly_gross - $deductions['total_deductions'];
    
    return [
        'gross_pay' => $monthly_gross,
        'sss' => $deductions['sss'],
        'philhealth' => $deductions['philhealth'],
        'pagibig' => $deductions['pagibig'],
        'withholding_tax' => $deductions['withholding_tax'],
        'total_deductions' => $deductions['total_deductions'],
        'net_pay' => $net_pay
    ];
}

function compute_overtime_pay($ot_hours, $ot_rate) {
    return round($ot_hours * $ot_rate, 2);
}

function compute_late_deduction($late_hours, $hourly_rate) {
    return round($late_hours * $hourly_rate, 2);
}

function compute_undertime_deduction($undertime_hours, $hourly_rate) {
    return round($undertime_hours * $hourly_rate, 2);
}

function compute_absence_deduction($days_absent, $daily_rate) {
    return round($days_absent * $daily_rate, 2);
}

function compute_monthly_gross_from_hours($total_hours, $daily_rate, $ot_rate) {
    $regular_hours = min($total_hours, HOURS_PER_DAY);
    $ot_hours = max(0, $total_hours - HOURS_PER_DAY);
    
    $regular_pay = ($regular_hours / HOURS_PER_DAY) * $daily_rate;
    $ot_pay = $ot_hours * $ot_rate;
    
    return [
        'regular_hours' => $regular_hours,
        'ot_hours' => $ot_hours,
        'regular_pay' => $regular_pay,
        'ot_pay' => $ot_pay,
        'total_gross' => $regular_pay + $ot_pay
    ];
}

function save_payroll_record($dbh, $data) {
    $sql = "INSERT INTO payroll (
        empid, basic_salary, allowances, overtime_pay, gross_pay,
        sss_deduction, philhealth_deduction, pagibig_deduction, tax_deduction,
        late_deduction, undertime_deduction, absence_deduction, other_deduction,
        total_deductions, net_pay, total_hours, ot_hours, regular_hours,
        period_month, period_year, processed_by
    ) VALUES (
        :empid, :basic_salary, :allowances, :overtime_pay, :gross_pay,
        :sss, :philhealth, :pagibig, :tax,
        :late, :undertime, :absence, :other,
        :total_deductions, :net_pay, :total_hours, :ot_hours, :regular_hours,
        :period_month, :period_year, :processed_by
    ) ON DUPLICATE KEY UPDATE
        basic_salary = VALUES(basic_salary),
        allowances = VALUES(allowances),
        overtime_pay = VALUES(overtime_pay),
        gross_pay = VALUES(gross_pay),
        sss_deduction = VALUES(sss_deduction),
        philhealth_deduction = VALUES(philhealth_deduction),
        pagibig_deduction = VALUES(pagibig_deduction),
        tax_deduction = VALUES(tax_deduction),
        late_deduction = VALUES(late_deduction),
        undertime_deduction = VALUES(undertime_deduction),
        absence_deduction = VALUES(absence_deduction),
        other_deduction = VALUES(other_deduction),
        total_deductions = VALUES(total_deductions),
        net_pay = VALUES(net_pay),
        total_hours = VALUES(total_hours),
        ot_hours = VALUES(ot_hours),
        regular_hours = VALUES(regular_hours),
        processed_by = VALUES(processed_by),
        created_at = CURRENT_TIMESTAMP";
    
    $query = $dbh->prepare($sql);
    $query->bindParam(':empid', $data['empid'], PDO::PARAM_STR);
    $query->bindParam(':basic_salary', $data['basic_salary'], PDO::PARAM_STR);
    $query->bindParam(':allowances', $data['allowances'], PDO::PARAM_STR);
    $query->bindParam(':overtime_pay', $data['overtime_pay'], PDO::PARAM_STR);
    $query->bindParam(':gross_pay', $data['gross_pay'], PDO::PARAM_STR);
    $query->bindParam(':sss', $data['sss'], PDO::PARAM_STR);
    $query->bindParam(':philhealth', $data['philhealth'], PDO::PARAM_STR);
    $query->bindParam(':pagibig', $data['pagibig'], PDO::PARAM_STR);
    $query->bindParam(':tax', $data['tax'], PDO::PARAM_STR);
    $query->bindParam(':late', $data['late'], PDO::PARAM_STR);
    $query->bindParam(':undertime', $data['undertime'], PDO::PARAM_STR);
    $query->bindParam(':absence', $data['absence'], PDO::PARAM_STR);
    $query->bindParam(':other', $data['other'], PDO::PARAM_STR);
    $query->bindParam(':total_deductions', $data['total_deductions'], PDO::PARAM_STR);
    $query->bindParam(':net_pay', $data['net_pay'], PDO::PARAM_STR);
    $query->bindParam(':total_hours', $data['total_hours'], PDO::PARAM_STR);
    $query->bindParam(':ot_hours', $data['ot_hours'], PDO::PARAM_STR);
    $query->bindParam(':regular_hours', $data['regular_hours'], PDO::PARAM_STR);
    $query->bindParam(':period_month', $data['period_month'], PDO::PARAM_INT);
    $query->bindParam(':period_year', $data['period_year'], PDO::PARAM_INT);
    $query->bindParam(':processed_by', $data['processed_by'], PDO::PARAM_INT);
    
    return $query->execute();
}

function get_payroll_history($dbh, $empid = null, $month = null, $year = null, $limit = 100) {
    $sql = "SELECT p.*, CONCAT(e.FirstName, ' ', e.LastName) as emp_name, e.Department
            FROM payroll p
            LEFT JOIN tblemployees e ON p.empid = e.EmpId
            WHERE 1=1";
    $params = [];
    
    if($empid) {
        $sql .= " AND p.empid = :empid";
        $params[':empid'] = $empid;
    }
    if($month) {
        $sql .= " AND p.period_month = :month";
        $params[':month'] = $month;
    }
    if($year) {
        $sql .= " AND p.period_year = :year";
        $params[':year'] = $year;
    }
    
    $sql .= " ORDER BY p.period_year DESC, p.period_month DESC LIMIT :limit";
    $params[':limit'] = $limit;
    
    $query = $dbh->prepare($sql);
    foreach($params as $key => $value) {
        if($key == ':limit') {
            $query->bindValue($key, $value, PDO::PARAM_INT);
        } else {
            $query->bindParam($key, $value);
        }
    }
    $query->execute();
    return $query->fetchAll(PDO::FETCH_OBJ);
}

function get_payroll_by_id($dbh, $id) {
    $sql = "SELECT p.*, CONCAT(e.FirstName, ' ', e.LastName) as emp_name, e.Department, e.EmailId
            FROM payroll p
            LEFT JOIN tblemployees e ON p.empid = e.EmpId
            WHERE p.id = :id";
    $query = $dbh->prepare($sql);
    $query->bindParam(':id', $id, PDO::PARAM_INT);
    $query->execute();
    return $query->fetch(PDO::FETCH_OBJ);
}

function get_payroll_summary($dbh, $month, $year) {
    $sql = "SELECT 
                COUNT(*) as total_employees,
                COALESCE(SUM(gross_pay), 0) as total_gross,
                COALESCE(SUM(total_deductions), 0) as total_deductions,
                COALESCE(SUM(net_pay), 0) as total_net,
                COALESCE(SUM(sss_deduction), 0) as total_sss,
                COALESCE(SUM(philhealth_deduction), 0) as total_philhealth,
                COALESCE(SUM(pagibig_deduction), 0) as total_pagibig,
                COALESCE(SUM(tax_deduction), 0) as total_tax
            FROM payroll 
            WHERE period_month = :month AND period_year = :year";
    $query = $dbh->prepare($sql);
    $query->bindParam(':month', $month, PDO::PARAM_INT);
    $query->bindParam(':year', $year, PDO::PARAM_INT);
    $query->execute();
    return $query->fetch(PDO::FETCH_OBJ);
}

function delete_payroll_record($dbh, $id) {
    $sql = "DELETE FROM payroll WHERE id = :id";
    $query = $dbh->prepare($sql);
    $query->bindParam(':id', $id, PDO::PARAM_INT);
    return $query->execute();
}

function generate_payslip_number($dbh, $empid, $month, $year) {
    return "PS-" . date('Ymd') . "-" . str_pad($empid, 10, '0', STR_PAD_LEFT) . "-" . str_pad($month, 2, '0', STR_PAD_LEFT) . $year;
}

function initialize_payroll_tables($dbh) {
    $dbh->exec("CREATE TABLE IF NOT EXISTS payroll (
        id INT AUTO_INCREMENT PRIMARY KEY,
        empid VARCHAR(100) NOT NULL,
        basic_salary DECIMAL(12,2) DEFAULT 0,
        allowances DECIMAL(12,2) DEFAULT 0,
        overtime_pay DECIMAL(12,2) DEFAULT 0,
        gross_pay DECIMAL(12,2) DEFAULT 0,
        sss_deduction DECIMAL(12,2) DEFAULT 0,
        philhealth_deduction DECIMAL(12,2) DEFAULT 0,
        pagibig_deduction DECIMAL(12,2) DEFAULT 0,
        tax_deduction DECIMAL(12,2) DEFAULT 0,
        late_deduction DECIMAL(12,2) DEFAULT 0,
        undertime_deduction DECIMAL(12,2) DEFAULT 0,
        absence_deduction DECIMAL(12,2) DEFAULT 0,
        other_deduction DECIMAL(12,2) DEFAULT 0,
        total_deductions DECIMAL(12,2) DEFAULT 0,
        net_pay DECIMAL(12,2) DEFAULT 0,
        total_hours DECIMAL(10,2) DEFAULT 0,
        ot_hours DECIMAL(10,2) DEFAULT 0,
        regular_hours DECIMAL(10,2) DEFAULT 0,
        period_month INT,
        period_year INT,
        processed_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_emp_period (empid, period_month, period_year)
    )");
    
    $dbh->exec("CREATE TABLE IF NOT EXISTS custom_deductions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        type ENUM('fixed', 'percentage') DEFAULT 'fixed',
        amount DECIMAL(10,2) DEFAULT 0,
        is_active TINYINT DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    $check = $dbh->query("SELECT COUNT(*) FROM custom_deductions")->fetchColumn();
    if($check == 0) {
        $dbh->exec("INSERT INTO custom_deductions (name, type, amount) VALUES 
            ('SSS Loan', 'fixed', 0),
            ('Pag-IBIG Loan', 'fixed', 0),
            ('Salary Loan', 'fixed', 0),
            ('Uniform/Deduction', 'fixed', 0)");
    }
}

function get_custom_deductions($dbh) {
    $sql = "SELECT * FROM custom_deductions WHERE is_active = 1";
    $query = $dbh->prepare($sql);
    $query->execute();
    return $query->fetchAll(PDO::FETCH_OBJ);
}

function save_custom_deduction($dbh, $data) {
    $sql = "INSERT INTO custom_deductions (name, type, amount, is_active) 
            VALUES (:name, :type, :amount, :is_active)
            ON DUPLICATE KEY UPDATE name = VALUES(name), type = VALUES(type), amount = VALUES(amount), is_active = VALUES(is_active)";
    $query = $dbh->prepare($sql);
    $query->bindParam(':name', $data['name'], PDO::PARAM_STR);
    $query->bindParam(':type', $data['type'], PDO::PARAM_STR);
    $query->bindParam(':amount', $data['amount'], PDO::PARAM_STR);
    $query->bindParam(':is_active', $data['is_active'], PDO::PARAM_INT);
    return $query->execute();
}

function delete_custom_deduction($dbh, $id) {
    $sql = "DELETE FROM custom_deductions WHERE id = :id";
    $query = $dbh->prepare($sql);
    $query->bindParam(':id', $id, PDO::PARAM_INT);
    return $query->execute();
}

function compute_full_payroll($dbh, $empid, $period_month, $period_year, $options = []) {
    $daily_rate = $options['daily_rate'] ?? DAILY_RATE;
    $ot_rate = $options['ot_rate'] ?? OT_RATE;
    $allowances = $options['allowances'] ?? 0;
    $late_deduction = $options['late_deduction'] ?? 0;
    $undertime_deduction = $options['undertime_deduction'] ?? 0;
    $absence_deduction = $options['absence_deduction'] ?? 0;
    $other_deduction = $options['other_deduction'] ?? 0;
    $processed_by = $options['processed_by'] ?? 0;
    
    $records = get_monthly_time_records($dbh, $empid, $period_month, $period_year);
    
    $total_hours = 0;
    $regular_hours = 0;
    $ot_hours = 0;
    
    foreach($records as $row) {
        $hours = floatval($row->HoursWorked);
        $total_hours += $hours;
        
        if($hours > HOURS_PER_DAY) {
            $regular_hours += HOURS_PER_DAY;
            $ot_hours += ($hours - HOURS_PER_DAY);
        } else {
            $regular_hours += $hours;
        }
    }
    
    $regular_pay = ($regular_hours / HOURS_PER_DAY) * $daily_rate;
    $overtime_pay = $ot_hours * $ot_rate;
    $basic_salary = $regular_pay;
    $gross_pay = $basic_salary + $overtime_pay + $allowances;
    
    $deductions = calculate_monthly_deductions($gross_pay);
    
    $total_deductions = $deductions['total_deductions'] + $late_deduction + $undertime_deduction + $absence_deduction + $other_deduction;
    $net_pay = $gross_pay - $total_deductions;
    
    $net_pay = max(0, $net_pay);
    
    return [
        'empid' => $empid,
        'basic_salary' => $basic_salary,
        'allowances' => $allowances,
        'overtime_pay' => $overtime_pay,
        'gross_pay' => $gross_pay,
        'sss' => $deductions['sss'],
        'philhealth' => $deductions['philhealth'],
        'pagibig' => $deductions['pagibig'],
        'tax' => $deductions['withholding_tax'],
        'late' => $late_deduction,
        'undertime' => $undertime_deduction,
        'absence' => $absence_deduction,
        'other' => $other_deduction,
        'total_deductions' => $total_deductions,
        'net_pay' => $net_pay,
        'total_hours' => $total_hours,
        'ot_hours' => $ot_hours,
        'regular_hours' => $regular_hours,
        'period_month' => $period_month,
        'period_year' => $period_year,
        'processed_by' => $processed_by
    ];
}