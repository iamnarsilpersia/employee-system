<!-- Employee Sidebar -->
<?php
$current_page = basename($_SERVER['PHP_SELF']);
$payroll_pages = ['payroll.php', 'payslip.php', 'time.php', 'leaves.php', 'monthly-salary.php', 'attendance-record.php'];
$leave_pages = ['leave.php', 'leave-history.php'];
?>
<ul class="metismenu" id="menu">
    <li class="<?php echo in_array($current_page, $payroll_pages) ? 'active' : ''; ?>">
        <a href="payroll.php"><i class="ti-money"></i> <span>Payroll</span></a>
    </li>
    
    <li class="<?php echo in_array($current_page, $leave_pages) ? 'active' : ''; ?>">
        <a href="leave.php"><i class="ti-calendar"></i> <span>Leave</span></a>
    </li>
    
    <li class="<?php echo ($current_page == 'upload-document.php') ? 'active' : ''; ?>">
        <a href="upload-document.php"><i class="ti-folder"></i> <span>Documents</span></a>
    </li>
    
    <li class="<?php echo ($current_page == 'my-profile.php') ? 'active' : ''; ?>">
        <a href="my-profile.php"><i class="ti-user"></i> <span>My Profile</span></a>
    </li>
    
    <li>
        <a href="logout.php"><i class="ti-power-off"></i> <span>Logout</span></a>
    </li>
</ul>
