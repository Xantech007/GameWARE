<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
.navbar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:15px 25px;
    background:#fff;
    border-bottom:1px solid #eaeaea;
    box-shadow:0 2px 10px rgba(0,0,0,0.05);
    flex-wrap:wrap;
    position:relative;
}

.nav-left{
    font-size:18px;
    font-weight:bold;
    color:#00aaff;
}

.nav-toggle{
    display:none;
    font-size:22px;
    cursor:pointer;
}

.nav-right{
    display:flex;
    align-items:center;
    gap:15px;
}

/* links */
.navbar a{
    text-decoration:none;
    color:#333;
    font-weight:500;
    transition:0.2s;
}

.navbar a:hover{
    color:#00aaff;
}

.balance{
    background:#eaf6ff;
    padding:6px 12px;
    border-radius:20px;
    font-size:14px;
    color:#0077aa;
}

/* MOBILE */
@media (max-width:768px){

    .nav-toggle{
        display:block;
    }

    .nav-right{
        display:none;
        width:100%;
        flex-direction:column;
        align-items:flex-start;
        gap:12px;
        margin-top:15px;
        padding-top:10px;
        border-top:1px solid #eee;
    }

    .nav-right.active{
        display:flex;
    }

    .nav-right a,
    .nav-right span{
        width:100%;
        padding:10px 0;
    }

    /* ensure wallet sits directly above logout */
    .balance{
        order:1;
        width:100%;
    }

    .nav-right a[href*="logout"]{
        order:2;
        width:100%;
    }
}
</style>

<div class="navbar">

    <div class="nav-left">
        <i class="fa-solid fa-gamepad"></i>
        <?php echo htmlspecialchars($site_name); ?>
    </div>

    <div class="nav-toggle" onclick="document.querySelector('.nav-right').classList.toggle('active')">
        <i class="fa-solid fa-bars"></i>
    </div>

    <div class="nav-right">

        <?php if($current_page !== 'index.php'): ?>
            <a href="/index.php">
                <i class="fa-solid fa-house"></i> Home
            </a>
        <?php endif; ?>

        <?php if(isset($_SESSION['user_id'])): ?>

            <span class="balance">
                <i class="fa-solid fa-wallet"></i>
                <?php echo $currency . " " . number_format($_SESSION['balance'],2); ?>
            </span>

            <?php if($current_page !== 'dashboard.php'): ?>
                <a href="/dashboard.php">
                    <i class="fa-solid fa-chart-line"></i> Dashboard
                </a>
            <?php endif; ?>

            <a href="/logout.php">
                <i class="fa-solid fa-right-from-bracket"></i> Logout
            </a>

        <?php else: ?>

            <a href="/login.php">
                <i class="fa-solid fa-user"></i> Login
            </a>

            <a href="/register.php">
                <i class="fa-solid fa-user-plus"></i> Register
            </a>

        <?php endif; ?>

    </div>

</div>
