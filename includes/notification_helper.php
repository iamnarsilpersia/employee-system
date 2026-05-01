<?php
// Helper function to add notifications
function addNotification($dbh, $userType, $userId, $title, $message, $link = null) {
    $sql = "INSERT INTO tblnotifications (user_type, user_id, title, message, link) VALUES (:user_type, :user_id, :title, :message, :link)";
    $query = $dbh->prepare($sql);
    $query->bindParam(':user_type', $userType, PDO::PARAM_STR);
    $query->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $query->bindParam(':title', $title, PDO::PARAM_STR);
    $query->bindParam(':message', $message, PDO::PARAM_STR);
    $query->bindParam(':link', $link, PDO::PARAM_STR);
    $query->execute();
}

// Notify admin (for employee actions)
function notifyAdmin($dbh, $title, $message, $link = null) {
    addNotification($dbh, 'employee', 0, $title, $message, $link);
}

// Notify specific employee (for admin actions)
function notifyEmployee($dbh, $empId, $title, $message, $link = null) {
    addNotification($dbh, 'admin', $empId, $title, $message, $link);
}
?>
