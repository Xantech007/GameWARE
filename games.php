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

                        <a href="#" onclick="loadGameWARE('<?= $game['crazygames_slug'] ?>', <?= $game['id'] ?>); return false;"
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
let currentSessionId = null;

// START GAME
function loadGameWARE(slug, gameId) {

    fetch('track_play.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `game_id=${gameId}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            currentSessionId = data.session_id;

            // ✅ Activate claim button
            if (typeof setPlayingState === "function") {
                setPlayingState(true);
            }

            localStorage.setItem('isPlaying', '1');
        }

        // redirect AFTER session starts
        window.location.href = "?game=" + encodeURIComponent(slug);
    });
}

// END GAME
window.addEventListener('beforeunload', function() {
    if (currentSessionId) {

        if (typeof setPlayingState === "function") {
            setPlayingState(false);
        }

        navigator.sendBeacon('end_play.php', new URLSearchParams({
            session_id: currentSessionId
        }));

        localStorage.removeItem('isPlaying');
    }
});

// RESTORE STATE AFTER RELOAD
document.addEventListener('DOMContentLoaded', () => {
    const params = new URLSearchParams(window.location.search);

    if (params.get('game')) {
        localStorage.setItem('isPlaying', '1');

        if (typeof setPlayingState === "function") {
            setPlayingState(true);
        }
    } else {
        localStorage.removeItem('isPlaying');

        if (typeof setPlayingState === "function") {
            setPlayingState(false);
        }
    }
});
</script>

<?php include "inc/footer.php"; ?>
