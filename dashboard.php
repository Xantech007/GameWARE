<?php
include "inc/header.php";
include "inc/navbar.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* CONNECT DB (if not already in header) */
if (!isset($conn)) {
    require_once "config/database.php";
    $db = new Database();
    $conn = $db->connect();
}

// FETCH USER
$stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// TOTAL EARNINGS
$earn = $conn->prepare("SELECT SUM(amount) as total FROM earnings WHERE user_id=?");
$earn->execute([$user_id]);
$totalEarned = $earn->fetch()['total'] ?? 0;

// TOTAL WITHDRAWN (only approved)
$with = $conn->prepare("SELECT SUM(amount) as total FROM withdrawals WHERE user_id=? AND status='approved'");
$with->execute([$user_id]);
$totalWithdrawn = $with->fetch()['total'] ?? 0;

// AVAILABLE BALANCE = Current balance from users table
$balance = $user['balance'] ?? 0;

// EARNINGS HISTORY (last 10)
$history = $conn->prepare("SELECT * FROM earnings WHERE user_id=? ORDER BY id DESC LIMIT 10");
$history->execute([$user_id]);
$earningsHistory = $history->fetchAll(PDO::FETCH_ASSOC);

// WITHDRAWALS HISTORY (last 10)
$wd = $conn->prepare("SELECT * FROM withdrawals WHERE user_id=? ORDER BY id DESC LIMIT 10");
$wd->execute([$user_id]);
$withdrawals = $wd->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- FONT AWESOME -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
.container {
    max-width: 1200px;
    margin: auto;
    padding: 20px;
}

h1 {
    font-size: 36px;
    margin-bottom: 30px;
}

/* SUMMARY CARDS */
.cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}
.card {
    background: #fff;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.06);
    text-align: center;
}
.card h3 {
    color: #666;
    font-size: 16px;
    margin-bottom: 8px;
}
.card p {
    font-size: 28px;
    font-weight: bold;
    margin: 0;
    color: #1e40af;
}

/* WITHDRAWAL SECTION */
.withdrawal-box {
    background: #fff;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.06);
    margin-bottom: 40px;
}
.withdrawal-box h2 {
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 18px;
}
.form-group label {
    display: block;
    margin-bottom: 6px;
    font-weight: 500;
}
.form-group input {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 16px;
}

.payment-methods {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    margin-top: 10px;
}
.payment-option {
    padding: 12px 18px;
    border: 2px solid #ddd;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s;
}
.payment-option.selected {
    border-color: #00aaff;
    background: #f0f9ff;
}

.btn {
    background: #00aaff;
    color: #fff;
    padding: 14px 28px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 17px;
    font-weight: 600;
}
.btn:hover {
    background: #0088cc;
}

/* TABLES */
table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0,0,0,0.06);
    margin-bottom: 40px;
}
table th, table td {
    padding: 14px 16px;
    text-align: left;
    border-bottom: 1px solid #eee;
}
table th {
    background: #f8fafc;
    font-weight: 600;
}

/* ICON COLORS */
.icon-blue { color: #00aaff; }
.icon-green { color: #22c55e; }
</style>

<div class="container">

    <h1><i class="fa-solid fa-chart-line icon-blue"></i> My Dashboard</h1>

    <!-- SUMMARY CARDS -->
    <div class="cards">
        <div class="card">
            <h3>Available Balance</h3>
            <p>$<?php echo number_format($balance, 2); ?></p>
        </div>
        <div class="card">
            <h3>Total Earned</h3>
            <p>$<?php echo number_format($totalEarned, 2); ?></p>
        </div>
        <div class="card">
            <h3>Total Withdrawn</h3>
            <p>$<?php echo number_format($totalWithdrawn, 2); ?></p>
        </div>
    </div>

    <!-- WITHDRAWAL SECTION - Upgraded -->
    <div class="withdrawal-box">
        <h2><i class="fa-solid fa-wallet icon-green"></i> Request Withdrawal</h2>
        <p style="color:#555; margin-bottom:20px;">Minimum withdrawal: $10.00 • Processed within 1-3 business days</p>

        <form id="withdrawForm" method="POST" action="withdrawals.php">
            <div class="form-group">
                <label for="amount">Withdrawal Amount (USD)</label>
                <input type="number" id="amount" name="amount" step="0.01" min="10" placeholder="Enter amount (min $10)" required>
            </div>

            <div class="form-group">
                <label>Payment Method</label>
                <div class="payment-methods">
                    <div class="payment-option selected" data-method="paypal">
                        <i class="fa-brands fa-paypal"></i> PayPal
                    </div>
                    <div class="payment-option" data-method="bank">
                        <i class="fa-solid fa-building-columns"></i> Bank Transfer
                    </div>
                    <div class="payment-option" data-method="wise">
                        <i class="fa-solid fa-globe"></i> Wise
                    </div>
                </div>
                <input type="hidden" id="payment_method" name="payment_method" value="paypal">
            </div>

            <button type="submit" class="btn">
                <i class="fa-solid fa-arrow-up-from-bracket"></i> Request Withdrawal
            </button>
        </form>
    </div>

    <!-- EARNINGS HISTORY -->
    <h2><i class="fa-solid fa-clock icon-blue"></i> Recent Earnings</h2>
    <table>
        <tr>
            <th>Game</th>
            <th>Amount</th>
            <th>Date</th>
        </tr>
        <?php if (count($earningsHistory) > 0): ?>
            <?php foreach ($earningsHistory as $row): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['game'] ?? 'Game Play'); ?></td>
                <td>$<?php echo number_format($row['amount'], 2); ?></td>
                <td><?php echo date('M d, Y H:i', strtotime($row['created_at'])); ?></td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="3" style="text-align:center; padding:30px;">No earnings yet. Start playing to earn!</td></tr>
        <?php endif; ?>
    </table>

    <!-- WITHDRAWALS HISTORY -->
    <h2><i class="fa-solid fa-money-bill-transfer icon-blue"></i> Withdrawal History</h2>
    <table>
        <tr>
            <th>Amount</th>
            <th>Method</th>
            <th>Status</th>
            <th>Date</th>
        </tr>
        <?php if (count($withdrawals) > 0): ?>
            <?php foreach ($withdrawals as $w): ?>
            <tr>
                <td>$<?php echo number_format($w['amount'], 2); ?></td>
                <td><?php echo ucfirst($w['payment_method'] ?? 'PayPal'); ?></td>
                <td><strong><?php echo ucfirst($w['status']); ?></strong></td>
                <td><?php echo date('M d, Y H:i', strtotime($w['created_at'])); ?></td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="4" style="text-align:center; padding:30px;">No withdrawals yet.</td></tr>
        <?php endif; ?>
    </table>

</div>

<script>
// Payment method selection
document.querySelectorAll('.payment-option').forEach(option => {
    option.addEventListener('click', function() {
        document.querySelectorAll('.payment-option').forEach(opt => opt.classList.remove('selected'));
        this.classList.add('selected');
        document.getElementById('payment_method').value = this.dataset.method;
    });
});
</script>

<?php include "inc/footer.php"; ?>
