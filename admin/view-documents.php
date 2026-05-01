<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('../includes/dbconn.php');

if(strlen($_SESSION['alogin'])==0){   
    header('location:../index.php');
    exit;
}

$empid = intval($_GET['empid']);

// Get employee info
$sql = "SELECT * FROM tblemployees WHERE id=:empid";
$query = $dbh->prepare($sql);
$query->bindParam(':empid', $empid, PDO::PARAM_INT);
$query->execute();
$employee = $query->fetch(PDO::FETCH_OBJ);

// Create document tables if not exist
$dbh->exec("CREATE TABLE IF NOT EXISTS tbl_document_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$dbh->exec("CREATE TABLE IF NOT EXISTS tbl_employee_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    emp_id INT NOT NULL,
    doc_type_id INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    description TEXT,
    uploaded_on DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX (emp_id)
)");

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
                                                <a href="../uploads/documents/<?php echo $doc->file_name; ?>" target="_blank" class="btn btn-sm btn-info">View</a>
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
