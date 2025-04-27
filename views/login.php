<?php
session_start();

$error = '';
if (isset($_SESSION['login_error'])) {
    $error = $_SESSION['login_error'];
    unset($_SESSION['login_error']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
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
                <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                <li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>
            </ul>
        </div>
    </nav>
</header>

<div class="container d-flex justify-content-center align-items-center" >
    <div class="card" style="width: 400px;">
        <div class="card-body">
            <h5 class="card-title text-center">Login</h5>
            
            <!-- Display Error Message -->
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger text-center">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form action="../scripts/login_user.php" method="POST">
                <div class="form-group">
                <label for="Email">Email Address</label>
                <input type="email" name="Email" id="Email" class="form-control" placeholder="Enter email" required>
                </div>
                <div class="form-group">
                    <label for="Password">Password</label>
                    <input type="password" name="Password" id="Password" class="form-control" placeholder="Password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>
            <div class="text-center mt-3">
                <small>Don't have an account? <a href="register.php">Register</a></small>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
