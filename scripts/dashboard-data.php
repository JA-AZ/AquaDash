<?php

session_start();
// Check if user is logged in as admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../views/login.php");
    exit();
}


// Database connection
$host = 'localhost'; // Replace with your database host
$dbname = 'waterdelivery'; // Replace with your database name
$username = 'root'; // Replace with your database username
$password = 'kapoyamagIT'; // Replace with your database password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Fetch total users
$totalUsers = 0;
try {
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM Users");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalUsers = $result['total'];
} catch (PDOException $e) {
    error_log("Error fetching total users: " . $e->getMessage());
}

// Fetch total products
$totalProducts = 0;
try {
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM Products");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalProducts = $result['total'];
} catch (PDOException $e) {
    error_log("Error fetching total products: " . $e->getMessage());
}

// Fetch total orders
$totalOrders = 0;
try {
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM Orders");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalOrders = $result['total'];
} catch (PDOException $e) {
    error_log("Error fetching total orders: " . $e->getMessage());
}
?>