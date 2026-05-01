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
<?php $page='attendance-record'; include('../includes/employee-header.php'); ?>

    <div class="main-content-inner">
        <div class="row">
            <div class="col-12 mt-5">
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title">Attendance Record - <?php echo date('F Y', strtotime("$year-$month-01")); ?></h4>
                        
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
                            <button type="submit" class="btn btn-primary">Filter</button>
                        </form>
                        
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th>Date</th>
                                        <th>Time In</th>
                                        <th>Time Out</th>
                                        <th>Hours Worked</th>
                                        <th>Status</th>
                                        <th>Daily Pay</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql = "SELECT * FROM tbltime_logs WHERE EmpID=:empid AND DateWorked BETWEEN :f AND :t ORDER BY DateWorked DESC";
                                    $query = $dbh->prepare($sql);
                                    $query->bindParam(':empid', $empid, PDO::PARAM_STR);
                                    $query->bindParam(':f', $from);
                                    $query->bindParam(':t', $to);
                                    $query->execute();
                                    $records = $query->fetchAll(PDO::FETCH_OBJ);
                                    
                                    $total_hours = 0;
                                    $total_pay = 0;
                                    
                                    if(count($records) > 0){
                                        foreach($records as $row){
                                            $pay = ($row->HoursWorked * DAILY_RATE) / 8;
                                            $total_hours += $row->HoursWorked;
                                            $total_pay += $pay;
                                            ?>
                                            <tr>
                                                <td><?php echo date('M d, Y', strtotime($row->DateWorked)); ?></td>
                                                <td><?php echo $row->TimeIn ? date('h:i A', strtotime($row->TimeIn)) : '-'; ?></td>
                                                <td><?php echo $row->TimeOut ? date('h:i A', strtotime($row->TimeOut)) : '-'; ?></td>
                                                <td><?php echo round($row->HoursWorked, 2); ?> hrs</td>
                                                <td>
                                                    <?php if($row->Status == 'Approved'){ ?>
                                                        <span class="badge badge-success">Approved</span>
                                                    <?php } elseif($row->Status == 'Pending'){ ?>
                                                        <span class="badge badge-warning">Pending</span>
                                                    <?php } else { ?>
                                                        <span class="badge badge-danger"><?php echo $row->Status; ?></span>
                                                    <?php } ?>
                                                </td>
                                                <td>₱<?php echo number_format($pay, 2); ?></td>
                                            </tr>
                                        <?php }
                                    } else {
                                        echo '<tr><td colspan="6" class="text-center">No attendance records found.</td></tr>';
                                    }
                                    ?>
                                </tbody>
                                <tfoot>
                                    <tr class="bg-light">
                                        <th colspan="3">Total</th>
                                        <th><?php echo round($total_hours, 2); ?> hrs</th>
                                        <th colspan="2">₱<?php echo number_format($total_pay, 2); ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include('../includes/footer.php'); ?>
