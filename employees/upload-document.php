<?php
session_start();
include('../includes/dbconn.php');
include('../includes/functions.php');

require_employee_login();

$eid = $_SESSION['eid'];
$msg = $error = "";

initialize_database_tables($dbh);

$docTypes = $dbh->query("SELECT * FROM tbl_document_types ORDER BY type_name")->fetchAll(PDO::FETCH_OBJ);

if(isset($_POST['upload'])){
    $doc_type = intval($_POST['doc_type']);
    $description = sanitize_input($_POST['description']);
    
    if(empty($doc_type)) {
        $error = "Please select a document type.";
    } elseif(!empty($_FILES['document']['name'])) {
        $file_name = $_FILES['document']['name'];
        $file_tmp = $_FILES['document']['tmp_name'];
        $file_size = $_FILES['document']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed = array('pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx');
        
        if(!in_array($file_ext, $allowed)) {
            $error = "Invalid file type. Allowed: PDF, JPG, PNG, DOC, DOCX";
        } elseif($file_size > 5242880) {
            $error = "File size must be less than 5MB.";
        } else {
            $new_filename = $eid . '_' . time() . '.' . $file_ext;
            $upload_path = '../uploads/documents/' . $new_filename;
            
            if(!is_dir('../uploads/documents/')){
                mkdir('../uploads/documents/', 0755, true);
            }
            
            if(move_uploaded_file($file_tmp, $upload_path)){
                $sql = "INSERT INTO tbl_employee_documents (emp_id, doc_type_id, file_name, original_name, description, uploaded_on) 
                        VALUES(:eid, :type, :fname, :oname, :desc, NOW())";
                $query = $dbh->prepare($sql);
                $query->bindParam(':eid', $eid, PDO::PARAM_INT);
                $query->bindParam(':type', $doc_type, PDO::PARAM_INT);
                $query->bindParam(':fname', $new_filename, PDO::PARAM_STR);
                $query->bindParam(':oname', $file_name, PDO::PARAM_STR);
                $query->bindParam(':desc', $description, PDO::PARAM_STR);
                
                if($query->execute()){
                    $msg = "Document uploaded successfully.";
                } else {
                    $error = "Failed to save document record.";
                }
            } else {
                $error = "Failed to upload file.";
            }
        }
    } else {
        $error = "Please select a file to upload.";
    }
}

$sql = "SELECT d.*, t.type_name FROM tbl_employee_documents d 
        LEFT JOIN tbl_document_types t ON d.doc_type_id = t.id 
        WHERE d.emp_id=:eid ORDER BY d.uploaded_on DESC";
$query = $dbh->prepare($sql);
$query->bindParam(':eid', $eid, PDO::PARAM_INT);
$query->execute();
$docs = $query->fetchAll(PDO::FETCH_OBJ);
?>

<?php $page='documents'; include('../includes/employee-header.php'); ?>

    <div class="main-content-inner">
        <div class="row">
            <div class="col-12 mt-5">
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title">Upload Document</h4>
                        <p class="text-muted">Upload your documents with document type</p>
                        
                        <?php if($error){ ?>
                        <div class="alert alert-danger"><?php echo htmlentities($error); ?></div>
                        <?php } ?>
                        
                        <?php if($msg){ ?>
                        <div class="alert alert-success"><?php echo htmlentities($msg); ?></div>
                        <?php } ?>
                        
                        <form method="POST" enctype="multipart/form-data">
                            <div class="form-group">
                                <label>Document Type</label>
                                <select name="doc_type" class="form-control" required>
                                    <option value="">Select Document Type</option>
                                    <?php foreach($docTypes as $type){ ?>
                                        <option value="<?php echo $type->id; ?>"><?php echo htmlentities($type->type_name); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="description" class="form-control" rows="3" placeholder="Document description"></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label>Select File</label>
                                <input type="file" name="document" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required>
                                <small class="text-muted">Allowed: PDF, JPG, PNG, DOC, DOCX (Max 5MB)</small>
                            </div>
                            
                            <button type="submit" name="upload" class="btn btn-primary">Upload Document</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title">My Documents</h4>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
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
                                    <?php if(count($docs) > 0){
                                        foreach($docs as $doc){ ?>
                                            <tr>
                                                <td><?php echo htmlentities($doc->type_name); ?></td>
                                                <td><?php echo htmlentities($doc->original_name); ?></td>
                                                <td><?php echo htmlentities($doc->description); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($doc->uploaded_on)); ?></td>
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
                                                    <a href="../uploads/documents/<?php echo htmlentities($doc->file_name); ?>" target="_blank" class="btn btn-sm btn-info">View</a>
                                                </td>
                                            </tr>
                                        <?php }
                                    } else { ?>
                                        <tr><td colspan="6" class="text-center">No documents uploaded yet.</td></tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include('../includes/employee-footer.php'); ?>