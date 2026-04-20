<div class="navbar">

    <div>
        <strong><?php echo htmlspecialchars($site_name); ?></strong>
    </div>

    <div>
        <a href="/index.php">Home</a>

        <?php if(isset($_SESSION['user_id'])): ?>
            <span style="margin-left:15px;">
                💰 <?php echo $currency . " " . number_format($_SESSION['balance'],2); ?>
            </span>
            <a href="/dashboard.php">Dashboard</a>
            <a href="/logout.php">Logout</a>
        <?php else: ?>
            <a href="/login.php">Login</a>
            <a href="/register.php">Register</a>
        <?php endif; ?>
    </div>

</div>
