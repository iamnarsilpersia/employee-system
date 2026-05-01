<link rel="stylesheet" href="../assets/css/styles.css">

<?php 
    include 'dbconn.php';
    
    // Get unread count for admin
    $sql = "SELECT id from tblnotifications where user_type='employee' AND is_read=0";
    $query = $dbh->prepare($sql);
    $query->execute();
    $unreadcount = $query->rowCount();
?>

<ul class="notification-area pull-right">
    <li class="dropdown">
        <i class="ti-bell dropdown-toggle" data-toggle="dropdown" style="cursor:pointer; font-size:20px; position:relative; color:#4F46E5;">
            <?php if($unreadcount > 0): ?>
            <span style="position:absolute; top:-8px; right:-8px; background:#EF4444; color:white; border-radius:50%; padding:2px 6px; font-size:11px;"><?php echo $unreadcount; ?></span>
            <?php endif; ?>
        </i>
        <div class="dropdown-menu bell-notify-box notify-box" style="width:350px; max-height:400px; overflow-y:auto; right:0; left:auto;">
            <span class="notify-title">You have <?php echo $unreadcount;?> <b>unread</b> notifications!</span>
            <?php if($unreadcount > 0): ?>
            <a href="mark-all-read.php" style="float:right; font-size:12px; color:#4F46E5;">Mark all as read</a>
            <?php endif; ?>

            <div class="notify-list">
                <?php 
                    $sql = "SELECT * FROM tblnotifications WHERE user_type='employee' ORDER BY created_at DESC LIMIT 20";
                    $query = $dbh->prepare($sql);
                    $query->execute();
                    $results = $query->fetchAll(PDO::FETCH_OBJ);
                    
                    if($query->rowCount() > 0) {
                        foreach($results as $result) { ?>  
                        <a href="<?php echo $result->link ? $result->link : '#'; ?>" class="notify-item" style="<?php echo $result->is_read ? 'opacity:0.6;' : ''; ?>">
                            <div class="notify-thumb">
                                <?php if(strpos($result->title, 'Leave') !== false): ?>
                                    <i class="ti-agenda btn-info"></i>
                                <?php elseif(strpos($result->title, 'Document') !== false): ?>
                                    <i class="ti-files btn-warning"></i>
                                <?php elseif(strpos($result->title, 'Profile') !== false): ?>
                                    <i class="ti-user btn-success"></i>
                                <?php else: ?>
                                    <i class="ti-bell btn-primary"></i>
                                <?php endif; ?>
                            </div>
                            <div class="notify-text">
                                <p><b><?php echo htmlentities($result->title); ?></b></p>
                                <p><?php echo htmlentities($result->message); ?></p>
                                <span><?php echo date('M d, Y h:i A', strtotime($result->created_at)); ?></span>
                            </div>
                        </a>
                    <?php }} else { ?>
                        <div class="notify-item">
                            <div class="notify-text" style="text-align:center; padding:20px;">
                                <p>No notifications</p>
                            </div>
                        </div>
                    <?php } ?> 
            </div>
        </div>
    </li>
</ul>
