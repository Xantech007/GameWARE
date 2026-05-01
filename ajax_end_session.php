<?php
session_start();
require_once "config/database.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($_SESSION['user_id'])) exit;

$user_id = $_SESSION['user_id'];
$session_id = (int)$data['session_id'];

$db = new Database();
$conn = $db->connect();

/* GET SESSION */
$stmt = $conn->prepare("
    SELECT gs.*, g.reward_per_min 
    FROM game_sessions gs
    JOIN games g ON gs.game_id = g.id
    WHERE gs.id = ? AND gs.user_id = ?
");
$stmt->execute([$session_id, $user_id]);
$session = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$session) exit;

/* CALCULATE (SERVER SIDE!) */
$start = strtotime($session['start_time']);
$end = time();

$duration = $end - $start;
$amount = ($duration / 60) * $session['reward_per_min'];

/* UPDATE */
$stmt = $conn->prepare("
    UPDATE game_sessions 
    SET end_time = NOW(),
        duration = ?,
        amount_earned = ?
    WHERE id = ?
");
$stmt->execute([$duration, $amount, $session_id]);

echo json_encode([
    "amount" => number_format($amount, 2)
]);
