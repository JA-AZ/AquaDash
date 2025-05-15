<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['AdminID'])) {
    echo json_encode(['success' => false, 'error' => 'Not authorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userID = intval($_POST['UserID'] ?? 0);
    $name = trim($_POST['Name'] ?? '');
    $email = trim($_POST['Email'] ?? '');
    $phone = trim($_POST['Phone'] ?? '');
    $address = trim($_POST['Address'] ?? '');

    if (!$userID || !$name || !$email || !$phone || !$address) {
        echo json_encode(['success' => false, 'error' => 'All fields are required.']);
        exit();
    }

    $host = 'localhost';
    $dbname = 'waterdelivery';
    $username = 'root';
    $password = 'kapoyamagIT';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'DB connection failed']);
        exit();
    }

    $stmt = $pdo->prepare('UPDATE Users SET Name=?, Email=?, Phone=?, Address=? WHERE UserID=?');
    if ($stmt->execute([$name, $email, $phone, $address, $userID])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Update failed']);
    }
    exit();
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit();
} 