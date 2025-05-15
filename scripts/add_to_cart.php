<?php
session_start();

if (!isset($_SESSION['UserID'])) {
    echo "User not logged in";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userID = $_SESSION['UserID'];
    $productID = intval($_POST['productID']);
    $quantity = intval($_POST['quantity']);
    $totalAmount = floatval($_POST['totalAmount']);

    // Database connection
    $conn = new mysqli("localhost", "root", "kapoyamagIT", "waterdelivery");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Insert into orders table
    $stmt = $conn->prepare("INSERT INTO orders (UserID, ProductID, Quantity, TotalAmount, Status) VALUES (?, ?, ?, ?, 'cart')");
    $stmt->bind_param("iiid", $userID, $productID, $quantity, $totalAmount);

    if ($stmt->execute()) {
        echo "Order added successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request method";
}
