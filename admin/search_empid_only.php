<?php
// API endpoint - no template needed
include('../includes/dbconn.php');

if(isset($_POST['query'])){
    $search = $_POST['query'].'%';
    $sql = "SELECT EmpId FROM tblemployees WHERE EmpId LIKE :search LIMIT 10";
    $query = $dbh->prepare($sql);
    $query->bindParam(':search', $search);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_ASSOC);
    
    if($results){
        foreach($results as $row){
            echo "<li>".$row['EmpId']."</li>";
        }
    } else {
        echo "<li>No matching ID</li>";
    }
}
?>
