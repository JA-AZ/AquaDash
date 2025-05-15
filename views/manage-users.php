<?php
// Include the PHP logic file for fetching users
include '../scripts/fetch-users.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link rel="stylesheet" href="manage-users-styles.css"> <!-- Link to external CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> <!-- Font Awesome -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- jQuery -->
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
                <li><a href="manage-users.php" class="active"><i class="fas fa-users"></i> Manage Users</a></li>
                <li><a href="manage-products.php"><i class="fas fa-box"></i> Manage Products</a></li>
                <li><a href="view-orders.php"><i class="fas fa-shopping-cart"></i> View Orders</a></li>
                <li><a href="index.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        <div class="main-content">
            <div class="header">
                <h1>Manage Users</h1>
            </div>
            <div class="user-table">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $rowNum = 1; foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $rowNum++; ?></td>
                            <td><?php echo htmlspecialchars($user['Name']); ?></td>
                            <td><?php echo htmlspecialchars($user['Email']); ?></td>
                            <td><?php echo htmlspecialchars($user['Phone']); ?></td>
                            <td><?php echo htmlspecialchars($user['Address']); ?></td>
                            <td>
                                <button class="edit-btn" 
                                    data-id="<?php echo $user['UserID']; ?>"
                                    data-name="<?php echo htmlspecialchars($user['Name']); ?>"
                                    data-email="<?php echo htmlspecialchars($user['Email']); ?>"
                                    data-phone="<?php echo htmlspecialchars($user['Phone']); ?>"
                                    data-address="<?php echo htmlspecialchars($user['Address']); ?>"
                                >Edit</button>
                                <button class="delete-btn" data-id="<?php echo $user['UserID']; ?>">Delete</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2><i class="fas fa-user-edit"></i> Edit User</h2>
            <form id="editForm" autocomplete="off">
                <input type="hidden" name="UserID" id="editUserID">
                <label for="editName">Name:</label>
                <input type="text" name="Name" id="editName" required>
                <label for="editEmail">Email:</label>
                <input type="email" name="Email" id="editEmail" required>
                <label for="editPhone">Phone:</label>
                <input type="text" name="Phone" id="editPhone" required>
                <label for="editAddress">Address:</label>
                <input type="text" name="Address" id="editAddress" required>
                <button type="submit" class="edit-save-btn"><i class="fas fa-save"></i> Save Changes</button>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Confirm Deletion</h2>
            <p>Are you sure you want to delete this user?</p>
            <button id="confirmDelete">Yes, Delete</button>
            <button id="cancelDelete">Cancel</button>
        </div>
    </div>

    <script src="manage-users.js"></script> <!-- Link to JavaScript -->
    <script>
    $(document).ready(function() {
        $('#editForm').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();
            $.post('../scripts/edit-user.php', formData, function(resp) {
                if (resp.success) {
                    location.reload();
                } else {
                    alert(resp.error || 'Failed to update user.');
                }
            }, 'json');
        });
    });
    </script>
</body>
</html>