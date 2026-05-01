<?php
session_start();
include('../includes/dbconn.php');
include('../includes/functions.php');

if(strlen($_SESSION['alogin'])==0){   
    header('location:../index.php');
    exit();
}

$msg = "";
$error = "";

// Create tables if not exist
try {
    $dbh->exec("CREATE TABLE IF NOT EXISTS deduction_rates (
        id INT AUTO_INCREMENT PRIMARY KEY,
        type VARCHAR(50) NOT NULL UNIQUE,
        rate DECIMAL(10,2) NOT NULL DEFAULT 0,
        is_percentage TINYINT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Insert default values if empty
    $check = $dbh->query("SELECT COUNT(*) FROM deduction_rates");
    if($check->fetchColumn() == 0) {
        $dbh->exec("INSERT INTO deduction_rates (type, rate, is_percentage) VALUES 
            ('SSS', 0, 1),
            ('PhilHealth', 0, 1),
            ('Pag-IBIG', 0, 1),
            ('Withholding Tax', 0, 1)");
    }
} catch(PDOException $e) {
    // Tables may already exist
}

// Update deduction rates
if(isset($_POST['update_rates'])){
    $sss_rate = floatval($_POST['sss_rate']);
    $philhealth_rate = floatval($_POST['philhealth_rate']);
    $pagibig_rate = floatval($_POST['pagibig_rate']);
    $tax_rate = floatval($_POST['tax_rate']);
    
    try {
        $dbh->beginTransaction();
        
        $stmt = $dbh->prepare("UPDATE deduction_rates SET rate=? WHERE type='SSS'");
        $stmt->execute([$sss_rate]);
        
        $stmt = $dbh->prepare("UPDATE deduction_rates SET rate=? WHERE type='PhilHealth'");
        $stmt->execute([$philhealth_rate]);
        
        $stmt = $dbh->prepare("UPDATE deduction_rates SET rate=? WHERE type='Pag-IBIG'");
        $stmt->execute([$pagibig_rate]);
        
        $stmt = $dbh->prepare("UPDATE deduction_rates SET rate=? WHERE type='Withholding Tax'");
        $stmt->execute([$tax_rate]);
        
        $dbh->commit();
        $msg = "Deduction rates updated successfully!";
    } catch(Exception $e) {
        $dbh->rollBack();
        $error = "Error updating rates: " . $e->getMessage();
    }
}

// Load current rates
$sql = "SELECT * FROM deduction_rates";
$query = $dbh->prepare($sql);
$query->execute();
$rates = [];
while($row = $query->fetch(PDO::FETCH_OBJ)){
    $rates[$row->type] = $row->rate;
}

$page='payroll'; 
include('../includes/admin-header.php'); 
?>

    <div class="page-title-area">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <div class="breadcrumbs-area clearfix">
                    <h4 class="page-title pull-left">Deductions Settings</h4>
                    <ul class="breadcrumbs pull-left">
                        <li><a href="dashboard.php">Home</a></li>
                        <li><span>Deductions</span></li>
                    </ul>
                </div>
            </div>
            
            <div class="col-sm-6 clearfix">
                <?php include '../includes/admin-profile-section.php'; ?>
            </div>
        </div>
    </div>

    <div class="main-content-inner">
        <div class="row">
            <div class="col-lg-6 col-ml-12">
                <div class="card">
                    <div class="card-body">
                        <?php if($error){?><div class="alert alert-danger alert-dismissible fade show"><strong>Error: </strong><?php echo htmlentities($error); ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                         </div><?php } 
                         else if($msg){?><div class="alert alert-success alert-dismissible fade show"><strong>Success: </strong><?php echo htmlentities($msg); ?> 
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                         </div><?php }?>

                        <form method="POST">
                            <div class="form-group">
                                <label>SSS Rate (%)</label>
                                <input type="number" step="0.01" name="sss_rate" value="<?php echo isset($rates['SSS']) ? $rates['SSS'] : 0; ?>" class="form-control">
                            </div>

                            <div class="form-group">
                                <label>PhilHealth Rate (%)</label>
                                <input type="number" step="0.01" name="philhealth_rate" value="<?php echo isset($rates['PhilHealth']) ? $rates['PhilHealth'] : 0; ?>" class="form-control">
                            </div>

                            <div class="form-group">
                                <label>Pag-IBIG Rate (%)</label>
                                <input type="number" step="0.01" name="pagibig_rate" value="<?php echo isset($rates['Pag-IBIG']) ? $rates['Pag-IBIG'] : 0; ?>" class="form-control">
                            </div>

                            <div class="form-group">
                                <label>Withholding Tax Rate (%)</label>
                                <input type="number" step="0.01" name="tax_rate" value="<?php echo isset($rates['Withholding Tax']) ? $rates['Withholding Tax'] : 0; ?>" class="form-control">
                            </div>

                            <button class="btn btn-primary" name="update_rates" type="submit">UPDATE RATES</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include '../includes/admin-footer.php'; ?>
