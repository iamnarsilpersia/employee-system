-- Deduction Rates Table
CREATE TABLE IF NOT EXISTS deduction_rates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(50) NOT NULL UNIQUE,
    rate DECIMAL(10,2) NOT NULL DEFAULT 0,
    is_percentage TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default deduction rates
INSERT INTO deduction_rates (type, rate, is_percentage) VALUES 
    ('SSS', 0, 1),
    ('PhilHealth', 0, 1),
    ('Pag-IBIG', 0, 1),
    ('Withholding Tax', 0, 1);

-- Employee Deductions Table
CREATE TABLE IF NOT EXISTS employee_deductions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empid VARCHAR(100) NOT NULL,
    sss DECIMAL(10,2) DEFAULT 0,
    philhealth DECIMAL(10,2) DEFAULT 0,
    pagibig DECIMAL(10,2) DEFAULT 0,
    withholding_tax DECIMAL(10,2) DEFAULT 0,
    period_month INT,
    period_year INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_emp_period (empid, period_month, period_year)
);

-- Payroll Table
CREATE TABLE IF NOT EXISTS payroll (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empid VARCHAR(100) NOT NULL,
    basic_salary DECIMAL(10,2) DEFAULT 0,
    allowances DECIMAL(10,2) DEFAULT 0,
    gross_pay DECIMAL(10,2) DEFAULT 0,
    sss_deduction DECIMAL(10,2) DEFAULT 0,
    philhealth_deduction DECIMAL(10,2) DEFAULT 0,
    pagibig_deduction DECIMAL(10,2) DEFAULT 0,
    tax_deduction DECIMAL(10,2) DEFAULT 0,
    total_deductions DECIMAL(10,2) DEFAULT 0,
    net_pay DECIMAL(10,2) DEFAULT 0,
    period_month INT,
    period_year INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_emp_period_pay (empid, period_month, period_year)
);

-- Add basic_salary column to employees table if not exists
-- ALTER TABLE tblemployees ADD COLUMN basic_salary DECIMAL(10,2) DEFAULT 0;