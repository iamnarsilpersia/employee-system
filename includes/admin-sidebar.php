<nav>
    <ul class="metismenu" id="menu">
        <li class="<?php if($page=='dashboard') {echo 'active';} ?>"><a href="dashboard.php"><i class="ti-dashboard"></i> <span>Dashboard</span></a></li>
        <li class="<?php if($page=='payroll') {echo 'active';} ?>">
            <a href="javascript:void(0)" aria-expanded="true"><i class="ti-money"></i><span>Payroll</span></a>
            <ul class="collapse">
                <li><a href="process-payroll.php"><i class="ti-calendar"></i> Process Payroll</a></li>
                <li><a href="payroll-history.php"><i class="ti-list"></i> Payroll History</a></li>
                <li><a href="payroll-settings.php"><i class="ti-settings"></i> Payroll Settings</a></li>
                <li><a href="deductions.php"><i class="ti-cut"></i> Deductions Info</a></li>
            </ul>
        </li>
        <li class="<?php if($page=='employee') {echo 'active';} ?>"><a href="employees.php"><i class="ti-id-badge"></i> <span>Employee Section</span></a></li>
        <li class="<?php if($page=='leave') {echo 'active';} ?>"><a href="leave-section.php"><i class="fa fa-sign-out"></i> <span>Leave Type</span></a></li>
        <li class="<?php if($page=='department') {echo 'active';} ?>"><a href="department.php"><i class="fa fa-th-large"></i> <span>Department Section</span></a></li>
        <li class="<?php if($page=='manage-leave') {echo 'active';} ?>">
            <a href="javascript:void(0)" aria-expanded="true"><i class="ti-briefcase"></i><span>Manage Leave</span></a>
            <ul class="collapse">
                <li><a href="pending-history.php"><i class="fa fa-spinner"></i> Pending</a></li>
                <li><a href="approved-history.php"><i class="fa fa-check"></i> Approved</a></li>
                <li><a href="declined-history.php"><i class="fa fa-times-circle"></i> Declined</a></li>
                <li><a href="leave-history.php"><i class="fa fa-history"></i> Leave History</a></li>
            </ul>
        </li>
        <li class="<?php if($page=='manage-documents') {echo 'active';} ?>"><a href="docs.php"><i class="fa fa-file"></i> <span>Manage Documents</span></a></li>
        <li class="<?php if($page=='manage-admin') {echo 'active';} ?>"><a href="manage-admin.php"><i class="fa fa-lock"></i> <span>Manage Admin</span></a></li>
    </ul>
</nav>