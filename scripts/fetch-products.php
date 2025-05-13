<?php
// Only allow admin
session_start();
if (!isset($_SESSION['AdminID'])) {
    header('Location: ../views/login.php');
    exit();
}

// Fetch product images from assets/Products
$productImages = [];
$imgDir = realpath(__DIR__ . '/../assets/Products');
if ($imgDir && is_dir($imgDir)) {
    foreach (glob($imgDir . '/*.{jpg,jpeg,png,gif}', GLOB_BRACE) as $img) {
        $productImages[] = '../assets/Products/' . basename($img);
    }
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
    die("Database connection failed: " . $e->getMessage());
}

// Pagination
$perPage = 6;
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($currentPage - 1) * $perPage;

// Fetch total products
$stmt = $pdo->query('SELECT COUNT(*) FROM products');
$totalProducts = $stmt->fetchColumn();
$totalPages = ceil($totalProducts / $perPage);

// Fetch paginated products
$stmt = $pdo->prepare('SELECT * FROM products ORDER BY ProductID DESC LIMIT :offset, :perPage');
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':perPage', $perPage, PDO::PARAM_INT);
$stmt->execute();
$paginatedProducts = $stmt->fetchAll(PDO::FETCH_ASSOC); 