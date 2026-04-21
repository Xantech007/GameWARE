<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
.navbar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:12px 20px;
    background:#fff;
    border-bottom:1px solid #eaeaea;
    box-shadow:0 2px 10px rgba(0,0,0,0.05);
    position:relative;
}

/* LEFT */
.nav-left{
    font-size:18px;
    font-weight:bold;
    color:#00aaff;
    display:flex;
    align-items:center;
    gap:8px;
}

/* RIGHT (desktop default) */
.nav-right{
    display:flex;
    align-items:center;
    gap:12px;
}

/* LINKS */
.nav-right a{
    text-decoration:none;
    color:#333;
    font-weight:500;
    transition:0.2s;
    white-space:nowrap;
}

.nav-right a:hover{
    color:#00aaff;
}

/* WALLET */
.balance{
    background:#eaf6ff;
    padding:6px 12px;
    border-radius:20px;
    font-size:14px;
    color:#0077aa;
    display:flex;
    align-items:center;
    gap:6px;
    white-space:nowrap;
}

/* HAMBURGER */
.menu-toggle{
    display:none;
    font-size:22px;
    cursor:pointer;
}

/* MOBILE */
@media (max-width: 768px){

    .menu-toggle{
        display:block;
    }

    .nav-right{
        display:none;
        position:absolute;
        top:60px;
        right:0;
        left:0;
        background:#fff;
        flex-direction:column;
        padding:15px;
        gap:15px;
        box-shadow:0 5px 15px rgba(0,0,0,0.1);
        z-index:999;
    }

    .nav-right.show{
        display:flex;
    }

    .nav-right a,
    .balance{
        width:100%;
    }
}
</style>

<div class="navbar">

    <div class="nav-left">
        <i class="fa-solid fa-gamepad"></i>
        <?php echo htmlspecialchars($site_name); ?>
    </div>

    <!-- HAMBURGER -->
    <div class="menu-toggle" onclick="toggleMenu()">
        <i class="fa-solid fa-bars"></i>
    </div>

    <div class="nav-right" id="navMenu">

        <!-- HOME -->
        <?php if($current_page !== 'index.php'): ?>
            <a href="/index.php">
                <i class="fa-solid fa-house"></i> Home
            </a>
        <?php endif; ?>

        <?php if(isset($_SESSION['user_id'])): ?>

            <!-- DASHBOARD -->
            <?php if($current_page !== 'dashboard.php'): ?>
                <a href="/dashboard.php">
                    <i class="fa-solid fa-chart-line"></i> Dashboard
                </a>
            <?php endif; ?>

                 <!-- PROFILE -->
                <?php if($current_page !== 'profile.php'): ?>
                    <a href="/profile.php">
                        <i class="fa-solid fa-user"></i> Profile
                    </a>
                <?php endif; ?>
        
            <!-- WALLET (always before logout) -->
            <span class="balance">
                <i class="fa-solid fa-wallet"></i>
                <?php
                $user_balance = 0.00;
                
                if(isset($_SESSION['user_id'])){
                    $stmt = $conn->prepare("SELECT balance FROM users WHERE id = ?");
                    $stmt->execute([$_SESSION['user_id']]);
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                
                    if($row){
                        $user_balance = $row['balance'];
                    }
                }
                ?>
                
                <?php echo $currency . " " . number_format($user_balance, 2); ?>
            </span>

            <!-- LOGOUT -->
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

<script>
function toggleMenu(){
    document.getElementById("navMenu").classList.toggle("show");
}
</script>
