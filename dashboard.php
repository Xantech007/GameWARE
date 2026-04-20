<?php
include "inc/header.php";
include "inc/navbar.php";

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// FETCH USER
$stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// TOTAL EARNINGS
$earn = $conn->prepare("SELECT SUM(amount) as total FROM earnings WHERE user_id=?");
$earn->execute([$user_id]);
$totalEarned = $earn->fetch()['total'] ?? 0;

// TOTAL WITHDRAWN
$with = $conn->prepare("SELECT SUM(amount) as total FROM withdrawals WHERE user_id=? AND status='approved'");
$with->execute([$user_id]);
$totalWithdrawn = $with->fetch()['total'] ?? 0;

// HISTORY
$history = $conn->prepare("SELECT * FROM earnings WHERE user_id=? ORDER BY id DESC LIMIT 10");
$history->execute([$user_id]);
$history = $history->fetchAll();

// WITHDRAWALS
$wd = $conn->prepare("SELECT * FROM withdrawals WHERE user_id=? ORDER BY id DESC LIMIT 10");
$wd->execute([$user_id]);
$withdrawals = $wd->fetchAll();
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
.container{max-width:1100px;margin:auto;padding:20px;}
.cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;margin-top:20px;}

.card{
    background:#fff;
    padding:20px;
    border-radius:10px;
    box-shadow:0 5px 15px rgba(0,0,0,0.05);
}

h2{margin-top:30px;}

table{
    width:100%;
    border-collapse:collapse;
    background:#fff;
    border-radius:10px;
    overflow:hidden;
}

table th,table td{
    padding:12px;
    border-bottom:1px solid #eee;
    text-align:left;
}

.btn{
    background:#00aaff;
    color:#fff;
    padding:10px 15px;
    border:none;
    border-radius:6px;
    cursor:pointer;
}
</style>

<div class="container">

<h1><i class="fa-solid fa-chart-line"></i> Dashboard</h1>

<!-- SUMMARY -->
<div class="cards">
    <div class="card">
        <h3>Balance</h3>
        <p><?php echo $currency . " " . number_format($user['balance'],2); ?></p>
    </div>

    <div class="card">
        <h3>Total Earned</h3>
        <p><?php echo $currency . " " . number_format($totalEarned,2); ?></p>
    </div>

    <div class="card">
        <h3>Total Withdrawn</h3>
        <p><?php echo $currency . " " . number_format($totalWithdrawn,2); ?></p>
    </div>
</div>

<!-- WITHDRAW FORM -->
<h2><i class="fa-solid fa-wallet"></i> Request Withdrawal</h2>

<form method="POST" action="withdraw.php">
    <input type="number" name="amount" step="0.01" required placeholder="Enter amount">
    <button class="btn">Withdraw</button>
</form>

<!-- EARNINGS HISTORY -->
<h2><i class="fa-solid fa-clock"></i> Earnings History</h2>

<table>
<tr><th>Game</th><th>Amount</th><th>Date</th></tr>
<?php foreach($history as $row): ?>
<tr>
    <td><?php echo htmlspecialchars($row['game']); ?></td>
    <td><?php echo $currency . " " . $row['amount']; ?></td>
    <td><?php echo $row['created_at']; ?></td>
</tr>
<?php endforeach; ?>
</table>

<!-- WITHDRAW HISTORY -->
<h2><i class="fa-solid fa-money-bill-transfer"></i> Withdrawals</h2>

<table>
<tr><th>Amount</th><th>Status</th><th>Date</th></tr>
<?php foreach($withdrawals as $w): ?>
<tr>
    <td><?php echo $currency . " " . $w['amount']; ?></td>
    <td><?php echo ucfirst($w['status']); ?></td>
    <td><?php echo $w['created_at']; ?></td>
</tr>
<?php endforeach; ?>
</table>

</div>

<?php include "inc/footer.php"; ?>
