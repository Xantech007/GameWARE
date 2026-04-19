<?php
require_once "config/database.php";

$db = new Database();
$conn = $db->connect();

// fetch active games
$stmt = $conn->prepare("SELECT * FROM games WHERE status = 1 ORDER BY id DESC");
$stmt->execute();
$games = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Game Center</title>

<style>
body{
    margin:0;
    font-family:Arial;
    background:#111;
    color:#fff;
    text-align:center;
}

h1{
    margin:20px;
}

.grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
    gap:20px;
    padding:20px;
}

.card{
    background:#1c1c1c;
    border-radius:10px;
    overflow:hidden;
    transition:0.3s;
    box-shadow:0 0 10px rgba(0,0,0,0.5);
}

.card:hover{
    transform:scale(1.05);
}

.card img{
    width:100%;
    height:140px;
    object-fit:cover;
}

.card h3{
    margin:10px;
}

.play-btn{
    display:block;
    margin:10px;
    padding:10px;
    background:#00c3ff;
    color:#000;
    text-decoration:none;
    border-radius:5px;
    font-weight:bold;
}
</style>
</head>

<body>

<h1>🎮 Game Center</h1>

<div class="grid">
<?php foreach($games as $game): ?>
    <div class="card">
        <img src="<?php echo $game['thumbnail']; ?>" alt="">
        <h3><?php echo $game['name']; ?></h3>
        <a class="play-btn" href="<?php echo $game['file_path']; ?>">Play</a>
    </div>
<?php endforeach; ?>
</div>

</body>
</html>
