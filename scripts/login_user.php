<?php
session_start();

// Database connection
$host = 'localhost';
$dbname = 'waterdelivery';
$username = 'root';
$password = 'kapoyamagIT';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Get form inputs
$email = $_POST['Email'];
$password = $_POST['Password'];

// Prepare and execute the select statement to find the user
$stmt = $pdo->prepare("SELECT * FROM Users WHERE Email = :email");
$stmt->execute(['email' => $email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if user exists and verify password
if ($user && password_verify($password, $user['Password'])) {
    $_SESSION['UserID'] = $user['UserID'];
    $_SESSION['Name'] = $user['Name'];

    // Redirect to the user dashboard or home page
    header('Location: ../views/shop.php');
    exit;
} else {
    // If login fails
    $_SESSION['login_error'] = "Invalid email or password.";
    header('Location: ../views/login.php');
    exit;
}
