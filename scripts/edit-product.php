<?php
session_start();
if (!isset($_SESSION['AdminID'])) {
    echo json_encode(['success'=>false,'error'=>'Not authorized']);
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = intval($_POST['ProductID'] ?? 0);
    $productName = trim($_POST['ProductName'] ?? '');
    $pricePerUnit = floatval($_POST['PricePerUnit'] ?? 0);
    $stockQuantity = intval($_POST['StockQuantity'] ?? 0);
    $currentImageURL = trim($_POST['CurrentImageURL'] ?? '');
    $imageURL = $currentImageURL;

    // Handle file upload if provided
    if (isset($_FILES['ImageFile']) && $_FILES['ImageFile']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $fileTmp = $_FILES['ImageFile']['tmp_name'];
        $fileName = basename($_FILES['ImageFile']['name']);
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            echo json_encode(['success'=>false,'error'=>'Invalid image type']);
            exit();
        }
        $newName = uniqid('prod_', true) . '.' . $ext;
        $targetDir = realpath(__DIR__ . '/../assets/Products');
        if (!$targetDir) {
            echo json_encode(['success'=>false,'error'=>'Image folder not found']);
            exit();
        }
        $targetPath = $targetDir . DIRECTORY_SEPARATOR . $newName;
        if (!move_uploaded_file($fileTmp, $targetPath)) {
            echo json_encode(['success'=>false,'error'=>'Image upload failed']);
            exit();
        }
        $imageURL = '../assets/Products/' . $newName;
        // Optionally delete old image if it exists and is not used by other products
        if ($currentImageURL && strpos($currentImageURL, '../assets/Products/') === 0) {
            $oldPath = realpath(__DIR__ . '/../' . substr($currentImageURL, 3));
            if ($oldPath && file_exists($oldPath)) {
                @unlink($oldPath);
            }
        }
    }
    if (!$productId || !$productName || $pricePerUnit <= 0 || $stockQuantity < 0 || !$imageURL) {
        echo json_encode(['success'=>false,'error'=>'Invalid input']);
        exit();
    }
    $host = 'localhost';
    $dbname = 'waterdelivery';
    $username = 'root';
    $password = 'kapoyamagIT';
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        echo json_encode(['success'=>false,'error'=>'DB connection failed']);
        exit();
    }
    $stmt = $pdo->prepare('UPDATE products SET ProductName=?, PricePerUnit=?, StockQuantity=?, ImageURL=? WHERE ProductID=?');
    if ($stmt->execute([$productName, $pricePerUnit, $stockQuantity, $imageURL, $productId])) {
        echo json_encode(['success'=>true]);
    } else {
        echo json_encode(['success'=>false,'error'=>'Update failed']);
    }
    exit();
} else {
    echo json_encode(['success'=>false,'error'=>'Invalid request']);
    exit();
} 