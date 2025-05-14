<?php
session_start();
if (!isset($_SESSION['AdminID'])) {
    echo json_encode(['success'=>false,'error'=>'Not authorized']);
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = intval($_POST['ProductID'] ?? 0);
    if (!$productId) {
        echo json_encode(['success'=>false,'error'=>'Invalid ProductID']);
        exit();
    }
    $host = 'localhost';
    $dbname = 'waterdelivery';
    $username = 'root';
    $password = '';
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        echo json_encode(['success'=>false,'error'=>'DB connection failed']);
        exit();
    }
    $stmt = $pdo->prepare('DELETE FROM products WHERE ProductID=?');
    if ($stmt->execute([$productId])) {
        echo json_encode(['success'=>true]);
    } else {
        echo json_encode(['success'=>false,'error'=>'Delete failed']);
    }
    exit();
} else {
    echo json_encode(['success'=>false,'error'=>'Invalid request']);
    exit();
} 