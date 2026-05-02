<?php
session_start();
require_once "config/database.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

$session_id = isset($_POST['session_id']) ? (int)$_POST['session_id'] : 0;

if ($session_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid session']);
    exit;
}

$db = new Database();
$conn = $db->connect();

$user_id = $_SESSION['user_id'];

// Get session + game reward rate
$stmt = $conn->prepare("
    SELECT gs.*, g.reward_per_min 
    FROM game_sessions gs
    JOIN games g ON gs.game_id = g.id
    WHERE gs.id = ? AND gs.user_id = ? AND gs.end_time IS NULL
");
$stmt->execute([$session_id, $user_id]);
$session = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$session) {
    echo json_encode(['status' => 'error', 'message' => 'Session not found or already ended']);
    exit;
}

// Calculate duration
$start = new DateTime($session['start_time']);
$end = new DateTime();
$duration = $start->diff($end);
$seconds = ($duration->days * 24 * 60 * 60) + 
           ($duration->h * 3600) + 
           ($duration->i * 60) + 
           $duration->s;

$minutes_played = $seconds / 60;

// Calculate reward
$reward = round($minutes_played * $session['reward_per_min'], 6);

// Update session
$stmt = $conn->prepare("
    UPDATE game_sessions 
    SET end_time = NOW(), 
        duration_seconds = ? 
    WHERE id = ?
");
$stmt->execute([$seconds, $session_id]);

// Add reward to user balance
$stmt = $conn->prepare("
    UPDATE users 
    SET balance = balance + ? 
    WHERE id = ?
");
$stmt->execute([$reward, $user_id]);

echo json_encode([
    'status' => 'success',
    'message' => 'Session ended successfully',
    'minutes_played' => round($minutes_played, 2),
    'reward_earned' => $reward,
    'new_balance' => null // You can fetch and return new balance if needed
]);
