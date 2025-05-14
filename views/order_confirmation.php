<?php
session_start(); // Start session

// Check if the user is logged in
if (!isset($_SESSION['UserID'])) {
    header('Location: login.php');
    exit;
}

$userID = $_SESSION['UserID'];

// Check if order ID is provided
if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    header('Location: shop.php');
    exit;
}

$confirmedOrderID = $_GET['order_id'];

// Database connection
$conn = new mysqli("localhost", "root", "", "waterdelivery");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch confirmed order details
$orderSql = "SELECT co.*, ua.* 
             FROM confirmed_orders co
             JOIN user_addresses ua ON co.AddressID = ua.AddressID
             WHERE co.ConfirmedOrderID = ? AND co.UserID = ?";
$orderStmt = $conn->prepare($orderSql);
$orderStmt->bind_param("ii", $confirmedOrderID, $userID);
$orderStmt->execute();
$orderResult = $orderStmt->get_result();

// Check if order exists and belongs to the logged-in user
if ($orderResult->num_rows === 0) {
    header('Location: shop.php');
    exit;
}

$orderDetails = $orderResult->fetch_assoc();

// Fetch order items
$itemsSql = "SELECT oi.*, p.ProductName, p.ImageURL 
             FROM order_items oi
             JOIN products p ON oi.ProductID = p.ProductID
             WHERE oi.ConfirmedOrderID = ?";
$itemsStmt = $conn->prepare($itemsSql);
$itemsStmt->bind_param("i", $confirmedOrderID);
$itemsStmt->execute();
$itemsResult = $itemsStmt->get_result();

// Calculate total items count
$totalItems = 0;
$itemsDetails = [];
while ($item = $itemsResult->fetch_assoc()) {
    $totalItems += $item['Quantity'];
    $itemsDetails[] = $item;
}

// Reset pointer
$itemsStmt->execute();
$itemsResult = $itemsStmt->get_result();

// Format the estimated delivery date
$deliveryDate = new DateTime($orderDetails['EstimatedDeliveryDate']);
$formattedDeliveryDate = $deliveryDate->format('l, F j, Y');

// Get order number (using confirmed order ID with a prefix)
$orderNumber = 'AQD-' . str_pad($confirmedOrderID, 8, '0', STR_PAD_LEFT);

// Get shipping method display text
$shippingMethodText = ($orderDetails['ShippingMethod'] === 'express') ? 'Express Delivery (Next Day)' : 'Standard Delivery (2-3 Days)';

// Get payment method display text
$paymentMethodText = ($orderDetails['PaymentMethod'] === 'cod') ? 'Cash on Delivery' : 'Online Payment';
?>

