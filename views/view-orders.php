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
$password = 'kapoyamagIT'; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['new_status'])) {
    $orderId = $_POST['order_id'];
    $newStatus = $_POST['new_status']; // Get the ENUM value directly

    // List of allowed ENUM values
    $allowedStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    if (in_array($newStatus, $allowedStatuses)) {
        // Update the OrderStatus column directly
        $updateStmt = $pdo->prepare("UPDATE confirmed_orders SET OrderStatus = :status WHERE ConfirmedOrderID = :orderId");
        $updateStmt->execute([':status' => $newStatus, ':orderId' => $orderId]);
        header("Location: view-orders.php");
        exit();
    } else {
        echo "Invalid status selected. Please choose a valid status.";
    }
}

// Fetch orders with customer name and calculate total per order
try {
    // First get unique orders with customer information
    $stmt = $pdo->query("
        SELECT 
            co.ConfirmedOrderID AS OrderID,
            u.Name AS CustomerName,
            DATE_FORMAT(co.OrderDate, '%M %d, %Y') AS FormattedOrderDate,
            co.OrderStatus
        FROM confirmed_orders co
        JOIN users u ON co.UserID = u.UserID
        ORDER BY co.OrderDate DESC
    ");
    $uniqueOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Prepare array to store order details
    $ordersWithTotals = [];
    
    // For each unique order, calculate the total and get items
    foreach ($uniqueOrders as $order) {
        $orderID = $order['OrderID'];
        
        // Calculate total for this order
        $totalStmt = $pdo->prepare("
            SELECT SUM(Quantity * PricePerUnit) AS OrderTotal
            FROM order_items
            WHERE ConfirmedOrderID = :orderId
        ");
        $totalStmt->execute([':orderId' => $orderID]);
        $totalResult = $totalStmt->fetch(PDO::FETCH_ASSOC);
        
        // Get all items for this order
        $itemsStmt = $pdo->prepare("
            SELECT 
                p.ProductName,
                oi.Quantity,
                oi.PricePerUnit AS Price,
                (oi.Quantity * oi.PricePerUnit) AS ItemTotal
            FROM order_items oi
            JOIN products p ON oi.ProductID = p.ProductID
            WHERE oi.ConfirmedOrderID = :orderId
        ");
        $itemsStmt->execute([':orderId' => $orderID]);
        $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Add order to the array with all needed information
        $ordersWithTotals[] = [
            'OrderID' => $orderID,
            'CustomerName' => $order['CustomerName'],
            'FormattedOrderDate' => $order['FormattedOrderDate'],
            'OrderStatus' => $order['OrderStatus'],
            'OrderTotal' => $totalResult['OrderTotal'],
            'Items' => $items
        ];
    }
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
    <style>
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: #fff;
            margin: 10% auto;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            width: 80%;
            max-width: 800px;
            position: relative;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .close-modal {
            position: absolute;
            right: 20px;
            top: 15px;
            font-size: 24px;
            cursor: pointer;
            color: #555;
        }
        
        .close-modal:hover {
            color: #000;
        }
        
        /* Table styles */
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
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .orders-table th, .orders-table td {
            padding: 16px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .orders-table th {
            background-color: #3498db;
            color: #fff;
            font-weight: 600;
        }
        
        .orders-table tr:hover {
            background-color: #f5f5f5;
        }
        
        /* Status badge styles */
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            text-transform: capitalize;
            font-size: 0.85rem;
            display: inline-block;
            min-width: 90px;
            text-align: center;
        }
        
        .status-pending {
            background-color: #ffeeba;
            color: #856404;
        }
        
        .status-processing {
            background-color: #b8daff;
            color: #004085;
        }
        
        .status-shipped {
            background-color: #c3e6cb;
            color: #155724;
        }
        
        .status-delivered {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        /* Status select styling */
        .status-select {
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 0.95rem;
            margin-right: 10px;
            background-color: #f9f9f9;
        }
        
        /* Action buttons */
        .action-buttons {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .update-btn {
            background-color: #3498db;
            color: #fff;
            padding: 8px 14px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.2s;
        }
        
        .update-btn:hover {
            background-color: #217dbb;
        }
        
        .details-btn {
            background-color: #6c757d;
            color: #fff;
            padding: 8px 14px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.2s;
        }
        
        .details-btn:hover {
            background-color: #5a6268;
        }
        
        /* Order details styling */
        .order-details-header {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .order-items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        .order-items-table th, .order-items-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .order-items-table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        
        .order-total {
            margin-top: 20px;
            text-align: right;
            font-weight: 700;
            font-size: 1.1rem;
        }
    </style>
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
                        <th>Customer Name</th>
                        <th>Total</th>
                        <th>Date Ordered</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($ordersWithTotals) > 0): ?>
                        <?php foreach ($ordersWithTotals as $order): ?>
                            <tr>
                                <td>#<?= htmlspecialchars($order['OrderID']) ?></td>
                                <td><?= htmlspecialchars($order['CustomerName']) ?></td>
                                <td>₱<?= number_format((float)$order['OrderTotal'], 2) ?></td>
                                <td><?= htmlspecialchars($order['FormattedOrderDate']) ?></td>
                                <td>
                                    <span class="status-badge status-<?= $order['OrderStatus'] ?>">
                                        <?= ucfirst(htmlspecialchars($order['OrderStatus'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <form method="POST" class="status-form">
                                            <input type="hidden" name="order_id" value="<?= $order['OrderID'] ?>">
                                            <select name="new_status" class="status-select">
                                                <?php
                                                $statusOptions = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
                                                foreach ($statusOptions as $status) {
                                                    $selected = ($status === $order['OrderStatus']) ? 'selected' : '';
                                                    echo "<option value='{$status}' $selected>" . ucfirst($status) . "</option>";
                                                }
                                                ?>
                                            </select>
                                            <button type="submit" class="update-btn">Update</button>
                                        </form>
                                        <button class="details-btn" onclick="showOrderDetails(<?= $order['OrderID'] ?>)">
                                            Details
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align:center;">No orders found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal for Order Details -->
<div id="orderDetailsModal" class="modal">
    <div class="modal-content">
        <span class="close-modal" onclick="closeOrderDetails()">&times;</span>
        <div id="orderDetailsContent"></div>
    </div>
</div>

<script>
    // Function to show order details modal
    function showOrderDetails(orderId) {
        // Find the order details from PHP-generated data
        const orders = <?= json_encode($ordersWithTotals) ?>;
        const order = orders.find(o => o.OrderID == orderId);
        
        if (order) {
            // Populate modal content
            let content = `
                <div class="order-details-header">
                    <h2>Order #${order.OrderID} Details</h2>
                    <p><strong>Customer:</strong> ${order.CustomerName}</p>
                    <p><strong>Date Ordered:</strong> ${order.FormattedOrderDate}</p>
                    <p><strong>Status:</strong> <span class="status-badge status-${order.OrderStatus}">${order.OrderStatus.charAt(0).toUpperCase() + order.OrderStatus.slice(1)}</span></p>
                </div>
                
                <h3>Order Items</h3>
                <table class="order-items-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            
            // Add all items
            order.Items.forEach(item => {
                content += `
                    <tr>
                        <td>${item.ProductName}</td>
                        <td>${item.Quantity}</td>
                        <td>₱${parseFloat(item.Price).toFixed(2)}</td>
                        <td>₱${parseFloat(item.ItemTotal).toFixed(2)}</td>
                    </tr>
                `;
            });
            
            // Close table and add total
            content += `
                    </tbody>
                </table>
                <div class="order-total">
                    Order Total: ₱${parseFloat(order.OrderTotal).toFixed(2)}
                </div>
            `;
            
            // Set content and show modal
            document.getElementById('orderDetailsContent').innerHTML = content;
            document.getElementById('orderDetailsModal').style.display = 'block';
        }
    }
    
    // Function to close order details modal
    function closeOrderDetails() {
        document.getElementById('orderDetailsModal').style.display = 'none';
    }
    
    // Close modal if clicked outside the content
    window.onclick = function(event) {
        const modal = document.getElementById('orderDetailsModal');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
</script>
</body>
</html>