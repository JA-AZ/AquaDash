<?php
session_start();
header('Content-Type: application/json');

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['UserID'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$userID = $_SESSION['UserID'];
$conn = new mysqli('localhost', 'root', '', 'waterdelivery');

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'DB connection failed: ' . $conn->connect_error]);
    exit;
}

// Get and sanitize input
$addressID = intval($_POST['address_id'] ?? 0);
$recipientName = trim($_POST['recipient_name'] ?? '');
$phoneNumber = trim($_POST['phone_number'] ?? '');
$streetAddress = trim($_POST['street_address'] ?? '');
$barangay = trim($_POST['barangay'] ?? '');
$city = trim($_POST['city'] ?? '');
$province = trim($_POST['province'] ?? '');
$postalCode = trim($_POST['postal_code'] ?? '');
$isDefault = isset($_POST['is_default']) ? 1 : 0;

// Validate input
if (!$addressID || !$recipientName || !$phoneNumber || !$streetAddress || !$barangay || !$city || !$province || !$postalCode) {
    echo json_encode(['success' => false, 'error' => 'All fields are required']);
    exit;
}

try {
    // Start transaction
    $conn->begin_transaction();

    // Check ownership
    $check = $conn->prepare('SELECT AddressID FROM user_addresses WHERE AddressID = ? AND UserID = ?');
    if (!$check) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    $check->bind_param('ii', $addressID, $userID);
    if (!$check->execute()) {
        throw new Exception('Execute failed: ' . $check->error);
    }
    
    $check->store_result();
    if ($check->num_rows === 0) {
        throw new Exception('Address not found or not owned by user');
    }
    $check->close();

    // If setting as default, unset other defaults
    if ($isDefault) {
        $unsetDefault = $conn->prepare('UPDATE user_addresses SET IsDefault = 0 WHERE UserID = ? AND AddressID != ?');
        if (!$unsetDefault) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        
        $unsetDefault->bind_param('ii', $userID, $addressID);
        if (!$unsetDefault->execute()) {
            throw new Exception('Execute failed: ' . $unsetDefault->error);
        }
        $unsetDefault->close();
    }

    // Update address
    $update = $conn->prepare('UPDATE user_addresses SET 
        RecipientName = ?,
        PhoneNumber = ?,
        StreetAddress = ?,
        Barangay = ?,
        City = ?,
        Province = ?,
        PostalCode = ?,
        IsDefault = ?,
        DateModified = CURRENT_TIMESTAMP
        WHERE AddressID = ? AND UserID = ?');

    if (!$update) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }

    $update->bind_param('sssssssiis', 
        $recipientName,
        $phoneNumber,
        $streetAddress,
        $barangay,
        $city,
        $province,
        $postalCode,
        $isDefault,
        $addressID,
        $userID
    );

    if (!$update->execute()) {
        throw new Exception('Execute failed: ' . $update->error);
    }

    if ($update->affected_rows === 0) {
        throw new Exception('No changes made to the address');
    }

    $update->close();

    // Commit transaction
    $conn->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    error_log('Address edit error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close(); 