<?php
session_start();
include('../includes/dbconn.php');

if(strlen($_SESSION['alogin'])==0){   
    header('location:../index.php');
    exit();
}

// Mark all employee-type notifications as read
$sql = "UPDATE tblnotifications SET is_read=1 WHERE user_type='employee' AND is_read=0";
$query = $dbh->prepare($sql);
$query->execute();

header('location:dashboard.php');
exit();
?>
