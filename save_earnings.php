<?php
session_start();
require_once "config/database.php";

$data = json_decode(file_get_contents("php://input"), true);

if(!isset($_SESSION['user_id'])) exit;

$user_id = $_SESSION['user_id'];
$game = $data['game'];
$amount = $data['amount'];

$db = new Database();
$conn = $db->connect();

// add balance
$conn->prepare("UPDATE users SET balance = balance + ? WHERE id=?")
     ->execute([$amount,$user_id]);

// log earning
$conn->prepare("INSERT INTO earnings(user_id,game,amount) VALUES(?,?,?)")
     ->execute([$user_id,$game,$amount]);
