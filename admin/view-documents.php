<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('../includes/dbconn.php');
include('../includes/functions.php');

if(strlen($_SESSION['alogin'])==0){   
    header('location:../index.php');
    exit;
}

if(!isset($_GET['empid']) || empty($_GET['empid'])) {
    header('location:docs.php');
    exit;
}

$empid = intval($_GET['empid']);

// Handle status update
if(isset($_POST['update_status'])) {
    $doc_id = intval($_POST['doc_id']);
    $new_status = sanitize_input($_POST['status']);
    
    $sql = "UPDATE tbl_employee_documents SET status=:status, admin_reviewed=1, admin_reviewed_on=NOW() WHERE id=:id";
    $query = $dbh->prepare($sql);
    $query->bindParam(':status', $new_status, PDO::PARAM_STR);
    $query->bindParam(':id', $doc_id, PDO::PARAM_INT);
    $query->execute();
    
    header('location:?empid=' . $empid);
    exit;
}

// Get employee info
$sql = "SELECT * FROM tblemployees WHERE id=:empid";
$query = $dbh->prepare($sql);
$query->bindParam(':empid', $empid, PDO::PARAM_INT);
$query->execute();
$employee = $query->fetch(PDO::FETCH_OBJ);

if(!$employee) {
    header('location:docs.php');
    exit;
}

// Create document tables if not exist
$dbh->exec("CREATE TABLE IF NOT EXISTS tbl_document_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

try {
    $dbh->exec("ALTER TABLE tbl_employee_documents ADD COLUMN IF NOT EXISTS status VARCHAR(20) DEFAULT 'Pending'");
    $dbh->exec("ALTER TABLE tbl_employee_documents ADD COLUMN IF NOT EXISTS admin_reviewed TINYINT DEFAULT 0");
    $dbh->exec("ALTER TABLE tbl_employee_documents ADD COLUMN IF NOT EXISTS admin_reviewed_on DATETIME DEFAULT NULL");
} catch (Exception $e) {}

// Insert default document types if empty
$check = $dbh->query("SELECT COUNT(*) FROM tbl_document_types")->fetchColumn();
if($check == 0) {
    $dbh->exec("INSERT INTO tbl_document_types (type_name) VALUES 
        ('Resume/CV'), ('Diploma'), ('Birth Certificate'), ('Government ID'), ('Tax Documents'), ('Other')");
}

// Get employee documents
$sql = "SELECT d.*, t.type_name FROM tbl_employee_documents d 
        LEFT JOIN tbl_document_types t ON d.doc_type_id = t.id 
        WHERE d.emp_id=:empid ORDER BY d.uploaded_on DESC";
$query = $dbh->prepare($sql);
$query->bindParam(':empid', $empid, PDO::PARAM_INT);
$query->execute();
$docs = $query->fetchAll(PDO::FETCH_OBJ);

$page='docs';
include('../includes/admin-header.php');
?>

<div class="main-content-inner">
    <div class="row">
        <div class="col-12 mt-5">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title">Documents - <?php echo htmlentities($employee->FirstName . ' ' . $employee->LastName); ?> (ID: <?php echo htmlentities($employee->EmpId); ?>)</h4>
                    
                    <a href="docs.php" class="btn btn-secondary mb-3"><i class="ti-arrow-left"></i> Back to List</a>
                    
                    <?php if(count($docs) > 0){ ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th>Document Type</th>
                                        <th>File Name</th>
                                        <th>Description</th>
                                        <th>Uploaded On</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($docs as $doc){ ?>
                                        <tr>
                                            <td><?php echo htmlentities($doc->type_name); ?></td>
                                            <td><?php echo htmlentities($doc->original_name); ?></td>
                                            <td><?php echo htmlentities($doc->description); ?></td>
                                            <td><?php echo date('M d, Y h:i A', strtotime($doc->uploaded_on)); ?></td>
                                            <td>
                                                <?php 
                                                $status = $doc->status ?? 'Pending';
                                                if($status == 'Approved') {
                                                    echo '<span class="badge badge-pill badge-success">Received</span>';
                                                } elseif($status == 'Rejected') {
                                                    echo '<span class="badge badge-pill badge-danger">Rejected</span>';
                                                } else {
                                                    echo '<span class="badge badge-pill badge-warning">Pending</span>';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <a href="../uploads/documents/<?php echo $doc->file_name; ?>" target="_blank" class="btn btn-sm btn-info">View</a>
                                                <?php if($status == 'Pending') { ?>
                                                <div class="btn-group ml-2">
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="doc_id" value="<?php echo $doc->id; ?>">
                                                        <input type="hidden" name="status" value="Approved">
                                                        <button type="submit" name="update_status" class="btn btn-sm btn-success" title="Mark as Received">
                                                            <i class="fa fa-check"></i>
                                                        </button>
                                                    </form>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="doc_id" value="<?php echo $doc->id; ?>">
                                                        <input type="hidden" name="status" value="Rejected">
                                                        <button type="submit" name="update_status" class="btn btn-sm btn-danger" title="Reject">
                                                            <i class="fa fa-times"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    <?php } else { ?>
                        <div class="text-center py-5">
                            <i class="ti-folder fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">No documents uploaded yet</h5>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/admin-footer.php'; ?>
