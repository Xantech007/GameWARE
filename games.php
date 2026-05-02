<?php

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
    <meta name="color-scheme" content="dark" />

    <!-- GameWARE Assets (Updated) -->
    <link rel="icon" type="image/png" href="assets/favicon.png" />

    <link rel="stylesheet" href="style.css" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        * { box-sizing: border-box; }
        .container { max-width: 1200px; margin: auto; padding: 15px; }
        .page-header { text-align: center; margin: 40px 0 25px; }
        .page-header h1 { font-size: 32px; margin-bottom: 8px; }
        .notice {
            background: #fff3cd; color: #856404; padding: 14px 20px;
            border-radius: 10px; margin-bottom: 30px; text-align: center;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 22px;
            margin-top: 20px;
        }
        .card {
            background: #fff; border-radius: 16px; overflow: hidden;
            box-shadow: 0 8px 25px rgba(0,0,0,0.07); transition: 0.3s;
        }
        .card:hover { transform: translateY(-6px); box-shadow: 0 15px 35px rgba(0,0,0,0.12); }
        .card img { width: 100%; height: 170px; object-fit: cover; }
        .card-body { padding: 18px 20px; flex: 1; }
        .play-btn {
            display: block; width: 100%; padding: 14px; text-align: center;
            background: #00aaff; color: #fff; border-radius: 10px;
            text-decoration: none; font-weight: 600; margin-top: 15px;
        }
        .play-btn:hover { background: #0088cc; }
    </style>
</head>
<body>

    <div class="loader" id="loader" style="display:none;">Loading Game...</div>

    <main id="gameInput">
        <div class="container">
            <div class="page-header">
                <h1><i class="fa-solid fa-gamepad"></i> GameWARE</h1>
                <p>Play games and earn • Powered by GameWARE</p>
            </div>

            <?php if (!isset($_SESSION['user_id'])): ?>
            <div class="notice">
                <i class="fa-solid fa-circle-info"></i>
                <strong>Login required</strong> to track playtime and earn rewards.
            </div>
            <?php endif; ?>

            <div class="grid">
                <?php if (count($games) > 0): ?>
                    <?php foreach ($games as $game): ?>
                        <div class="card">
                            <?php if (!empty($game['thumbnail'])): ?>
                                <img src="<?= htmlspecialchars($game['thumbnail']) ?>" 
                                     alt="<?= htmlspecialchars($game['name']) ?>">
                            <?php endif; ?>

                            <div class="card-body">
                                <h3><?= htmlspecialchars($game['name']) ?></h3>
                                <p>Play and earn based on time spent.</p>
                                <?php if (!empty($game['reward_per_min'])): ?>
                                <strong style="color:#00aa00;">
                                    $<?= number_format($game['reward_per_min'], 4) ?>/min
                                </strong>
                                <?php endif; ?>
                            </div>

                            <div style="padding: 0 20px 20px;">
                                <a href="#" onclick="loadGameWARE('<?= htmlspecialchars($game['crazygames_slug'] ?? '') ?>', <?= $game['id'] ?>); return false;"
                                   class="play-btn">
                                    <i class="fa-solid fa-play"></i> Play Now
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="text-align:center; grid-column:1/-1; padding:80px 20px;">
                        No games available at the moment.
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- SaneGames Scripts (Backend still uses it for now) -->
    <script src="palmframe.js"></script>
    <palmframe-widget project="w82cB8t3Jgv0" />
    <script src="main.js"></script>

    <script>
    let currentSessionId = null;

    function loadGameWARE(slug, gameId) {
        if (!slug) {
            alert("This game is not properly configured (missing slug).");
            return;
        }

        // Track play start
        fetch('track_play.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `game_id=${gameId}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                currentSessionId = data.session_id;
            }
        })
        .catch(() => {});

        // Load via SaneGames backend
        const currentUrl = new URL(window.location.href);
        currentUrl.search = `?game=${encodeURIComponent(slug)}`;
        window.location.href = currentUrl.toString();
    }

    // Auto end session when leaving
    window.addEventListener('beforeunload', function() {
        if (currentSessionId) {
            navigator.sendBeacon('end_play.php', new URLSearchParams({
                session_id: currentSessionId
            }));
        }
    });
    </script>
