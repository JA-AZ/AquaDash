<?php
// Include the PHP logic file from the scripts folder
include '../scripts/dashboard-data.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="admin-style.css"> <!-- Link to external CSS -->
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
                <li><a href="view-orders.php"><i class="fas fa-shopping-cart"></i> View Orders</a></li>
                <li><a href="index.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        <div class="main-content">
            <div class="header">
                <h1>Welcome, Admin</h1>
            </div>
            <div class="dashboard-stats">
                <div class="stat-card">
                    <h3>Total Users</h3>
                    <p><?php echo $totalUsers; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Products</h3>
                    <p><?php echo $totalProducts; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Orders</h3>
                    <p><?php echo $totalOrders; ?></p>
                </div>
            </div>
            <!-- Quick Actions -->
            <div class="quick-actions">
                <a href="manage-products.php" class="quick-action-btn"><i class="fas fa-plus"></i> Add New Product</a>
                <a href="view-orders.php" class="quick-action-btn"><i class="fas fa-shopping-cart"></i> View All Orders</a>
                <a href="manage-users.php" class="quick-action-btn"><i class="fas fa-users"></i> Manage Users</a>
            </div>
        </div>
    </div>
    <style>
    .quick-actions {
        display: flex;
        gap: 24px;
        margin: 32px 0 0 0;
        justify-content: flex-start;
    }
    .quick-action-btn {
        background: #3498db;
        color: #fff;
        font-size: 1.15rem;
        font-weight: 600;
        padding: 18px 32px;
        border-radius: 8px;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 12px;
        box-shadow: 0 2px 8px rgba(52, 152, 219, 0.08);
        transition: background 0.2s, box-shadow 0.2s;
    }
    .quick-action-btn:hover {
        background: #217dbb;
        box-shadow: 0 4px 16px rgba(52, 152, 219, 0.13);
        color: #fff;
    }
    </style>
</body>
</html>