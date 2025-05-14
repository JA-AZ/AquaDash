<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['UserID'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}
$userID = $_SESSION['UserID'];
$conn = new mysqli('localhost', 'root', '', 'waterdelivery');
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'DB connection failed']);
    exit;
}
$recipientName = trim($_POST['recipient_name'] ?? '');
$phoneNumber = trim($_POST['phone_number'] ?? '');
$streetAddress = trim($_POST['street_address'] ?? '');
$barangay = trim($_POST['barangay'] ?? '');
$city = trim($_POST['city'] ?? '');
$province = trim($_POST['province'] ?? '');
$postalCode = trim($_POST['postal_code'] ?? '');
$isDefault = isset($_POST['is_default']) ? 1 : 0;
if (!$recipientName || !$phoneNumber || !$streetAddress || !$barangay || !$city || !$province || !$postalCode) {
    echo json_encode(['success' => false, 'error' => 'All fields required']);
    exit;
}
if ($isDefault) {
    $conn->query("UPDATE user_addresses SET IsDefault = 0 WHERE UserID = $userID");
}
$stmt = $conn->prepare("INSERT INTO user_addresses (UserID, RecipientName, PhoneNumber, StreetAddress, Barangay, City, Province, PostalCode, IsDefault) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param('isssssssi', $userID, $recipientName, $phoneNumber, $streetAddress, $barangay, $city, $province, $postalCode, $isDefault);
if ($stmt->execute()) {
    $addressID = $stmt->insert_id;
    echo json_encode(['success' => true, 'address_id' => $addressID]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}
$stmt->close();
$conn->close(); 