<?php

// Add these lines at the top of checkout.php, right after <?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start(); // Start session

// Check if the user is logged in
if (!isset($_SESSION['UserID'])) {
    header('Location: login.php');
    exit;
}

$userID = $_SESSION['UserID'];

// Database connection
$conn = new mysqli("localhost", "root", "", "waterdelivery");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user's orders with product details (cart items)
$sql = "SELECT o.OrderID, o.Quantity, o.TotalAmount, p.ProductID, p.ProductName, p.ImageURL, p.PricePerUnit
        FROM orders o
        JOIN products p ON o.ProductID = p.ProductID
        WHERE o.UserID = ? AND o.Status = 'cart'";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

// Count items in cart
$itemCount = $result->num_rows;

// Calculate subtotal
$subtotal = 0;
while ($row = $result->fetch_assoc()) {
    $subtotal += $row['TotalAmount'];
}

// Set default shipping fees
$standardShippingFee = 45.00;
$expressShippingFee = 90.00;

// Default to standard shipping
$shippingFee = $standardShippingFee;
$shippingMethod = 'standard';

// Handle shipping method selection
if (isset($_POST['shipping_method'])) {
    $shippingMethod = $_POST['shipping_method'];
    $shippingFee = ($shippingMethod === 'express') ? $expressShippingFee : $standardShippingFee;
}

$total = $subtotal + $shippingFee;

// Fetch user addresses
$addressesSql = "SELECT * FROM user_addresses WHERE UserID = ? ORDER BY IsDefault DESC, DateAdded DESC";
$addressesStmt = $conn->prepare($addressesSql);
$addressesStmt->bind_param("i", $userID);
$addressesStmt->execute();
$addressesResult = $addressesStmt->get_result();

// Check if the form is submitted to add a new address
$addressAdded = false;
if (isset($_POST['add_address'])) {
    $recipientName = $_POST['recipient_name'];
    $phoneNumber = $_POST['phone_number'];
    $streetAddress = $_POST['street_address'];
    $barangay = $_POST['barangay'];
    $city = $_POST['city'];
    $province = $_POST['province'];
    $postalCode = $_POST['postal_code'];
    $isDefault = isset($_POST['is_default']) ? 1 : 0;
    
    // If setting as default, first unset all other default addresses
    if ($isDefault) {
        $unsetDefaultSql = "UPDATE user_addresses SET IsDefault = 0 WHERE UserID = ?";
        $unsetStmt = $conn->prepare($unsetDefaultSql);
        $unsetStmt->bind_param("i", $userID);
        $unsetStmt->execute();
        $unsetStmt->close();
    }
    
    // Insert the new address
    $insertAddressSql = "INSERT INTO user_addresses (UserID, RecipientName, PhoneNumber, StreetAddress, Barangay, City, Province, PostalCode, IsDefault) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $insertStmt = $conn->prepare($insertAddressSql);
    $insertStmt->bind_param("isssssssi", $userID, $recipientName, $phoneNumber, $streetAddress, $barangay, $city, $province, $postalCode, $isDefault);
    
    if ($insertStmt->execute()) {
        $addressAdded = true;
        // Refresh the addresses list
        $addressesStmt->execute();
        $addressesResult = $addressesStmt->get_result();
    } else {
        $addressError = "Error saving address: " . $insertStmt->error;
    }
    
    $insertStmt->close();
}

