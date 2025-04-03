<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
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
            <ul class="navbar-nav ms-auto"> <!-- Changed ml-auto to ms-auto -->
                <li class="nav-item"><a class="nav-link" href="./index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="./login.php">Login</a></li>
                <li class="nav-item"><a class="nav-link" href="./register.php">Register</a></li>
            </ul>
        </div>
    </nav>
</header>

<div class="container d-flex justify-content-center align-items-center">
    <div class="card" style="width: 400px;">
        <div class="card-body">
            <h5 class="card-title text-center">Register</h5>
            <form action="../scripts/register_user.php" method="POST" onsubmit="return validatePasswords()">
                <div class="form-group">
                    <label for="Name">Name</label>
                    <input type="text" name="Name" id="Name" class="form-control" placeholder="Enter name" required>
                </div>
                <div class="form-group">
                    <label for="Email">Email</label>
                    <input type="email" name="Email" id="Email" class="form-control" placeholder="Enter email" required>
                </div>
                <div class="form-group">
                    <label for="Phone">Phone</label>
                    <input type="text" name="Phone" id="Phone" class="form-control" placeholder="Enter phone number" required>
                </div>
                <div class="form-group">
                    <label for="Address">Address</label>
                    <input type="text" name="Address" id="Address" class="form-control" placeholder="Enter address" required>
                </div>
                <div class="form-group">
                    <label for="Password">Password</label>
                    <input type="password" name="Password" id="Password" class="form-control" placeholder="Enter password" required>
                </div>
                <div class="form-group">
                    <label for="Password_confirmation">Confirm Password</label>
                    <input type="password" name="Password_confirmation" id="Password_confirmation" class="form-control" placeholder="Confirm password" required>
                    <small id="passwordError" class="text-danger" style="display: none;">Passwords do not match!</small>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Register</button>
            </form>
            <div class="text-center mt-3">
                <small>Already have an account? <a href="../views/login.php">Login</a></small>
            </div>
        </div>
    </div>
</div>

<script>
function validatePasswords() {
    const password = document.getElementById('Password').value;
    const confirmPassword = document.getElementById('Password_confirmation').value;
    const error = document.getElementById('passwordError');

    if (password !== confirmPassword) {
        error.style.display = 'block';
        return false;
    } else {
        error.style.display = 'none';
        return true;
    }
}
</script>

<!-- Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script> <!-- Use full version -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
