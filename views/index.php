<?php
session_start();
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
        <a class="navbar-brand" href="./index.php">
            <img src="../assets/Aqua_Dash.png" alt="Aqua Dash Logo" class="logo">
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon">☰</span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="./index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="./shop.php">Shop</a></li>
                
                <?php if (!isset($_SESSION['UserID'])): ?>
                    <li class="nav-item"><a class="nav-link" href="./login.php">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="./register.php">Register</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="./cart.php">Cart</a></li>
                    <li class="nav-item"><a class="nav-link" href="../scripts/logout.php">Log out</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
</header>

<main>
    <section class="hero">
            <h1><strong>From our source</strong> <br> to your <span class="highlight">Home</span></h1>
            <p>pure, fresh, and always on time. <br> Order now and experience hassle-free water delivery!</p>
            <button class="order-btn" 
                onclick="location.href='<?php echo isset($_SESSION['UserID']) ? './shop.php' : './register.php'; ?>'">
                Order Now
            </button>
    </section>
</main>

<!-- Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

</body>
</html>