// Process order confirmation
if (isset($_POST['confirm_order'])) {
    // Check if an address is selected
    if (!isset($_POST['address_id']) || empty($_POST['address_id'])) {
        $orderError = "Please select a delivery address";
    } else {
        $addressID = $_POST['address_id'];
        $shippingMethod = $_POST['shipping_method'];
        $shippingFee = ($shippingMethod === 'express') ? $expressShippingFee : $standardShippingFee;
        $paymentMethod = 'cod'; // Currently only COD is available
        
        // Begin transaction
        $conn->begin_transaction();
        
        try {
            // Calculate estimated delivery date based on shipping method
            $deliveryDays = ($shippingMethod === 'express') ? 1 : 3;
            $estimatedDeliveryDate = date('Y-m-d', strtotime("+$deliveryDays days"));
            
            // Insert into confirmed_orders table
            $insertOrderSql = "INSERT INTO confirmed_orders 
                               (UserID, AddressID, ShippingMethod, ShippingFee, Subtotal, TotalAmount, PaymentMethod, EstimatedDeliveryDate) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $insertOrderStmt = $conn->prepare($insertOrderSql);
            $insertOrderStmt->bind_param("iisdddsb", $userID, $addressID, $shippingMethod, $shippingFee, $subtotal, $total, $paymentMethod, $estimatedDeliveryDate);
            $insertOrderStmt->execute();
            
            // Get the new confirmed order ID
            $confirmedOrderID = $conn->insert_id;
            
            // Reset result pointer for cart items
            $stmt->execute();
            $cartItems = $stmt->get_result();
            
            // Insert each cart item into order_items table
            while ($item = $cartItems->fetch_assoc()) {
                $productID = $item['ProductID'] ?? 0; // Fallback if ProductID isn't in the result
                $quantity = $item['Quantity'];
                $pricePerUnit = $item['PricePerUnit'];
                $itemSubtotal = $item['TotalAmount'];
                $orderID = $item['OrderID'];
                
                $insertItemSql = "INSERT INTO order_items 
                                 (ConfirmedOrderID, ProductID, Quantity, PricePerUnit, Subtotal) 
                                 VALUES (?, ?, ?, ?, ?)";
                $insertItemStmt = $conn->prepare($insertItemSql);
                $insertItemStmt->bind_param("iiidd", $confirmedOrderID, $productID, $quantity, $pricePerUnit, $itemSubtotal);
                $insertItemStmt->execute();
                
                // Update order status to 'confirmed'
                $updateOrderSql = "UPDATE orders SET Status = 'confirmed' WHERE OrderID = ?";
                $updateOrderStmt = $conn->prepare($updateOrderSql);
                $updateOrderStmt->bind_param("i", $orderID);
                $updateOrderStmt->execute();
            }
            
            // Commit the transaction
            $conn->commit();
            
            // Redirect to order confirmation page
            header("Location: order_confirmation.php?order_id=$confirmedOrderID");
            exit;
            
        } catch (Exception $e) {
            // Something went wrong, rollback the transaction
            $conn->rollback();
            $orderError = "An error occurred while processing your order. Please try again.";
        }
    }
}

