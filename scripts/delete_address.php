<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['UserID'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}
$userID = $_SESSION['UserID'];
$conn = new mysqli('localhost', 'root', 'kapoyamagIT', 'waterdelivery');
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'DB connection failed']);
    exit;
}
$addressID = intval($_POST['address_id'] ?? 0);
if (!$addressID) {
    echo json_encode(['success' => false, 'error' => 'No address ID']);
    exit;
}
// Check ownership
$check = $conn->prepare('SELECT AddressID FROM user_addresses WHERE AddressID = ? AND UserID = ?');
$check->bind_param('ii', $addressID, $userID);
$check->execute();
$check->store_result();
if ($check->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Address not found']);
    exit;
}
$check->close();
$stmt = $conn->prepare('DELETE FROM user_addresses WHERE AddressID = ? AND UserID = ?');
$stmt->bind_param('ii', $addressID, $userID);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}
$stmt->close();
$conn->close(); 