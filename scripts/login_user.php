<?php
session_start();

// Database connection
$host = 'localhost';
$dbname = 'waterdelivery';
$username = 'root';
$password = 'kapoyamagIT'; //replace with your actual password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Get form inputs
$email = $_POST['Email'];
$password = $_POST['Password'];

// Check in Admin table
$stmt = $pdo->prepare("SELECT * FROM Admin WHERE Email = :email");
$stmt->execute(['email' => $email]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if ($admin) {
    if (password_verify($password, $admin['Password'])) {
        // Admin login successful
        $_SESSION['AdminID'] = $admin['AdminID'];
        $_SESSION['Name'] = $admin['Name'];

        // Redirect to the admin dashboard
        header('Location: ../views/admin-dashboard.php');
        exit;
    } else {
        // Invalid password for admin
        $_SESSION['login_error'] = "Invalid password.";
        header('Location: ../views/login.php');
        exit;
    }
}

// Check in Users table
$stmt = $pdo->prepare("SELECT * FROM Users WHERE Email = :email");
$stmt->execute(['email' => $email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    if (password_verify($password, $user['Password'])) {
        // User login successful
        $_SESSION['UserID'] = $user['UserID'];
        $_SESSION['Name'] = $user['Name'];

        // Redirect to the user dashboard or home page
        header('Location: ../views/shop.php');
        exit;
    } else {
        // Invalid password for user
        $_SESSION['login_error'] = "Invalid password.";
        header('Location: ../views/login.php');
        exit;
    }
}

// If no match is found in either table
$_SESSION['login_error'] = "Invalid email or password.";
header('Location: ../views/login.php');
exit;