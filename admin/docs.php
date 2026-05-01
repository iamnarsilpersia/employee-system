<?php
session_start();
error_reporting(0);
include('../includes/dbconn.php');
include('../includes/functions.php');

if(strlen($_SESSION['alogin'])==0){   
    header('location:../index.php');
    exit;
}

$page='docs'; 
include('../includes/admin-header.php'); 
?>

    <div class="main-content-inner">
        <div class="row">
            <div class="col-12 mt-5">
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title">Documents</h4>
                        <p class="text-muted font-14 mb-4">Manage employee documents here.</p>
                        
                        <?php
                        $sql = "SELECT * FROM tblemployees ORDER BY FirstName";
                        $query = $dbh->prepare($sql);
                        $query->execute();
                        $results = $query->fetchAll(PDO::FETCH_OBJ);
                        
                        if($query->rowCount() > 0){
                            foreach($results as $emp){ ?>  
                                <div class="mb-3">
                                    <strong><?php echo htmlentities($emp->FirstName . ' ' . $emp->LastName); ?></strong><br>
                                    <small>ID: <?php echo htmlentities($emp->EmpId); ?></small>
                                    <a href="view-documents.php?empid=<?php echo $emp->id; ?>" class="btn btn-sm btn-info">View Documents</a>
                                </div>
                        <?php } } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include '../includes/admin-footer.php'; ?>
