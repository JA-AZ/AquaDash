    <?php
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

    // Fetch user's orders with product details
    $sql = "SELECT o.OrderID, o.Quantity, o.TotalAmount, p.ProductName, p.ImageURL, p.PricePerUnit, p.ProductID
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
    $shippingFee = ($subtotal > 0) ? 45.00 : 0.00;
    $total = $subtotal + $shippingFee;

    // Reset result pointer
    $stmt->execute();
    $result = $stmt->get_result();
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Water Delivery Service - My Cart</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
        <link rel="stylesheet" href="./cart_styles.css">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
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
                        <li class="nav-item"><a class="nav-link active" href="./cart.php">My Cart</a></li>
                        <li class="nav-item"><a class="nav-link" href="../scripts/logout.php">Log out</a></li>
                    </ul>
                </div>
            </nav>
        </header>

            <div class="cart-header">
                <h1>My Cart</h1>
                <div class="breadcrumb">
                    <a href="./index.php"> Home </a> ›  
                    <a href="./shop.php"> Shop </a> ›  
                    My Cart
            </div>

        </div>

        <div class="container-fluid">
            <div class="row cart-container">
                <!-- Cart Items Column -->
                <div class="col-lg-8 col-md-12">
                    <div class="cart-items-container">
                        <h2>Cart Items (<?php echo $itemCount; ?>)</h2>
                        
                        <?php if ($result->num_rows > 0): ?>
                            <div class="cart-items-list">
                                <?php while ($row = $result->fetch_assoc()): 
                                    $unitPrice = $row['PricePerUnit'];
                                    $unitType = "";
                                    
                                    // Determine unit type based on product name
                                    if (stripos($row['ProductName'], 'gallon') !== false) {
                                        $unitType = "gallon";
                                    } elseif (stripos($row['ProductName'], 'pack') !== false || stripos($row['ProductName'], 'bottle') !== false) {
                                        $unitType = "bottle";
                                    }
                                ?>
                                    <div class="cart-item" data-order-id="<?php echo $row['OrderID']; ?>">
                                        <div class="item-image">
                                            <!-- Using product image from database -->
                                            <img src="<?php echo $row['ImageURL']; ?>" alt="<?php echo $row['ProductName']; ?>" class="product-img">
                                        </div>
                                        
                                        <div class="item-details">
                                            <h3><?php echo $row['ProductName']; ?></h3>
                                            <p class="item-price" data-unit-price="<?php echo $unitPrice; ?>">
                                                <?php echo '₱' . number_format($unitPrice, 2) . ' / ' . $unitType; ?>
                                            </p>
                                        </div>
                                        
                                        <div class="item-quantity">
                                            <div class="quantity-selector">
                                                <button type="button" class="quantity-btn decrease">−</button>
                                                <span class="quantity-value"><?php echo $row['Quantity']; ?></span>
                                                <button type="button" class="quantity-btn increase">+</button>
                                            </div>
                                        </div>
                                        
                                        <div class="item-subtotal">
                                            ₱<?php echo number_format($row['TotalAmount'], 2); ?>
                                        </div>
                                        
                                        <div class="item-remove">
                                            <button type="button" class="remove-btn">
                                                <i class="fas fa-trash"></i> Remove
                                            </button>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-cart">
                                <p>Your cart is empty.</p>
                                <a href="./shop.php" class="btn btn-primary">Shop Now</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Order Summary Column -->
                <div class="col-lg-4 col-md-12">
                    <div class="order-summary">
                        <h2>Order Summary</h2>
                        
                        <div class="summary-details">
                            <div class="summary-row">
                                <span>Subtotal (<?php echo $itemCount; ?> items)</span>
                                <span class="amount">₱<?php echo number_format($subtotal, 2); ?></span>
                            </div>
                            
                            <div class="summary-row">
                                <span>Shipping & Handling</span>
                                <span class="amount">₱<?php echo number_format($shippingFee, 2); ?></span>
                            </div>
                            
                            <div class="summary-row total">
                                <span>Estimated Total</span>
                                <span class="amount">₱<?php echo number_format($total, 2); ?></span>
                            </div>
                            
                            <div class="delivery-estimate">
                                Delivery: Estimated 2-3 business days
                            </div>
                        </div>
                        
                        <button class="checkout-btn">
                            <i class="fas fa-lock"></i> Proceed to Checkout
                        </button>
                        
                    </div>
                </div>
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
                function updateQuantity(orderId, newQuantity, item) {
                    $.post('../scripts/update_cart.php', { order_id: orderId, quantity: newQuantity }, function(response) {
                        if (response.trim() === 'success') {
                            // Update subtotal for this item
                            let unitPrice = parseFloat(item.find('.item-price').data('unit-price'));
                            let newSubtotal = unitPrice * newQuantity;
                            item.find('.item-subtotal').text('₱' + newSubtotal.toFixed(2));

                            // After updating this item's subtotal, update cart totals
                            updateCartTotals();
                        } else {
                            alert('Failed to update cart. Please try again.');
                        }
                    }).fail(function() {
                        alert('Error occurred while updating cart.');
                    });
                }

                function updateCartTotals() {
                    let subtotal = 0;

                    $('.cart-item').each(function() {
                        let itemSubtotalText = $(this).find('.item-subtotal').text().replace('₱', '');
                        subtotal += parseFloat(itemSubtotalText);
                    });

                    let shipping = (subtotal > 0) ? 45.00 : 0.00;
                    let total = subtotal + shipping;

                    $('.summary-details .summary-row span.amount').eq(0).text('₱' + subtotal.toFixed(2)); // Subtotal
                    $('.summary-details .summary-row span.amount').eq(1).text('₱' + shipping.toFixed(2)); // Shipping
                    $('.summary-details .summary-row.total span.amount').text('₱' + total.toFixed(2)); // Total
                }

                $('.increase').click(function() {
                    let item = $(this).closest('.cart-item');
                    let orderId = item.data('order-id');
                    let quantity = item.find('.quantity-value');
                    let newValue = parseInt(quantity.text()) + 1;
                    quantity.text(newValue);

                    updateQuantity(orderId, newValue, item);
                });
                
                $('.decrease').click(function() {
                    let item = $(this).closest('.cart-item');
                    let orderId = item.data('order-id');
                    let quantity = item.find('.quantity-value');
                    let currentValue = parseInt(quantity.text());
                    if (currentValue > 1) {
                        let newValue = currentValue - 1;
                        quantity.text(newValue);

                        updateQuantity(orderId, newValue, item);
                    }
                });

                $('.remove-btn').click(function() {
                    let item = $(this).closest('.cart-item');
                    let orderId = item.data('order-id');

                    if (confirm('Are you sure you want to remove this item from your cart?')) {
                        $.post('../scripts/remove_from_cart.php', { order_id: orderId }, function(response) {
                            if (response.trim() === 'success') {
                                item.fadeOut(300, function() {
                                    $(this).remove();
                                    updateCartTotals();
                                });
                            } else {
                                alert('Failed to remove item. Please try again.');
                            }
                        }).fail(function() {
                            alert('Error occurred while removing item.');
                        });
                    }
                });

                // Redirect to checkout page when checkout button is clicked
                $('.checkout-btn').click(function() {
                    window.location.href = "./checkout.php";
                });

                // When page loads, make sure cart totals are correct
                updateCartTotals();
            });
        </script>
    </body>
    </html>

    <?php
    $stmt->close();
    $conn->close();
    ?>