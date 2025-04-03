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

// Fetch products from the database
$sql = "SELECT * FROM products";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Water Delivery Service</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body>
<header>
    <nav class="navbar navbar-expand-lg">
        <a class="navbar-brand" href="index.php">
            <img src="../assets/Aqua_Dash.png" alt="Aqua Dash Logo" class="logo">
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon">☰</span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="./shop.php">Shop</a></li>
                <li class="nav-item"><a class="nav-link" href="./cart.php">My Cart</a></li>
                <li class="nav-item"><a class="nav-link" href="../scripts/logout.php">Log out</a></li>
            </ul>
        </div>
    </nav>
</header>

<div class="title-container mx-5 my-5">
    <h1 class="text-left">Shop</h1>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5 g-4 py-5">

        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                ?>
                <div class="col">
                    <div class="product-card">
                        <img src="<?php echo $row['ImageURL']; ?>" class="card-img-top" alt="<?php echo $row['ProductName']; ?>">
                        <div class="card-body">
                            <h5 class="product-card-title"><?php echo $row['ProductName']; ?></h5>
                            <h3 class="product-price">₱<?php echo number_format($row['PricePerUnit'], 2); ?></h3>
                            <div class="quantity-label">Quantity</div>
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="quantity-section">
                                    <div class="quantity-selector">
                                        <button type="button" class="quantity-btn" onclick="decreaseQuantity(this)">−</button>
                                        <span class="quantity-value">1</span>
                                        <button type="button" class="quantity-btn" onclick="increaseQuantity(this)">+</button>
                                    </div>
                                </div>
                                <button class="btn add-btn-primary" onclick="addToCart(<?php echo $row['ProductID']; ?>, this)">Add</button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }
        } else {
            echo "<p class='text-center'>No products available.</p>";
        }
        $conn->close();
        ?>

    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script>
    function increaseQuantity(button) {
        let valueElement = button.previousElementSibling;
        let value = parseInt(valueElement.textContent);
        valueElement.textContent = value + 1;
    }

    function decreaseQuantity(button) {
        let valueElement = button.nextElementSibling;
        let value = parseInt(valueElement.textContent);
        if (value > 1) {
            valueElement.textContent = value - 1;
        }
    }
</script>
<script>
function addToCart(productID, button) {
    let quantity = parseInt(button.parentElement.querySelector('.quantity-value').textContent);
    let price = parseFloat(button.parentElement.parentElement.querySelector('.product-price').textContent.replace('₱', ''));
    let totalAmount = quantity * price;

    // Send data using AJAX
    $.ajax({
        url: '../scripts/add_to_cart.php',
        type: 'POST',
        data: {
            productID: productID,
            quantity: quantity,
            totalAmount: totalAmount
        },
        success: function(response) {
            alert(response); // Success message
        },
        error: function() {
            alert("Failed to add to cart");
        }
    });
}
</script>


</body>
</html>
