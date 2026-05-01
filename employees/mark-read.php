<?php
session_start();
include('../includes/dbconn.php');

session_start();
include('../includes/dbconn.php');

if(!isset($_SESSION['emplogin'])) {   
    header('location:../index.php');
    exit();
}

$eid = $_SESSION['eid'];

// Mark all admin-type notifications for this employee as read
$sql = "UPDATE tblnotifications SET is_read=1 WHERE user_type='admin' AND is_read=0 AND (user_id=:eid OR user_id=0)";
$query = $dbh->prepare($sql);
$query->bindParam(':eid', $eid, PDO::PARAM_INT);
$query->execute();

header('location:leave-history.php');
exit();
?>
?>
