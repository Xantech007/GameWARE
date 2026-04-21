<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
.navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 20px;
    background: #fff;
    border-bottom: 1px solid #eaeaea;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    position: sticky;
    top: 0;
    z-index: 1000;
}

/* LEFT SIDE - Logo */
.nav-left {
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
}

.nav-left img {
    height: 38px;
    width: auto;
}

/* CENTER - Balance */
.nav-center {
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 1;
    padding: 0 15px;
}

.balance {
    background: #eaf6ff;
    padding: 8px 18px;
    border-radius: 25px;
    font-size: 14.5px;
    color: #0077aa;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
}

/* RIGHT SIDE - Desktop */
.nav-right {
    display: flex;
    align-items: center;
    gap: 18px;
}

/* Links */
.nav-right a {
    text-decoration: none;
    color: #333;
    font-weight: 500;
    transition: color 0.2s;
    display: flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
}

.nav-right a:hover {
    color: #00aaff;
}

/* Hamburger */
.menu-toggle {
    display: none;
    font-size: 24px;
    cursor: pointer;
    color: #333;
}

/* ===================== MOBILE ===================== */
@media (max-width: 768px) {
    .navbar {
        flex-wrap: wrap;
    }

    .nav-left {
        flex: 1;
    }

    .menu-toggle {
        display: block;
    }

    .nav-center {
        order: 3;
        width: 100%;
        margin: 10px 0;
        justify-content: center;
    }

    .nav-right {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: #fff;
        flex-direction: column;
        padding: 20px;
        gap: 16px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        z-index: 999;
        width: 100%;
    }

    .nav-right.show {
        display: flex;
    }

    .nav-right a {
        width: 100%;
        justify-content: center;
        padding: 10px 0;
    }
}
</style>

<div class="navbar">
    <!-- Logo -->
    <a href="/index.php" class="nav-left">
        <img src="assets/images/logo.png" alt="<?php echo htmlspecialchars($site_name ?? 'PlayEarn'); ?>">
    </a>

    <!-- Balance - Now in the center -->
    <div class="nav-center">
        <span class="balance">
            <i class="fa-solid fa-wallet"></i>
            <?php
            $user_balance = 0.00;
            if (isset($_SESSION['user_id']) && isset($conn)) {
                $stmt = $conn->prepare("SELECT balance FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row) {
                    $user_balance = $row['balance'];
                }
            }
            echo (\( currency ?? ' \)') . " " . number_format($user_balance, 2);
            ?>
        </span>
    </div>

    <!-- Hamburger Menu -->
    <div class="menu-toggle" onclick="toggleMenu()">
        <i class="fa-solid fa-bars"></i>
    </div>

    <!-- Navigation Menu -->
    <div class="nav-right" id="navMenu">
        <?php if ($current_page !== 'index.php'): ?>
            <a href="/index.php">
                <i class="fa-solid fa-house"></i> Home
            </a>
        <?php endif; ?>

        <?php if (isset($_SESSION['user_id'])): ?>
            <!-- Logged-in User Menu -->
            <?php if ($current_page !== 'dashboard.php'): ?>
                <a href="/dashboard.php">
                    <i class="fa-solid fa-chart-line"></i> Dashboard
                </a>
            <?php endif; ?>

            <?php if ($current_page !== 'profile.php'): ?>
                <a href="/profile.php">
                    <i class="fa-solid fa-user"></i> Profile
                </a>
            <?php endif; ?>

            <!-- Logout -->
            <a href="/logout.php">
                <i class="fa-solid fa-right-from-bracket"></i> Logout
            </a>

        <?php else: ?>
            <!-- Guest Menu -->
            <a href="/login.php">
                <i class="fa-solid fa-right-to-bracket"></i> Login
            </a>
            <a href="/register.php">
                <i class="fa-solid fa-user-plus"></i> Register
            </a>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleMenu() {
    document.getElementById("navMenu").classList.toggle("show");
}

// Close menu when clicking outside
document.addEventListener('click', function(e) {
    const menu = document.getElementById("navMenu");
    const toggle = document.querySelector(".menu-toggle");
    if (!menu.contains(e.target) && !toggle.contains(e.target)) {
        menu.classList.remove("show");
    }
});
</script>
