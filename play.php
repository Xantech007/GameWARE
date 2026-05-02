<?php
include "inc/header.php";
include "inc/navbar.php";

// REQUIRE LOGIN
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// VALIDATE GAME ID
if (!isset($_GET['game_id']) || !is_numeric($_GET['game_id'])) {
    die("Invalid game.");
}

$game_id = (int) $_GET['game_id'];

// FETCH GAME
$stmt = $conn->prepare("SELECT * FROM games WHERE id = ? AND status = 1");
$stmt->execute([$game_id]);
$game = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$game) {
    die("Game not found.");
}

// GAME SETTINGS
$reward_per_min = (float)$game['reward_per_min'];
$game_path = $game['game_path']; // IMPORTANT: your game file path
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
.container {
    max-width: 1100px;
    margin: auto;
    padding: 20px;
}

.game-box {
    background: #fff;
    border-radius: 14px;
    padding: 15px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.08);
}

iframe {
    width: 100%;
    height: 500px;
    border: none;
    border-radius: 10px;
    background: #000;
}

.stats {
    display: flex;
    justify-content: space-between;
    margin-top: 15px;
    flex-wrap: wrap;
    gap: 10px;
}

.stat {
    background: #f8fafc;
    padding: 12px 15px;
    border-radius: 10px;
    font-size: 15px;
}

.earn {
    color: green;
    font-weight: bold;
}

.controls {
    margin-top: 20px;
    text-align: center;
}

button {
    padding: 12px 20px;
    border: none;
    border-radius: 10px;
    background: #00aaff;
    color: #fff;
    font-size: 16px;
    cursor: pointer;
}

button:hover {
    background: #0088cc;
}
</style>

<div class="container">

    <h2><i class="fa-solid fa-gamepad"></i> <?php echo htmlspecialchars($game['name']); ?></h2>

    <div class="game-box">

        <!-- GAME FRAME -->
        <iframe src="<?php echo htmlspecialchars($game_path); ?>"></iframe>

        <!-- STATS -->
        <div class="stats">
            <div class="stat">
                ⏱ Time: <span id="time">0</span> sec
            </div>

            <div class="stat">
                💰 Earned: 
                <span class="earn">$<span id="earnings">0.0000</span></span>
            </div>

            <div class="stat">
                ⚡ Rate: $<?php echo number_format($reward_per_min, 4); ?>/min
            </div>
        </div>

        <!-- CONTROLS -->
        <div class="controls">
            <button onclick="claimReward()">
                <i class="fa-solid fa-coins"></i> Claim Reward
            </button>
        </div>

    </div>

</div>

<script>
function claimReward() {

    if (earned <= 0) {
        alert("No earnings yet.");
        return;
    }

    fetch("claim_reward.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: "amount=" + earned.toFixed(4)
    })
    .then(res => res.json())
    .then(data => {

        if (data.status === "success") {

            // ✅ Update navbar balance instantly
            const navBalance = document.getElementById("navBalance");
            if (navBalance) {
                navBalance.innerText = "$" + data.new_balance;
            }

            alert(data.message);

            // RESET GAME STATS
            seconds = 0;
            earned = 0;
            document.getElementById("time").innerText = 0;
            document.getElementById("earnings").innerText = "0.0000";

        } else {
            alert(data.message);
        }

    })
    .catch(() => {
        alert("Something went wrong.");
    });
}
</script>

<?php include "inc/footer.php"; ?>
