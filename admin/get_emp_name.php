<?php
// API endpoint - no template needed
include('../includes/dbconn.php');

if(isset($_POST['empid'])){
    $empid = $_POST['empid'];
    
    $sql = "SELECT FirstName, LastName FROM tblemployees WHERE EmpId=:empid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':empid', $empid);
    $query->execute();
    $row = $query->fetch(PDO::FETCH_ASSOC);
    
    if($row){
        echo $row['FirstName'].' '.$row['LastName'];
    } else {
        echo "";
    }
}
?>
