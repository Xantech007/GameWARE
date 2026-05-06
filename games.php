<?php

include "inc/header.php";
include "inc/navbar.php";

/* CONNECT DB */
if (!isset($conn)) {
    require_once "config/database.php";
    $db = new Database();
    $conn = $db->connect();
}

// Fetch active games
$stmt = $conn->prepare("SELECT * FROM games WHERE status = 1 ORDER BY id DESC");
$stmt->execute();
$games = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>GameWARE - Play & Earn</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <link rel="icon" type="image/png" href="assets/favicon.png" />
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        * { box-sizing: border-box; }
        .container { max-width: 1200px; margin: auto; padding: 15px; }
        .page-header { text-align: center; margin: 40px 0 25px; }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 22px;
        }

        .card {
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0,0,0,0.07);
        }

        .card img {
            width: 100%;
            height: 170px;
            object-fit: cover;
        }

        .card-body {
            padding: 18px;
        }

        .play-btn {
            display: block;
            padding: 12px;
            text-align: center;
            background: #00aaff;
            color: #fff;
            border-radius: 10px;
            text-decoration: none;
            margin-top: 10px;
        }
    </style>
</head>
<body>

<main>
    <div class="container">

        <div class="page-header">
            <h1><i class="fa-solid fa-gamepad"></i> GameWARE</h1>
            <p>Play games and earn</p>
        </div>

        <div class="grid">
            <?php foreach ($games as $game): ?>
                <div class="card">
                    <img src="<?= htmlspecialchars($game['thumbnail']) ?>">
                    <div class="card-body">
                        <h3><?= htmlspecialchars($game['name']) ?></h3>
                        <p>$<?= number_format($game['reward_per_min'], 4) ?>/min</p>

                        <a href="#"
                        onclick="loadGameWARE(
                            '<?= htmlspecialchars($game['crazygames_slug']) ?>',
                            <?= $game['id'] ?>,
                            <?= $game['reward_per_min'] ?? 0 ?>
                        ); return false;"
                        class="play-btn">
                        Play Now
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </div>
</main>

<script>
let currentSessionId = localStorage.getItem('sessionId') || null;
let heartbeatInterval = null;
let idleTime = 0;
let isIdle = false;
let liveEarnings = 0;

// START GAME
function loadGameWARE(slug, gameId, rpm = 0) {

    fetch('track_play.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `game_id=${gameId}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            currentSessionId = data.session_id;

            localStorage.setItem('sessionId', currentSessionId);
            localStorage.setItem('isPlaying', '1');

            if (typeof setPlayingState === "function") {
                setPlayingState(true);
            }

            startHeartbeat();
        }

        window.location.href = "?game=" + encodeURIComponent(slug);
    });
}

// HEARTBEAT LOOP
function startHeartbeat() {

    if (heartbeatInterval) clearInterval(heartbeatInterval);

    heartbeatInterval = setInterval(() => {

        if (!currentSessionId || isIdle) return;

        fetch('heartbeat.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `session_id=${currentSessionId}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.earned) {
                liveEarnings += parseFloat(data.earned);
                updateBalanceUI();
            }
        });

    }, 10000);
}

// UPDATE BALANCE LIVE
function updateBalanceUI() {
    const el = document.getElementById('navBalance');
    if (!el) return;

    let current = parseFloat(el.innerText.replace(/[^0-9.]/g, ''));
    let updated = current + liveEarnings;

    el.innerText = "$" + updated.toFixed(4);

    liveEarnings = 0;
}

// IDLE DETECTION
function resetIdle() {
    idleTime = 0;
    isIdle = false;
}

setInterval(() => {
    idleTime++;

    if (idleTime > 60) {
        isIdle = true;
    }
}, 1000);

['mousemove','keydown','click','scroll','touchstart'].forEach(evt => {
    document.addEventListener(evt, resetIdle);
});

// RESTORE STATE AFTER RELOAD
document.addEventListener('DOMContentLoaded', () => {

    const params = new URLSearchParams(window.location.search);

    if (params.get('game')) {

        if (typeof setPlayingState === "function") {
            setPlayingState(true);
        }

        startHeartbeat();

    } else {

        localStorage.removeItem('sessionId');
        localStorage.removeItem('isPlaying');

        if (typeof setPlayingState === "function") {
            setPlayingState(false);
        }
    }
});

// END SESSION RELIABLY
window.addEventListener('beforeunload', function() {
    if (currentSessionId) {
        navigator.sendBeacon('end_play.php', new URLSearchParams({
            session_id: currentSessionId
        }));
    }
});
</script>

<?php include "inc/footer.php"; ?>
