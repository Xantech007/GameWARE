<?php
session_start();
require_once "../config/database.php";

$db = new Database();
$conn = $db->connect();

$duration = (int)$_POST['duration'];

$settings = $conn->query("SELECT earn_rate,currency FROM settings WHERE id=1")->fetch();

$amount = $duration * $settings['earn_rate'];

if(isset($_SESSION['user_id'])){
    $user_id = $_SESSION['user_id'];

    // update balance
    $stmt = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id=?");
    $stmt->execute([$amount,$user_id]);

    // transaction
    $stmt = $conn->prepare("INSERT INTO transactions (user_id,amount,type) VALUES (?,?, 'game')");
    $stmt->execute([$user_id,$amount]);

    echo json_encode([
        "status"=>"credited",
        "amount"=>$amount,
        "currency"=>$settings['currency']
    ]);

} else {
    echo json_encode([
        "status"=>"guest",
        "amount"=>$amount,
        "currency"=>$settings['currency']
    ]);
}
