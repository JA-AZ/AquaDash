<?php
session_start();

if (!isset($_SESSION['UserID'])) {
    echo 'error';
    exit;
}

if (isset($_POST['order_id']) && isset($_POST['quantity'])) {
    $orderId = intval($_POST['order_id']);
    $quantity = intval($_POST['quantity']);

    // Database connection
    $conn = new mysqli("localhost", "root", "", "waterdelivery");

    if ($conn->connect_error) {
        echo 'error';
        exit;
    }

    // Fetch the PricePerUnit based on the order
    $sql = "SELECT p.PricePerUnit
            FROM orders o
            JOIN products p ON o.ProductID = p.ProductID
            WHERE o.OrderID = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $pricePerUnit = $row['PricePerUnit'];
        $totalAmount = $pricePerUnit * $quantity;

        // Update the order
        $updateSql = "UPDATE orders SET Quantity = ?, TotalAmount = ? WHERE OrderID = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("idi", $quantity, $totalAmount, $orderId);

        if ($updateStmt->execute()) {
            echo 'success';
        } else {
            echo 'error';
        }

        $updateStmt->close();
    } else {
        echo 'error';
    }

    $stmt->close();
    $conn->close();
} else {
    echo 'error';
}
?>
