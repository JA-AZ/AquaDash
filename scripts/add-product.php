<?php
session_start();
if (!isset($_SESSION['AdminID'])) {
    header('Location: ../views/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productName = trim($_POST['ProductName'] ?? '');
    $pricePerUnit = floatval($_POST['PricePerUnit'] ?? 0);
    $stockQuantity = intval($_POST['StockQuantity'] ?? 0);
    $imageURL = '';

    // Handle file upload
    if (isset($_FILES['ImageFile']) && $_FILES['ImageFile']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $fileTmp = $_FILES['ImageFile']['tmp_name'];
        $fileName = basename($_FILES['ImageFile']['name']);
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            header('Location: ../views/manage-products.php?error=Invalid+image+type');
            exit();
        }
        $newName = uniqid('prod_', true) . '.' . $ext;
        $targetDir = realpath(__DIR__ . '/../assets/Products');
        if (!$targetDir) {
            header('Location: ../views/manage-products.php?error=Image+folder+not+found');
            exit();
        }
        $targetPath = $targetDir . DIRECTORY_SEPARATOR . $newName;
        if (!move_uploaded_file($fileTmp, $targetPath)) {
            header('Location: ../views/manage-products.php?error=Image+upload+failed');
            exit();
        }
        $imageURL = '../assets/Products/' . $newName;
    } else {
        header('Location: ../views/manage-products.php?error=Image+required');
        exit();
    }

    // Basic validation
    if (!$productName || $pricePerUnit <= 0 || $stockQuantity < 0 || !$imageURL) {
        header('Location: ../views/manage-products.php?error=Invalid+input');
        exit();
    }

    // Database connection
    $host = 'localhost';
    $dbname = 'waterdelivery';
    $username = 'root';
    $password = 'kapoyamagIT';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        header('Location: ../views/manage-products.php?error=DB+connection+failed');
        exit();
    }

    // Insert product
    $stmt = $pdo->prepare('INSERT INTO products (ProductName, PricePerUnit, StockQuantity, ImageURL) VALUES (?, ?, ?, ?)');
    if ($stmt->execute([$productName, $pricePerUnit, $stockQuantity, $imageURL])) {
        header('Location: ../views/manage-products.php?success=Product+added');
        exit();
    } else {
        header('Location: ../views/manage-products.php?error=Insert+failed');
        exit();
    }
} else {
    header('Location: ../views/manage-products.php');
    exit();
} 