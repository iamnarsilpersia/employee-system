<?php
session_start();
error_reporting(0);
include('../includes/dbconn.php');
include('../includes/functions.php');
include('../includes/config.php');

if(strlen($_SESSION['emplogin'])==0){   
    header('location:../index.php');
    exit;
}

$eid = $_SESSION['eid'];
$empid = $_SESSION['emplogin'];

// Month filter
$month = isset($_GET['month']) ? intval($_GET['month']) : date('m');
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

$from = date("Y-m-01", strtotime("$year-$month-01"));
$to = date("Y-m-t", strtotime($from));
?>
<?php $page='payslip'; include('../includes/employee-header.php'); ?>

    <div class="main-content-inner">
        <div class="row">
            <div class="col-12 mt-5">
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title">My Payslip - <?php echo date('F Y', strtotime("$year-$month-01")); ?></h4>
                        
                        <form method="GET" class="form-inline mb-4">
                            <select name="month" class="form-control mr-2">
                                <?php for($m=1; $m<=12; $m++){ ?>
                                    <option value="<?php echo $m; ?>" <?php echo ($m==$month)?'selected':''; ?>>
                                        <?php echo date('F', mktime(0,0,0,$m,1)); ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <select name="year" class="form-control mr-2">
                                <?php for($y=date('Y')-2; $y<=date('Y'); $y++){ ?>
                                    <option value="<?php echo $y; ?>" <?php echo ($y==$year)?'selected':''; ?>><?php echo $y; ?></option>
                                <?php } ?>
                            </select>
                            <button type="submit" class="btn btn-primary">View</button>
                        </form>
                        
                        <?php
                        $sql = "SELECT * FROM tbltime_logs WHERE EmpID=:empid AND DateWorked BETWEEN :f AND :t ORDER BY DateWorked";
                        $query = $dbh->prepare($sql);
                        $query->bindParam(':empid', $empid, PDO::PARAM_STR);
                        $query->bindParam(':f', $from);
                        $query->bindParam(':t', $to);
                        $query->execute();
                        $records = $query->fetchAll(PDO::FETCH_OBJ);
                        
                        $total_hours = 0;
                        $total_pay = 0;
                        
                        // Get deduction rates
                        $deductions = $dbh->query("SELECT * FROM deduction_rates")->fetchAll(PDO::FETCH_OBJ);
                        $deduction_rates = [];
                        foreach($deductions as $d){
                            $deduction_rates[$d->type] = ['rate' => $d->rate, 'is_percentage' => $d->is_percentage];
                        }
                        ?>
                        
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th>Date</th>
                                        <th>Time In</th>
                                        <th>Time Out</th>
                                        <th>Hours Worked</th>
                                        <th>Daily Pay</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($records as $row){ 
                                        $pay = ($row->HoursWorked * DAILY_RATE) / 8;
                                        $total_hours += $row->HoursWorked;
                                        $total_pay += $pay;
                                    ?>
                                        <tr>
                                            <td><?php echo date('M d, Y', strtotime($row->DateWorked)); ?></td>
                                            <td><?php echo $row->TimeIn ? date('h:i A', strtotime($row->TimeIn)) : '-'; ?></td>
                                            <td><?php echo $row->TimeOut ? date('h:i A', strtotime($row->TimeOut)) : '-'; ?></td>
                                            <td><?php echo round($row->HoursWorked, 2); ?> hrs</td>
                                            <td>₱<?php echo number_format($pay, 2); ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                                <tfoot>
                                    <tr class="bg-light">
                                        <th colspan="3">Total</th>
                                        <th><?php echo round($total_hours, 2); ?> hrs</th>
                                        <th>₱<?php echo number_format($total_pay, 2); ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        <?php
                        // Calculate deductions
                        $sss = ($deduction_rates['SSS']['is_percentage'] ? $total_pay * ($deduction_rates['SSS']['rate']/100) : $deduction_rates['SSS']['rate']);
                        $philhealth = ($deduction_rates['PhilHealth']['is_percentage'] ? $total_pay * ($deduction_rates['PhilHealth']['rate']/100) : $deduction_rates['PhilHealth']['rate']);
                        $pagibig = ($deduction_rates['Pag-IBIG']['is_percentage'] ? $total_pay * ($deduction_rates['Pag-IBIG']['rate']/100) : $deduction_rates['Pag-IBIG']['rate']);
                        $tax = ($deduction_rates['Withholding Tax']['is_percentage'] ? $total_pay * ($deduction_rates['Withholding Tax']['rate']/100) : $deduction_rates['Withholding Tax']['rate']);
                        $total_deductions = $sss + $philhealth + $pagibig + $tax;
                        $net_pay = $total_pay - $total_deductions;
                        ?>
                        
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <h5>Deductions</h5>
                                <table class="table table-sm">
                                    <tr><td>SSS</td><td>₱<?php echo number_format($sss, 2); ?></td></tr>
                                    <tr><td>PhilHealth</td><td>₱<?php echo number_format($philhealth, 2); ?></td></tr>
                                    <tr><td>Pag-IBIG</td><td>₱<?php echo number_format($pagibig, 2); ?></td></tr>
                                    <tr><td>Withholding Tax</td><td>₱<?php echo number_format($tax, 2); ?></td></tr>
                                    <tr class="bg-light"><th>Total Deductions</th><th>₱<?php echo number_format($total_deductions, 2); ?></th></tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <h3>Net Pay: ₱<?php echo number_format($net_pay, 2); ?></h3>
                                        <p class="mb-0">For <?php echo date('F Y', strtotime("$year-$month-01")); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include('../includes/footer.php'); ?>
