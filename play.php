<?php
include "inc/header.php";
include "inc/navbar.php";
require_once "config/database.php";

/* CHECK LOGIN */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* CONNECT DB */
$db = new Database();
$conn = $db->connect();
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* MESSAGE */
$success = "";

/* GET GAME */
if (!isset($_GET['game_id'])) {
    die("Invalid game.");
}

$game_id = (int)$_GET['game_id'];

$stmt = $conn->prepare("SELECT * FROM games WHERE id = ? AND status = 1");
$stmt->execute([$game_id]);
$game = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$game) {
    die("Game not found.");
}

/* CREATE SESSION (ONCE PER LOAD) */
$stmt = $conn->prepare("
    INSERT INTO game_sessions (user_id, game_id, start_time)
    VALUES (?, ?, NOW())
");
$stmt->execute([$user_id, $game_id]);

$session_id = $conn->lastInsertId();
$rate = (float)$game['reward_per_min'];

/* =========================
   CLAIM HANDLER (SAFE)
   ========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['claim'])) {

    $session_id_post = (int)$_POST['session_id'];

    /* GET SESSION */
    $stmt = $conn->prepare("
        SELECT gs.*, g.reward_per_min 
        FROM game_sessions gs
        JOIN games g ON gs.game_id = g.id
        WHERE gs.id = ? AND gs.user_id = ?
    ");
    $stmt->execute([$session_id_post, $user_id]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$session) {
        $success = "⚠️ Session not found.";
    } else {

        /* AUTO FINALIZE IF NOT ENDED */
        if (empty($session['end_time'])) {

            $start = strtotime($session['start_time']);
            $end = time();

            $duration = $end - $start;
            $amount = ($duration / 60) * $session['reward_per_min'];

            if ($duration < 5) $amount = 0;

            $stmt = $conn->prepare("
                UPDATE game_sessions 
                SET end_time = NOW(),
                    duration = ?,
                    amount_earned = ?
                WHERE id = ?
            ");
            $stmt->execute([$duration, $amount, $session_id_post]);

            $session['amount_earned'] = $amount;
        }

        /* CLAIM CHECK */
        if ($session['claimed'] == 1) {
            $success = "⚠️ Already claimed.";
        } elseif ($session['amount_earned'] <= 0) {
            $success = "⚠️ No earnings to claim.";
        } else {

            /* UPDATE BALANCE */
            $stmt = $conn->prepare("
                UPDATE users 
                SET balance = balance + ? 
                WHERE id = ?
            ");
            $stmt->execute([$session['amount_earned'], $user_id]);

            /* MARK CLAIMED */
            $stmt = $conn->prepare("
                UPDATE game_sessions 
                SET claimed = 1 
                WHERE id = ?
            ");
            $stmt->execute([$session_id_post]);

            $success = "✅ $" . number_format($session['amount_earned'], 2) . " added to your balance!";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($game['name']); ?> - Play</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body { margin:0; font-family:Arial; background:#0f172a; color:#fff; }
        .wrapper { display:flex; flex-direction:column; height:100vh; }
        iframe { flex:1; border:none; width:100%; }

        .panel {
            background:#111827;
            padding:15px;
            display:flex;
            justify-content:space-between;
            align-items:center;
            flex-wrap:wrap;
        }

        .btn {
            padding:10px 15px;
            border:none;
            border-radius:6px;
            cursor:pointer;
        }

        .quit { background:#ef4444; color:#fff; }
        .claim { background:#22c55e; color:#fff; }

        .alert {
            background:#16a34a;
            padding:10px;
            text-align:center;
        }
    </style>
</head>
<body>

<?php if ($success): ?>
    <div class="alert"><?php echo $success; ?></div>
<?php endif; ?>

<div class="wrapper">

    <iframe src="<?php echo htmlspecialchars($game['file_path']); ?>"></iframe>

    <div class="panel">
        <div>⏱ Time: <b id="time">0</b>s</div>
        <div>💰 Earned: $<b id="earn">0.00</b></div>

        <div>
            <button class="btn quit" onclick="quitGame()">Quit</button>

            <!-- SAFE CLAIM FORM -->
            <form method="POST" style="display:inline;">
                <input type="hidden" name="session_id" value="<?php echo $session_id; ?>">
                <button type="submit" name="claim" class="btn claim">
                    Claim
                </button>
            </form>
        </div>
    </div>

</div>

<script>
let seconds = 0;
let rate = <?php echo $rate; ?>;
let stopped = false;

/* TIMER */
setInterval(() => {
    if (stopped) return;

    seconds++;
    document.getElementById("time").innerText = seconds;

    let earned = (seconds / 60) * rate;
    document.getElementById("earn").innerText = earned.toFixed(2);

}, 1000);

/* QUIT */
function quitGame() {
    stopped = true;

    fetch("ajax_end_session.php", {
        method: "POST",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify({
            session_id: <?php echo $session_id; ?>,
            duration: seconds
        })
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById("earn").innerText = data.amount;
        alert("Game ended. You can now claim your earnings.");
    });
}
</script>

</body>
</html>

<?php include "inc/footer.php"; ?>
