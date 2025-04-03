<?php
// Database connection
$host = 'localhost';
$dbname = 'waterdelivery';
$username = 'root';
$password = 'kapoyamagIT';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['Name'];
    $email = $_POST['Email'];
    $phone = $_POST['Phone'];
    $address = $_POST['Address'];
    $password = $_POST['Password'];
    $passwordConfirmation = $_POST['Password_confirmation'];

    // Confirm password match
    if ($password !== $passwordConfirmation) {
        echo "Passwords do not match.";
        exit;
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    try {
        // Check if the email already exists
        $stmt = $pdo->prepare("SELECT * FROM Users WHERE Email = :email");
        $stmt->execute(['email' => $email]);
        if ($stmt->rowCount() > 0) {
            echo "Email is already registered.";
            exit;
        }

        // Insert user
        $stmt = $pdo->prepare("INSERT INTO Users (Name, Email, Phone, Address, Password) VALUES (:Name, :Email, :Phone, :Address, :Password)");
        $stmt->execute([
            'Name' => $name,
            'Email' => $email,
            'Phone' => $phone,
            'Address' => $address,
            'Password' => $hashedPassword,
        ]);

        // Redirect after successful registration
        header('Location: ../views/login.php?success=1');
        exit;
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage()); // Log error but don't display it
        echo "An error occurred. Please try again.";
    }
}
