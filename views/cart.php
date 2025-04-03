    <?php
    session_start(); // Start session

    // Check if the user is logged in
    if (!isset($_SESSION['UserID'])) {
        header('Location: login.php');
        exit;
    }

    $userID = $_SESSION['UserID'];

    // Database connection
    $conn = new mysqli("localhost", "root", "kapoyamagIT", "waterdelivery");

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Fetch user's orders with product details
    $sql = "SELECT o.OrderID, o.Quantity, o.TotalAmount, p.ProductName, p.ImageURL, p.PricePerUnit
            FROM orders o
            JOIN products p ON o.ProductID = p.ProductID
            WHERE o.UserID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $result = $stmt->get_result();

    $subtotal = 0;
    while ($row = $result->fetch_assoc()) {
        $subtotal += $row['TotalAmount'];
    }
    $shippingFee = ($subtotal > 0) ? 50.00 : 0.00;
    $total = $subtotal + $shippingFee;

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
        <link rel="stylesheet" href="./styles.css">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
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

    <h1 class="mb-4">My Cart</h1>

    <div class="container mt-4">
    <div class="row d-flex align-items-start">
        <!-- Cart Items -->
        <div class="col-7">
            <?php if ($result->num_rows > 0): ?>    
                <div class="cart-container">
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="cart-item">
                            <!-- Product Image -->
                            <img src="<?php echo $row['ImageURL']; ?>" alt="<?php echo $row['ProductName']; ?>" class="cart-item-img">

                            <div class="cart-item-details">
                                <h5><?php echo $row['ProductName']; ?></h5>
                                <p>₱<?php echo number_format($row['PricePerUnit'], 2); ?> x <?php echo $row['Quantity']; ?></p>
                                <h6>Subtotal: ₱<?php echo number_format($row['TotalAmount'], 2); ?></h6>
                            </div>

                            <div class="cart-actions">
                                <!-- Quantity Selector -->
                                <form action="update_cart.php" method="POST">
                                    <input type="hidden" name="orderID" value="<?php echo $row['OrderID']; ?>">
                                    <button type="submit" name="decrease" class="quantity-btn">−</button>
                                    <span class="quantity-value"><?php echo $row['Quantity']; ?></span>
                                    <button type="submit" name="increase" class="quantity-btn">+</button>
                                </form>

                                <!-- Remove Button -->
                                <form action="remove_from_cart.php" method="POST">
                                    <input type="hidden" name="orderID" value="<?php echo $row['OrderID']; ?>">
                                    <button type="submit" class="remove-btn">Remove</button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="text-muted">Your cart is empty.</p>
            <?php endif; ?>
        </div>

        <!-- Summary Section -->
        <div class="col-3">
            <?php if ($subtotal > 0): ?>
                <div class="cart-summary">
                    <p>Subtotal: <span>₱<?php echo number_format($subtotal, 2); ?></span></p>
                    <p>Shipping Fee: <span>₱<?php echo number_format($shippingFee, 2); ?></span></p>
                    <p class="total">Total: <span>₱<?php echo number_format($total, 2); ?></span></p>
                    <button class="checkout-btn">Checkout</button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>


    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

    </body>
    </html>

    <?php
    $stmt->close();
    $conn->close();
    ?>
