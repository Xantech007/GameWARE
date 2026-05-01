<?php
session_start();
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

/* CREATE SESSION */
$stmt = $conn->prepare("
    INSERT INTO game_sessions (user_id, game_id, start_time)
    VALUES (?, ?, NOW())
");
$stmt->execute([$user_id, $game_id]);

$session_id = $conn->lastInsertId();
$rate = (float)$game['reward_per_min'];
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($game['name']); ?> - Play</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            margin: 0;
            font-family: Arial;
            background: #0f172a;
            color: #fff;
        }

        .wrapper {
            display: flex;
            flex-direction: column;
            height: 100vh;
        }

        iframe {
            flex: 1;
            border: none;
            width: 100%;
        }

        .panel {
            background: #111827;
            padding: 15px;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
        }

        .panel span {
            font-weight: bold;
        }

        .btn {
            padding: 10px 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .quit {
            background: #ef4444;
            color: #fff;
        }

        .claim {
            background: #22c55e;
            color: #fff;
        }

        @media(max-width:600px){
            .panel {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>

<div class="wrapper">

    <!-- GAME -->
    <iframe src="<?php echo htmlspecialchars($game['file_path']); ?>"></iframe>

    <!-- PANEL -->
    <div class="panel">
        <div>⏱ Time: <span id="time">0</span>s</div>
        <div>💰 Earned: $<span id="earn">0.00</span></div>

        <div>
            <button class="btn quit" onclick="quitGame()">Quit</button>
            <button class="btn claim" onclick="claimEarnings()">Claim</button>
        </div>
    </div>

</div>

<script>
let seconds = 0;
let rate = <?php echo $rate; ?>;
let session_id = <?php echo $session_id; ?>;
let stopped = false;

let timer = setInterval(() => {
    if (stopped) return;

    seconds++;

    document.getElementById("time").innerText = seconds;

    let earned = (seconds / 60) * rate;
    document.getElementById("earn").innerText = earned.toFixed(2);
}, 1000);

/* QUIT GAME */
function quitGame() {
    stopped = true;

    fetch("ajax_end_session.php", {
        method: "POST",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify({
            session_id: session_id,
            duration: seconds
        })
    })
    .then(res => res.json())
    .then(data => {
        alert("Game ended. Earned: $" + data.amount);
        window.location.href = "index.php";
    });
}

/* CLAIM */
function claimEarnings() {
    fetch("ajax_claim.php", {
        method: "POST",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify({
            session_id: session_id
        })
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
    });
}
</script>

</body>
</html>