// Reset result pointer for displaying items
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Water Delivery Service - Checkout</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./checkout_styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        
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

    <div class="checkout-header">
        <h1>Checkout</h1>
        <div class="breadcrumb">
            <a href="./index.php"> Home </a> › 
            <a href="./shop.php"> Shop </a> › 
            <a href="./cart.php"> My Cart </a> › 
            Checkout
        </div>
    </div>

    <div class="container-fluid">
        <?php if (isset($orderError)): ?>
            <div class="alert alert-danger"><?php echo $orderError; ?></div>
        <?php endif; ?>
        
        <?php if ($addressAdded): ?>
            <div class="alert alert-success">Address added successfully.</div>
        <?php endif; ?>
        
        <?php if ($itemCount == 0): ?>
            <div class="alert alert-warning">Your cart is empty. <a href="./shop.php">Continue shopping</a></div>
        <?php else: ?>
            <form method="post" action="checkout.php" id="checkout-form">
                <div class="row">
                    <!-- Left Column - Checkout Steps -->
                    <div class="col-lg-8 col-md-12">
                        <!-- 1. Shipping Address Section -->
                        <div class="checkout-section">
                            <h2 class="section-title"><i class="fas fa-map-marker-alt"></i> Shipping Address</h2>
                            
                            <?php if ($addressesResult->num_rows > 0): ?>
                                <div id="address-list">
                                    <?php while ($address = $addressesResult->fetch_assoc()): ?>
                                        <div class="address-card <?php echo $address['IsDefault'] ? 'selected' : ''; ?>" data-address-id="<?php echo $address['AddressID']; ?>">
                                            <input type="radio" name="address_id" value="<?php echo $address['AddressID']; ?>" class="radio-button" <?php echo $address['IsDefault'] ? 'checked' : ''; ?>>
                                            <h4><?php echo htmlspecialchars($address['RecipientName']); ?></h4>
                                            <p><?php echo htmlspecialchars($address['PhoneNumber']); ?></p>
                                            <p>
                                                <?php echo htmlspecialchars($address['StreetAddress']); ?>,<br>
                                                <?php echo htmlspecialchars($address['Barangay']); ?>,<br>
                                                <?php echo htmlspecialchars($address['City']); ?>, <?php echo htmlspecialchars($address['Province']); ?> <?php echo htmlspecialchars($address['PostalCode']); ?>
                                            </p>
                                            <?php if ($address['IsDefault']): ?>
                                                <span class="badge badge-primary">Default</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <p>No addresses found. Please add a delivery address.</p>
                            <?php endif; ?>
                            
                            <div class="address-actions">
                                <button type="button" id="add-address-toggle" class="btn btn-secondary">
                                    <i class="fas fa-plus"></i> Add New Address
                                </button>
                            </div>
                            
                            <div id="address-form" class="address-form" <?php echo ($addressesResult->num_rows === 0) ? 'style="display:block;"' : ''; ?>>
                                <h3>Add New Address</h3>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="recipient_name">Recipient Name</label>
                                            <input type="text" class="form-control" id="recipient_name" name="recipient_name" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="phone_number">Phone Number</label>
                                            <input type="text" class="form-control" id="phone_number" name="phone_number" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="street_address">Street Address</label>
                                    <input type="text" class="form-control" id="street_address" name="street_address" required>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="barangay">Barangay</label>
                                            <input type="text" class="form-control" id="barangay" name="barangay" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="city">City</label>
                                            <input type="text" class="form-control" id="city" name="city" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="province">Province</label>
                                            <input type="text" class="form-control" id="province" name="province" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="postal_code">Postal Code</label>
                                            <input type="text" class="form-control" id="postal_code" name="postal_code" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="is_default" name="is_default" checked>
                                        <label class="custom-control-label" for="is_default">Set as default address</label>
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="col">
                                        <button type="submit" name="add_address" id="save-address-btn" class="btn btn-primary">Save Address</button>
                                        <button type="button" id="cancel-address" class="btn btn-secondary">Cancel</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- 2. Shipping Method Section -->
                        <div class="checkout-section">
                            <h2 class="section-title"><i class="fas fa-truck"></i> Shipping Method</h2>
                            
                            <div class="shipping-option selected">
                                <input type="radio" name="shipping_method" value="standard" id="standard-shipping" checked>
                                <div class="shipping-details">
                                    <div class="shipping-name">Standard Delivery</div>
                                    <div class="shipping-time">2-3 business days</div>
                                </div>
                                <div class="shipping-cost">₱<?php echo number_format($standardShippingFee, 2); ?></div>
                            </div>
                            
                            <div class="shipping-option">
                                <input type="radio" name="shipping_method" value="express" id="express-shipping">
                                <div class="shipping-details">
                                    <div class="shipping-name">Express Delivery</div>
                                    <div class="shipping-time">Next business day</div>
                                </div>
                                <div class="shipping-cost">₱<?php echo number_format($expressShippingFee, 2); ?></div>
                            </div>
                        </div>
                        
                        <!-- 3. Payment Method Section -->
                        <div class="checkout-section">
                            <h2 class="section-title"><i class="fas fa-credit-card"></i> Payment Method</h2>
                            
                            <div class="payment-option selected">
                                <input type="radio" name="payment_method" value="cod" id="cod-payment" checked>
                                <i class="fas fa-money-bill-wave payment-icon"></i>
                                <div class="payment-details">
                                    <div class="payment-name">Cash on Delivery</div>
                                    <div class="payment-description">Pay when you receive your order</div>
                                </div>
                            </div>
                            
                            <!-- Future payment methods can be added here -->
                            <div class="payment-option disabled" style="opacity: 0.5; cursor: not-allowed;">
                                <input type="radio" name="payment_method" value="online" id="online-payment" disabled>
                                <i class="fas fa-credit-card payment-icon"></i>
                                <div class="payment-details">
                                    <div class="payment-name">Online Payment (Coming Soon)</div>
                                    <div class="payment-description">Pay securely with your credit/debit card</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right Column - Order Summary -->
                    <div class="col-lg-4 col-md-12">
                        <div class="order-summary">
                            <h2>Order Summary</h2>
                            
                            <div class="order-items-preview">
                                <h3>Items (<?php echo $itemCount; ?>)</h3>
                                
                                <?php if ($result->num_rows > 0): ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <div class="order-item">
                                            <div class="order-item-image">
                                                <img src="<?php echo $row['ImageURL']; ?>" alt="<?php echo $row['ProductName']; ?>">
                                            </div>
                                            <div class="order-item-details">
                                                <div class="order-item-name"><?php echo $row['ProductName']; ?></div>
                                                <div class="order-item-quantity">Qty: <?php echo $row['Quantity']; ?></div>
                                            </div>
                                            <div class="order-item-price">₱<?php echo number_format($row['TotalAmount'], 2); ?></div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </div>
                            
                            <div class="summary-details">
                                <div class="summary-row">
                                    <span>Subtotal (<?php echo $itemCount; ?> items)</span>
                                    <span class="amount">₱<?php echo number_format($subtotal, 2); ?></span>
                                </div>
                                
                                <div class="summary-row">
                                    <span>Shipping & Handling</span>
                                    <span class="amount" id="shipping-cost">₱<?php echo number_format($shippingFee, 2); ?></span>
                                </div>
                                
                                <div class="summary-row total">
                                    <span>Estimated Total</span>
                                    <span class="amount" id="total-cost">₱<?php echo number_format($total, 2); ?></span>
                                </div>
                                
                                <div class="delivery-estimate">
                                    Delivery: Estimated <?php echo ($shippingMethod === 'express') ? 'next business day' : '2-3 business days'; ?>
                                </div>
                            </div>
                            
                            <button type="submit" name="confirm_order" class="confirm-order-btn" <?php echo ($addressesResult->num_rows === 0) ? 'disabled' : ''; ?>>
                                <i class="fas fa-check-circle"></i> Confirm Order
                            </button>
                            
                            <div class="payment-methods">
                                <i class="fas fa-money-bill-wave"></i>
                                <i class="fas fa-hand-holding-usd"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        <?php endif; ?>
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
            // Address card selection
            $('.address-card').click(function() {
                $('.address-card').removeClass('selected');
                $(this).addClass('selected');
                $(this).find('input[type="radio"]').prop('checked', true);
            });
            
            // Add address form toggle
            $('#add-address-toggle').click(function() {
                $('#address-form').slideDown();
            });
            
            $('#cancel-address').click(function() {
                $('#address-form').slideUp();
            });
            
            // Shipping method selection
            $('.shipping-option').click(function() {
                $('.shipping-option').removeClass('selected');
                $(this).addClass('selected');
                $(this).find('input[type="radio"]').prop('checked', true);
                
                // Update shipping cost and total
                const shippingMethod = $(this).find('input[type="radio"]').val();
                const shippingCost = (shippingMethod === 'express') ? <?php echo $expressShippingFee; ?> : <?php echo $standardShippingFee; ?>;
                const subtotal = <?php echo $subtotal; ?>;
                const total = subtotal + shippingCost;
                
                $('#shipping-cost').text('₱' + shippingCost.toFixed(2));
                $('#total-cost').text('₱' + total.toFixed(2));
                
                // Update estimated delivery time
                const deliveryEstimate = (shippingMethod === 'express') ? 'next business day' : '2-3 business days';
                $('.delivery-estimate').text('Delivery: Estimated ' + deliveryEstimate);
            });
            
            // Payment method selection
            $('.payment-option:not(.disabled)').click(function() {
                $('.payment-option').removeClass('selected');
                $(this).addClass('selected');
                $(this).find('input[type="radio"]').prop('checked', true);
            });
            
            // Form validation before submission
            $('#checkout-form').submit(function(e) {
                // Skip validation if we're adding a new address
                if ($(document.activeElement).attr('name') === 'add_address') {
                    return true;
                }
                
                // Otherwise check for selected address
                if ($('input[name="address_id"]:checked').length === 0) {
                    alert('Please select a delivery address');
                    e.preventDefault();
                    return false;
                }
                return true;
            });
        });
    </script>
</body>
</html>

<?php
$stmt->close();
$addressesStmt->close();
$conn->close();
?>