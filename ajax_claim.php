<?php
session_start();
require_once "config/database.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["message" => "Not logged in"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$user_id = $_SESSION['user_id'];
$session_id = (int)$data['session_id'];

$db = new Database();
$conn = $db->connect();
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* GET SESSION */
$stmt = $conn->prepare("
    SELECT * FROM game_sessions 
    WHERE id = ? AND user_id = ? AND claimed = 0
");
$stmt->execute([$session_id, $user_id]);
$session = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$session) {
    echo json_encode(["message" => "Already claimed or invalid"]);
    exit;
}

if ($session['amount_earned'] <= 0) {
    echo json_encode(["message" => "No earnings to claim"]);
    exit;
}

/* ADD EXACT STORED AMOUNT */
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
$stmt->execute([$session_id]);

echo json_encode([
    "message" => "✅ $" . number_format($session['amount_earned'], 2) . " added to your balance"
]);
