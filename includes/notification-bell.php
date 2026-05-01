<ul class="notification-area" style="padding:0; margin:0; list-style:none;">
    <li class="dropdown" style="display:inline-block;">
        <i class="ti-bell dropdown-toggle" data-toggle="dropdown" style="cursor:pointer; font-size:20px; position:relative; color:#4F46E5; padding:10px;"></i>
        <?php 
        // Get unread count
        include 'dbconn.php';
        $sql = "SELECT id from tblnotifications where user_type='employee' AND is_read=0";
        $query = $dbh->prepare($sql);
        $query->execute();
        $unreadcount = $query->rowCount();
        ?>
        <?php if($unreadcount > 0): ?>
        <span style="position:absolute; top:2px; right:2px; background:#EF4444; color:white; border-radius:50%; padding:2px 5px; font-size:10px;"><?php echo $unreadcount; ?></span>
        <?php endif; ?>
        <div class="dropdown-menu bell-notify-box notify-box" style="width:320px; max-height:350px; overflow-y:auto; right:0; left:auto; z-index:9999;">
            <span class="notify-title"><?php echo $unreadcount;?> notification(s)</span>
            <?php if($unreadcount > 0): ?>
            <a href="mark-all-read.php" style="float:right; font-size:11px; color:#4F46E5;">Mark all read</a>
            <?php endif; ?>
            <div class="notify-list" style="margin-top:10px;">
                <?php 
                $sql = "SELECT * from tblnotifications where user_type='employee' ORDER BY created_at DESC LIMIT 15";
                $query = $dbh->prepare($sql);
                $query->execute();
                if($query->rowCount() > 0) {
                    while($result = $query->fetch(PDO::FETCH_OBJ)) { ?>  
                    <a href="<?php echo isset($result->link) ? $result->link : '#'; ?>" class="notify-item" style="display:block; padding:10px; border-bottom:1px solid #eee; <?php echo $result->is_read ? 'opacity:0.6;' : ''; ?>">
                        <div class="notify-text">
                            <p style="margin:0; font-size:13px;"><b><?php echo htmlentities($result->title); ?></b></p>
                            <p style="margin:3px 0; font-size:12px;"><?php echo htmlentities($result->message); ?></p>
                            <span style="font-size:10px; color:#888;"><?php echo date('M d, h:i A', strtotime($result->created_at)); ?></span>
                        </div>
                    </a>
                    <?php }
                } else { ?>
                    <div style="padding:20px; text-align:center;"><p>No notifications</p></div>
                <?php } ?> 
            </div>
        </div>
    </li>
</ul>