<!DOCTYPE html>
<html lang="en">
<head> 
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Aqua Dash</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./order_confirmation_styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .confirmation-header {
            background-color: #0097c2;
            color: white;
            padding: 20px 40px;
        }
        
        .order-success {
            background-color: #e8f6ff;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .success-icon {
            color: #0097c2;
            font-size: 4rem;
            margin-bottom: 20px;
        }
        
        .order-info {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .info-box {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            flex: 1;
            min-width: 250px;
        }
        
        .info-box h3 {
            font-size: 1.1rem;
            color: #0097c2;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        
        .info-box h3 i {
            margin-right: 10px;
        }
        
        .info-box p {
            margin-bottom: 5px;
            color: #555;
        }
        
        .info-label {
            font-weight: 600;
            display: inline-block;
            min-width: 120px;
        }
        
        .order-items {
            background-color: white;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }
        
        .print-btn {
            background-color: #0097c2;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 10px 20px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
        }
        
        .print-btn:hover {
            background-color: #007ea3;
        }
        
        .print-btn i {
            margin-right: 8px;
        }
        
        .continue-btn {
            background-color: #4caf50;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 10px 20px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .continue-btn:hover {
            background-color: #3e9142;
        }
        
        .continue-btn i {
            margin-right: 8px;
        }
        
        .action-buttons {
            display: flex;
            justify-content: center;
            margin-bottom: 40px;
        }
        
        @media (max-width: 768px) {
            .confirmation-header {
                padding: 15px;
            }
            
            .info-box {
                flex: 100%;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 10px;
                align-items: center;
            }
            
            .print-btn, .continue-btn {
                width: 100%;
                margin-right: 0;
            }
        }
    </style>
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg">
            <a class="navbar-brand" href="./index.php">
                <img src="../assets/Aqua_Dash.png" alt="Aqua Dash Logo" class="logo">
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                <span class="navbar-toggler-icon">☰</span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="./index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="./shop.php">Shop</a></li>
                    <li class="nav-item"><a class="nav-link" href="./cart.php">My Cart</a></li>
                    <li class="nav-item"><a class="nav-link" href="../scripts/logout.php">Log out</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <div class="confirmation-header">
        <h1>Order Confirmation</h1>
        <div class="breadcrumb">
            <a href="./index.php"> Home </a> › 
            <a href="./shop.php"> Shop </a> › 
            <a href="./cart.php"> My Cart </a> › 
            <a href="./checkout.php"> Checkout </a> › 
            Order Confirmation
        </div>
    </div>

    <div class="container-fluid">
        <div class="order-success">
            <i class="fas fa-check-circle success-icon"></i>
            <h2>Thank You for Your Order!</h2>
            <p>Your order has been received and is being processed. You will receive an email confirmation shortly.</p>
            <h4>Order Number: <strong><?php echo $orderNumber; ?></strong></h4>
        </div>
        
        <div class="order-info">
            <div class="info-box">
                <h3><i class="fas fa-truck"></i> Shipping Information</h3>
                <p><span class="info-label">Recipient:</span> <?php echo htmlspecialchars($orderDetails['RecipientName']); ?></p>
                <p><span class="info-label">Address:</span> <?php echo htmlspecialchars($orderDetails['StreetAddress']); ?></p>
                <p><span class="info-label"></span> <?php echo htmlspecialchars($orderDetails['Barangay']); ?>, <?php echo htmlspecialchars($orderDetails['City']); ?></p>
                <p><span class="info-label"></span> <?php echo htmlspecialchars($orderDetails['Province']); ?>, <?php echo htmlspecialchars($orderDetails['PostalCode']); ?></p>
                <p><span class="info-label">Phone:</span> <?php echo htmlspecialchars($orderDetails['PhoneNumber']); ?></p>
            </div>
            
            <div class="info-box">
                <h3><i class="fas fa-info-circle"></i> Order Details</h3>
                <p><span class="info-label">Order Date:</span> <?php echo date('F j, Y', strtotime($orderDetails['OrderDate'])); ?></p>
                <p><span class="info-label">Order Status:</span> <span class="badge badge-info">Pending</span></p>
                <p><span class="info-label">Payment Method:</span> <?php echo $paymentMethodText; ?></p>
                <p><span class="info-label">Shipping Method:</span> <?php echo $shippingMethodText; ?></p>
                <p><span class="info-label">Est. Delivery:</span> <?php echo $formattedDeliveryDate; ?></p>
            </div>
        </div>
        
        <div class="order-items">
            <h3>Order Summary</h3>
            
            <div class="cart-items-list">
                <?php while ($item = $itemsResult->fetch_assoc()): ?>
                <div class="cart-item">
                    <div class="item-image">
                        <img src="<?php echo $item['ImageURL']; ?>" alt="<?php echo $item['ProductName']; ?>" class="product-img">
                    </div>
                    <div class="item-details">
                        <h3><?php echo htmlspecialchars($item['ProductName']); ?></h3>
                        <p class="item-price">₱<?php echo number_format($item['PricePerUnit'], 2); ?> each</p>
                    </div>
                    <div class="item-quantity">
                        <span>Qty: <?php echo $item['Quantity']; ?></span>
                    </div>
                    <div class="item-subtotal">
                        ₱<?php echo number_format($item['Subtotal'], 2); ?>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            
            <div class="summary-details">
                <div class="summary-row">
                    <span>Subtotal (<?php echo $totalItems; ?> items)</span>
                    <span class="amount">₱<?php echo number_format($orderDetails['Subtotal'], 2); ?></span>
                </div>
                
                <div class="summary-row">
                    <span>Shipping & Handling</span>
                    <span class="amount">₱<?php echo number_format($orderDetails['ShippingFee'], 2); ?></span>
                </div>
                
                <div class="summary-row total">
                    <span>Total</span>
                    <span class="amount">₱<?php echo number_format($orderDetails['TotalAmount'], 2); ?></span>
                </div>
            </div>
        </div>
        
        <div class="action-buttons">
            <button class="print-btn" id="print-order">
                <i class="fas fa-print"></i> Print Order
            </button>
            <a href="./shop.php" class="continue-btn">
                <i class="fas fa-shopping-cart"></i> Continue Shopping
            </a>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h4>Aqua Dash</h4>
                    <p>Your trusted water delivery service. Quality water delivered to your doorstep.</p>
                </div>
                <div class="col-md-4">
                    <h4>Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="./index.php">Home</a></li>
                        <li><a href="./shop.php">Shop</a></li>
                        <li><a href="./cart.php">My Cart</a></li>
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h4>Contact Us</h4>
                    <p><i class="fas fa-phone"></i> (123) 456-7890</p>
                    <p><i class="fas fa-envelope"></i> info@aquadash.com</p>
                    <p><i class="fas fa-map-marker-alt"></i> 123 Water St, Hydro City</p>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12 text-center copyright">
                    <p>&copy; <?php echo date('Y'); ?> Aqua Dash. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Print order functionality
            $('#print-order').click(function() {
                window.print();
            });
        });
    </script>
</body>
</html>

<?php
// Close connections
$orderStmt->close();
$itemsStmt->close();
$conn->close();
?>