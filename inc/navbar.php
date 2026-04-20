<div style="background:#000;padding:10px;display:flex;justify-content:space-between;">
    <div><?php echo $site_name; ?></div>

    <div>
    <?php if(isset($_SESSION['user_id'])): ?>
        Balance: <?php echo $currency . " " . number_format($_SESSION['balance'],2); ?>
        | <a href="logout.php" style="color:#fff;">Logout</a>
    <?php else: ?>
        <a href="login.php" style="color:#fff;">Login</a> |
        <a href="register.php" style="color:#fff;">Register</a>
    <?php endif; ?>
    </div>
</div>
