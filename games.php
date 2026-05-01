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

<!-- FONT AWESOME -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
* { box-sizing: border-box; }

.container {
    max-width: 1200px;
    margin: auto;
    padding: 15px;
}

.page-header {
    text-align: center;
    margin-top: 40px;
    margin-bottom: 25px;
}
.page-header h1 {
    font-size: 32px;
    margin-bottom: 8px;
}
.page-header p {
    color: #555;
    font-size: 17px;
}

.notice {
    background: #fff3cd;
    color: #856404;
    padding: 14px 20px;
    border-radius: 10px;
    margin-bottom: 30px;
    text-align: center;
    font-size: 15.5px;
}

.grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 22px;
}

.card {
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    transition: 0.3s ease;
    box-shadow: 0 8px 25px rgba(0,0,0,0.07);
    display: flex;
    flex-direction: column;
}
.card:hover {
    transform: translateY(-6px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.12);
}
.card img {
    width: 100%;
    height: 170px;
    object-fit: cover;
}

.card-body {
    padding: 18px 20px;
    flex: 1;
}
.card h3 {
    margin: 8px 0 12px;
    font-size: 20px;
}
.card p {
    color: #666;
    font-size: 15px;
}

.badge {
    display: inline-block;
    padding: 6px 12px;
    background: #e0f2fe;
    color: #0369a1;
    border-radius: 8px;
    font-size: 13.5px;
    margin-top: 10px;
}

.play-btn {
    display: block;
    width: 100%;
    padding: 14px;
    text-align: center;
    background: #00aaff;
    color: #fff;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    font-size: 16px;
    transition: 0.3s;
    margin-top: auto;
}
.play-btn:hover {
    background: #0088cc;
}

.icon-blue { color: #00aaff; }

@media (max-width: 768px) {
    .grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="container">

    <!-- HEADER -->
    <div class="page-header">
        <h1><i class="fa-solid fa-gamepad icon-blue"></i> Game Center</h1>
        <p>Play exciting games and earn real money in USD</p>
    </div>

    <!-- LOGIN NOTICE -->
    <?php if (!isset($_SESSION['user_id'])): ?>
        <div class="notice">
            <i class="fa-solid fa-circle-info"></i>
            <strong>Login is required</strong> to earn and claim rewards.
        </div>
    <?php endif; ?>

    <!-- GAMES GRID -->
    <div class="grid">
        <?php if (count($games) > 0): ?>
            <?php foreach ($games as $game): ?>
                <div class="card">
                    <img src="<?php echo htmlspecialchars($game['thumbnail']); ?>" 
                         alt="<?php echo htmlspecialchars($game['name']); ?>">
                    
                    <div class="card-body">
                        <h3><?php echo htmlspecialchars($game['name']); ?></h3>

                        <p>Play and earn based on time spent.</p>

                        <span class="badge">
                            <i class="fa-solid fa-coins"></i> 
                            $<?php echo number_format($game['reward_per_min'], 4); ?>/min
                        </span>
                    </div>

                    <div style="padding: 18px 20px 20px;">

                        <?php if (isset($_SESSION['user_id'])): ?>
                            <!-- PLAY VIA play.php -->
                            <a class="play-btn" 
                               href="play.php?game_id=<?php echo $game['id']; ?>">
                                <i class="fa-solid fa-play"></i> Play Now
                            </a>
                        <?php else: ?>
                            <!-- FORCE LOGIN -->
                            <a class="play-btn" href="login.php">
                                <i class="fa-solid fa-lock"></i> Login to Play
                            </a>
                        <?php endif; ?>

                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align:center; grid-column: 1 / -1; padding: 60px;">
                No games available at the moment.
            </p>
        <?php endif; ?>
    </div>

</div>

<?php include "inc/footer.php"; ?>
