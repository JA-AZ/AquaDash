<?php

session_start();
// Check if user is logged in as admin
if (!isset($_SESSION['AdminID'])) { // Updated to match the session variable set in login_user.php
    header("Location: ../views/login.php");
    exit();
}

// Database connection
$host = 'localhost';
$dbname = 'waterdelivery';
$username = 'root';
$password = ''; // replace with your actual password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Fetch all users
$users = [];
try {
    $stmt = $pdo->query("SELECT UserID, Name, Email, Phone, Address FROM Users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching users: " . $e->getMessage());
}
?>