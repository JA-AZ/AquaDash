<?php
session_start();

if (!isset($_SESSION['UserID'])) {
    http_response_code(403); // Forbidden if not logged in
    exit('Unauthorized');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderID = intval($_POST['order_id']);

    // Connect to database
    $conn = new mysqli("localhost", "root", "", "waterdelivery");

    if ($conn->connect_error) {
        http_response_code(500); // Internal Server Error
        exit('Database connection failed');
    }

    // Make sure the order belongs to the logged-in user
    $stmt = $conn->prepare("DELETE FROM orders WHERE OrderID = ? AND UserID = ?");
    $stmt->bind_param("ii", $orderID, $_SESSION['UserID']);

    if ($stmt->execute()) {
        echo 'success';
    } else {
        http_response_code(500);
        echo 'Failed to delete item.';
    }

    $stmt->close();
    $conn->close();
} else {
    http_response_code(405); // Method not allowed
}
?>
