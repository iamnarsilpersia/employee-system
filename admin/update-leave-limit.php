<?php
session_start();
include('../includes/dbconn.php');
include('../includes/functions.php');

if(strlen($_SESSION['alogin'])==0){   
    header('location:../index.php');
    exit;
}

if(isset($_POST['leave_limit'])) {
    $leave_limit = intval($_POST['leave_limit']);
    
    $sql = "UPDATE settings SET leave_limit=:limit WHERE id=1";
    $query = $dbh->prepare($sql);
    $query->bindParam(':limit', $leave_limit, PDO::PARAM_INT);
    $query->execute();
    
    $_SESSION['msg'] = "Leave limit updated successfully!";
}

header('location:leave-section.php');
exit;
?>