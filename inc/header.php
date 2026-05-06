<?php
session_start();
require_once __DIR__ . "/../config/database.php";

$db = new Database();
$conn = $db->connect();

$settings = $conn->query("SELECT * FROM settings WHERE id=1")->fetch(PDO::FETCH_ASSOC);

$site_name = $settings['site_name'];
$currency = $settings['currency'];
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($site_name); ?></title>

<link rel="icon" type="image/png" href="/assets/favicon.png">
<link rel="shortcut icon" href="/assets/favicon.png">

<link rel="stylesheet" href="/assets/css/theme.css">

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
/* ===== HEADER EARNINGS UI ===== */
.header-earnings {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 15px;
    margin: 15px auto;
    flex-wrap: wrap;
}

/* Balance box */
.balance-box {
    background: #111;
    color: #fff;
    padding: 10px 16px;
    border-radius: 10px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 15px;
}

/* Claim button */
.claim-btn {
    background: #00aaff;
    color: #fff;
    border: none;
    padding: 10px 18px;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 600;
    transition: 0.2s;
}

.claim-btn:hover {
    background: #0088cc;
}
</style>

</head>
<body>

<?php if (isset($_SESSION['user_id'])): ?>
<div class="header-earnings">

    <!-- Balance -->
    <div class="balance-box">
        <i class="fa-solid fa-wallet"></i>
        <span id="navBalance">
            <?php
            $user_balance = $_SESSION['balance'] ?? 0.00;

            if (isset($_SESSION['user_id'])) {
                $stmt = $conn->prepare("SELECT balance FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row) {
                    $user_balance = $row['balance'];
                    $_SESSION['balance'] = $user_balance;
                }
            }

            echo ($currency ?? '$') . number_format($user_balance, 4);
            ?>
        </span>
    </div>

    <!-- Claim Button -->
    <button onclick="claimEarnings()" class="claim-btn">
        💰 Claim Earnings
    </button>

</div>
<?php endif; ?>

<script>
function claimEarnings() {
    fetch('/claim.php')
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            alert("Claimed <?php echo $currency ?? '$'; ?>" + parseFloat(data.amount).toFixed(4));

            // Update balance instantly
            const balanceEl = document.getElementById('navBalance');
            let current = parseFloat(balanceEl.innerText.replace(/[^0-9.]/g, ''));
            let updated = current + parseFloat(data.amount);

            balanceEl.innerText = "<?php echo $currency ?? '$'; ?>" + updated.toFixed(4);
        } else {
            alert("No earnings to claim.");
        }
    })
    .catch(() => {
        alert("Error claiming earnings.");
    });
}
</script>
