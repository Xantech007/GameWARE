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
    SELECT * FROM game_sessions 
    WHERE id = ? AND user_id = ? AND claimed = 0
");
$stmt->execute([$session_id, $user_id]);
$session = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$session) {
    echo json_encode(["message" => "Already claimed or invalid"]);
    exit;
}

/* ADD BALANCE */
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
    "message" => "Earnings claimed successfully!"
]);
