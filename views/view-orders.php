<?php
session_start();

if (!isset($_SESSION['AdminID'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$host = 'localhost';
$dbname = 'waterdelivery';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['new_status'])) {
    $orderId = $_POST['order_id'];
    $newStatusId = $_POST['new_status'];  // Getting the StatusID from the form

    // Step 1: Check if the new StatusID exists in the orderstatus table
    $statusCheckStmt = $pdo->prepare("SELECT StatusID FROM orderstatus WHERE StatusID = :statusId");
    $statusCheckStmt->execute([':statusId' => $newStatusId]);
    $statusExists = $statusCheckStmt->fetch(PDO::FETCH_ASSOC);

    if ($statusExists) {
        // Step 2: If the StatusID exists, update the order status
        $updateStmt = $pdo->prepare("UPDATE confirmed_orders SET StatusID = :statusId WHERE ConfirmedOrderID = :orderId");
        $updateStmt->execute([':statusId' => $newStatusId, ':orderId' => $orderId]);

        // Step 3: Redirect to the same page to show the updated status
        header("Location: view-orders.php");
        exit();
    } else {
        echo "Invalid status selected. Please choose a valid status.";
    }
}

// Fetch orders with status information
try {
    $stmt = $pdo->query("
        SELECT 
            co.ConfirmedOrderID AS OrderID,
            co.UserID,
            co.OrderDate,
            os.StatusName AS OrderStatus,
            p.ProductName,
            oi.Quantity,
            oi.PricePerUnit AS Price,
            (oi.Quantity * oi.PricePerUnit) AS TotalItemPrice
        FROM confirmed_orders co
        JOIN order_items oi ON co.ConfirmedOrderID = oi.ConfirmedOrderID
        JOIN products p ON oi.ProductID = p.ProductID
        LEFT JOIN orderstatus os ON co.StatusID = os.StatusID
        ORDER BY co.OrderDate DESC
    ");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching orders: " . $e->getMessage());
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View All Orders</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="admin-style.css">
</head>
<body>
<div class="container">
    <div class="sidebar">
        <div class="logo-container">
            <img src="../assets/Aqua_Dash.png" alt="Aqua Dash Logo" class="sidebar-logo">
            <h2>Admin Panel</h2>
        </div>
        <ul>
            <li><a href="admin-dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="manage-users.php"><i class="fas fa-users"></i> Manage Users</a></li>
            <li><a href="manage-products.php"><i class="fas fa-box"></i> Manage Products</a></li>
            <li><a href="view-orders.php" class="active"><i class="fas fa-shopping-cart"></i> View Orders</a></li>
            <li><a href="index.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>All Orders</h1>
        </div>

        <div class="orders-table">
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>User ID</th>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Order Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($orders) > 0): ?>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?= htmlspecialchars($order['OrderID']) ?></td>
                                <td><?= htmlspecialchars($order['UserID']) ?></td>
                                <td><?= htmlspecialchars($order['ProductName'] ?? '') ?></td>
                                <td><?= htmlspecialchars($order['Quantity'] ?? '') ?></td>
                                <td>₱<?= number_format((float)$order['Price'], 2) ?></td>
                                <td>₱<?= number_format((float)$order['TotalItemPrice'], 2) ?></td>
                                <td><?= htmlspecialchars($order['OrderStatus'] ?? '') ?></td>
                                <td><?= htmlspecialchars($order['OrderDate'] ?? '') ?></td>
                                <td>
                                    <form method="POST" class="status-form">
                                        <input type="hidden" name="order_id" value="<?= $order['OrderID'] ?>">

                                        <select name="new_status" class="status-select">
                                            <?php
                                            $statusStmt = $pdo->query("SELECT * FROM orderstatus");
                                            $statusOptions = $statusStmt->fetchAll(PDO::FETCH_ASSOC);
                                            foreach ($statusOptions as $status) {
                                                $selected = ($status['StatusName'] === $order['OrderStatus']) ? 'selected' : '';
                                                echo "<option value='{$status['StatusID']}' $selected>{$status['StatusName']}</option>";
                                            }
                                            ?>
                                        </select>

                                        <button type="submit" class="update-btn">Update</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="9" style="text-align:center;">No orders found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.orders-table {
    margin-top: 30px;
    overflow-x: auto;
}
.orders-table table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}
.orders-table th, .orders-table td {
    padding: 16px;
    text-align: left;
    border-bottom: 1px solid #e0e0e0;
}
.orders-table th {
    background-color: #3498db;
    color: #fff;
}
.orders-table tr:hover {
    background-color: #f5f5f5;
}
.status-select {
    padding: 6px 10px;
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 0.95rem;
}
.update-btn {
    background-color: #3498db;
    color: #fff;
    padding: 8px 14px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    margin-top: 6px;
}
.update-btn:hover {
    background-color: #217dbb;
}
</style>
</body>
</html>
