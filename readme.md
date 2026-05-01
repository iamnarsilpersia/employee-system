# Employee Management System

A PHP-based Employee Management System with Admin and Employee portals, built for Philippine companies. Features employee management, leave management, attendance tracking, and payroll computation.

## Features

### Admin Panel
- **Employee Management**
  - Add/Edit/Delete employees
  - Activate/Deactivate employee accounts
  - Change employee passwords
  - View employee documents

- **Leave Management**
  - Approve/Reject leave applications
  - Manage leave types
  - View leave history

- **Attendance & Payroll**
  - Track employee time in/out
  - Monthly payroll calculation
  - Daily rate and overtime rate settings
  - Deduction rates management (SSS, PhilHealth, Pag-IBIG, Withholding Tax)

- **Department Management**
  - Add/Edit/Delete departments

- **Admin Management**
  - Add/Remove admin accounts

### Employee Portal
- **Profile Management**
  - Update personal information
  - Change password

- **Leave Application**
  - Apply for leaves online
  - View leave history and status

- **Time Tracking**
  - Time in/out system
  - View attendance records
  - Monthly salary computation

## Technology Stack

- **Backend:** PHP (PDO)
- **Database:** MySQL (via XAMPP)
- **Frontend:** HTML5, CSS3, Bootstrap 4, jQuery
- **Libraries:** DataTables, Chart.js, ZingChart

## Installation

### Prerequisites
- XAMPP (Apache + MySQL + PHP)
- PHP 7.4 or higher

### Setup Instructions

1. **Place files in XAMPP htdocs:**
   ```
   C:\xampp\htdocs\Employee_Management_System\
   ```

2. **Start XAMPP:**
   - Open XAMPP Control Panel
   - Start Apache and MySQL services

3. **Create Database:**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create database: `employeeleavedb`
   - Import SQL file: `DATABASE FILE/employeeleavedb.sql`

4. **Configure Database Connection:**
   Edit `includes/dbconn.php`:
   ```php
   $servername = "localhost";
   $dbusername = "root";
   $dbpassword = "";
   $dbname = "employeeleavedb";
   ```

5. **Access the System:**
   - Main Login: http://localhost/Employee_Management_System/
   - Admin: Username: `admin`, Password: `Test@123`
   - Employee: Use Employee ID (e.g., `EMP001`) and password

## File Structure

```
Employee_Management_System/
├── admin/                    # Admin portal files
│   ├── add-admin.php
│   ├── add-department.php
│   ├── add-employee.php
│   ├── add-leavetype.php
│   ├── dashboard.php        # Payroll & attendance
│   ├── employees.php        # Employee list
│   ├── leave-section.php     # Leave types
│   ├── department.php
│   ├── docs.php
│   ├── attendance_history.php
│   └── ...
├── employees/               # Employee portal files
│   ├── leaves.php           # Main dashboard with attendance
│   ├── leave.php            # Leave application
│   ├── leave-history.php
│   ├── my-profile.php
│   ├── time.php             # Time tracking
│   ├── time-in-out.php
│   └── ...
├── includes/                # Shared components
│   ├── dbconn.php          # Database connection
│   ├── config.php          # Config constants
│   ├── functions.php       # Reusable functions
│   ├── admin-header.php    # Admin template header
│   ├── admin-footer.php    # Admin template footer
│   ├── employee-header.php # Employee template header
│   ├── employee-footer.php # Employee template footer
│   └── ...
├── assets/                  # CSS, JS, images
├── uploads/                 # Employee documents
├── index.php                # Main login page
└── DATABASE FILE/           # SQL files
```

## Database Tables

- `admin` - Admin accounts
- `tblemployees` - Employee records
- `tblleaves` - Leave applications
- `tblleavetype` - Leave types
- `tbldepartments` - Department list
- `attendance` - Time in/out records
- `tbltime_logs` - Employee time logs
- `tbldocuments` - Uploaded documents
- `tblnotifications` - System notifications
- `settings` - Payroll settings
- `deduction_rates` - Philippine tax/statutory rates

## Configuration

### Payroll Settings (`includes/config.php`)
```php
define("DAILY_RATE", 500);     // Daily rate per 8 hours
define("OT_RATE", 50);          // Overtime rate per hour
define("HOURS_PER_DAY", 8);    // Standard working hours
```

### Session Variables
- `$_SESSION['alogin']` - Admin username
- `$_SESSION['emplogin']` - Employee ID
- `$_SESSION['eid']` - Employee database ID

## Security Notes

⚠️ **Current Security Status:**
- Passwords use MD5 hashing (upgrade to `password_hash()` recommended)
- No CSRF protection (add tokens)
- No rate limiting on login attempts
- Input validation needed on file uploads

## Credits

Developed for Employee Management System with PHP/XAMPP.
Organized and refactored in 2026.

## License

MIT License - Free to use and modify.
