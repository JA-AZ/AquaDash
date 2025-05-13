<?php
// Include the PHP logic file for fetching products (to be created)
include '../scripts/fetch-products.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products</title>
    <link rel="stylesheet" href="manage-users-styles.css"> <!-- Reuse user management styles -->
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
                <li><a href="manage-users.php"><i class="fas fa-users"></i> Manage Users</a></li>
                <li><a href="manage-products.php" class="active"><i class="fas fa-box"></i> Manage Products</a></li>
                <li><a href="view-orders.php"><i class="fas fa-shopping-cart"></i> View Orders</a></li>
                <li><a href="index.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        <div class="main-content">
            <div class="header">
                <h1>Manage Products</h1>
            </div>
           <!-- Revised Add Product Form matching current UI -->
            <div class="add-product-section">
                <h2 class="add-product-title">Add New Product</h2>
                <form id="addProductForm" enctype="multipart/form-data" method="POST" action="../scripts/add-product.php" class="add-product-form">
                    <div class="add-product-grid">
                        <div class="image-upload-container">
                            <label for="ImageFile" class="image-upload-label">
                                <div class="image-upload-content">
                                    <i class="fas fa-cloud-upload-alt upload-icon"></i>
                                    <span id="imagePreviewText">Upload Image</span>
                                    <img id="imagePreview" src="#" alt="Preview" style="display:none;" />
                                </div>
                                <input type="file" name="ImageFile" id="ImageFile" accept="image/*" required style="display:none;">
                            </label>
                        </div>
                        <div class="product-fields">
                            <input type="text" name="ProductName" id="ProductName" placeholder="Product Name" required class="product-input">
                            <div class="product-fields-row">
                                <input type="number" step="0.01" name="PricePerUnit" id="PricePerUnit" placeholder="Price Per Unit (₱)" required class="product-input">
                                <input type="number" name="StockQuantity" id="StockQuantity" placeholder="Stock Quantity" required class="product-input">
                            </div>
                            <button type="submit" class="add-product-btn">
                                <i class="fas fa-plus-circle"></i> Add Product
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <!-- Products Table -->
            <div class="user-table">
                <table>
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($paginatedProducts as $product): ?>
                        <tr data-product-id="<?php echo $product['ProductID']; ?>">
                            <td><img src="<?php echo $product['ImageURL']; ?>" alt="<?php echo htmlspecialchars($product['ProductName']); ?>" style="width:60px;height:60px;object-fit:cover;"></td>
                            <td><?php echo htmlspecialchars($product['ProductName']); ?></td>
                            <td>₱<?php echo number_format($product['PricePerUnit'], 2); ?></td>
                            <td><?php echo $product['StockQuantity']; ?></td>
                            <td>
                                <button class="edit-btn" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($product)); ?>)">Edit</button>
                                <button class="delete-btn" onclick="openDeleteModal(<?php echo $product['ProductID']; ?>)">Delete</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <!-- Pagination -->
                <div style="text-align:center;margin:20px 0;">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>" class="edit-btn" style="margin:0 5px;<?php if($i==$currentPage) echo 'background:#218838;'; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                </div>
            </div>
            <!-- Edit Product Modal -->
            <div id="editModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeEditModal()">&times;</span>
                    <h2>Edit Product</h2>
                    <form id="editProductForm" enctype="multipart/form-data">
                        <input type="hidden" name="ProductID" id="editProductID">
                        <label for="editProductName">Product Name:</label>
                        <input type="text" name="ProductName" id="editProductName" required>
                        <label for="editPricePerUnit">Price Per Unit (₱):</label>
                        <input type="number" step="0.01" name="PricePerUnit" id="editPricePerUnit" required>
                        <label for="editStockQuantity">Stock Quantity:</label>
                        <input type="number" name="StockQuantity" id="editStockQuantity" required>
                        <label>Current Image:</label>
                        <img id="currentEditImage" src="" alt="Current Image" style="width:60px;height:60px;object-fit:cover;display:block;margin-bottom:10px;">
                        <label for="editImageFile">Change Image (optional):</label>
                        <input type="file" name="ImageFile" id="editImageFile" accept="image/*">
                        <button type="submit" class="edit-btn">Save Changes</button>
                    </form>
                </div>
            </div>
            <!-- Delete Confirmation Modal -->
            <div id="deleteModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeDeleteModal()">&times;</span>
                    <h2>Confirm Deletion</h2>
                    <p>Are you sure you want to delete this product?</p>
                    <button id="confirmDelete" class="delete-btn">Yes, Delete</button>
                    <button onclick="closeDeleteModal()">Cancel</button>
                </div>
            </div>
            <script>
            // Modal logic
            let currentDeleteId = null;
            let currentEditImageUrl = '';
            function openEditModal(product) {
                $("#editProductID").val(product.ProductID);
                $("#editProductName").val(product.ProductName);
                $("#editPricePerUnit").val(product.PricePerUnit);
                $("#editStockQuantity").val(product.StockQuantity);
                $("#currentEditImage").attr('src', product.ImageURL);
                currentEditImageUrl = product.ImageURL;
                $("#editModal").show();
            }
            function closeEditModal() { $("#editModal").hide(); }
            function openDeleteModal(productId) {
                currentDeleteId = productId;
                $("#deleteModal").show();
            }
            function closeDeleteModal() { $("#deleteModal").hide(); currentDeleteId = null; }
            // AJAX for edit (with file upload)
            $("#editProductForm").submit(function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                formData.append('CurrentImageURL', currentEditImageUrl);
                $.ajax({
                    url: "../scripts/edit-product.php",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(resp) { location.reload(); }
                });
            });
            // AJAX for delete
            $("#confirmDelete").click(function() {
                if (!currentDeleteId) return;
                $.post("../scripts/delete-product.php", { ProductID: currentDeleteId }, function(resp) {
                    location.reload();
                });
            });
            // Close modals on outside click
            $(window).on('click', function(e) {
                if ($(e.target).is('#editModal')) closeEditModal();
                if ($(e.target).is('#deleteModal')) closeDeleteModal();
            });
            </script>
        </div>
    </div>
    <style>
    .add-product-section {
        border: 1.5px solid #14404b;
        margin: 40px 40px 30px 40px;
        padding: 0 0 30px 0;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(33, 150, 243, 0.07);
    }
    .add-product-title {
        font-size: 2.5rem;
        font-weight: 700;
        margin: 0 0 20px 0;
        padding: 24px 24px 10px 24px;
        border-bottom: 1.5px solid #2196f3;
        color: #14404b;
        letter-spacing: 1px;
    }
    .add-product-form {
        margin: 0;
        padding: 0 24px;
    }
    .add-product-grid {
        display: flex;
        gap: 40px;
        align-items: flex-start;
        margin-top: 24px;
    }
    .image-upload-container {
        width: 320px;
        height: 320px;
        border: 2.5px dashed #2196f3;
        border-radius: 16px;
        background: #f4faff;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        transition: border-color 0.2s;
        box-shadow: 0 2px 8px rgba(33, 150, 243, 0.04);
    }
    .image-upload-label {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        cursor: pointer;
        font-size: 1.5rem;
        color: #14404b;
        font-weight: 500;
        text-align: center;
    }
    .image-upload-content {
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    .upload-icon {
        font-size: 3.5rem;
        color: #2196f3;
        margin-bottom: 18px;
        opacity: 0.85;
    }
    #imagePreview {
        max-width: 90%;
        max-height: 90%;
        display: block;
        margin: 0 auto;
        border-radius: 10px;
        box-shadow: 0 4px 16px rgba(33, 150, 243, 0.13);
        background: #fff;
        padding: 8px;
    }
    #imagePreviewText {
        color: #14404b;
        font-size: 1.3rem;
        font-weight: 500;
        opacity: 0.8;
    }
    .product-fields {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 22px;
        justify-content: flex-start;
    }
    .product-fields-row {
        display: flex;
        gap: 20px;
    }
    .product-input {
        font-size: 1.15rem;
        padding: 16px 14px;
        border: 1.5px solid #b3d6f6;
        border-radius: 6px;
        margin-bottom: 0;
        width: 100%;
        background: #f8f9fa;
        color: #14404b;
        transition: border-color 0.2s;
    }
    .product-input:focus {
        border-color: #2196f3;
        outline: none;
    }
    .add-product-btn {
        background: #2196f3;
        color: #fff;
        font-size: 1.2rem;
        padding: 15px 0;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        margin-top: 18px;
        width: 100%;
        font-weight: 600;
        letter-spacing: 0.5px;
        box-shadow: 0 2px 8px rgba(33, 150, 243, 0.07);
        transition: background 0.2s, box-shadow 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }
    .add-product-btn:hover {
        background: #14404b;
        box-shadow: 0 4px 16px rgba(33, 150, 243, 0.13);
    }
    @media (max-width: 900px) {
        .add-product-grid { flex-direction: column; align-items: stretch; }
        .image-upload-container { margin-bottom: 20px; }
    }
    </style>
    <script>
    // Image preview for add product
    const imageFileInput = document.getElementById('ImageFile');
    const imagePreview = document.getElementById('imagePreview');
    const imagePreviewText = document.getElementById('imagePreviewText');
    if (imageFileInput) {
        imageFileInput.addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(ev) {
                    imagePreview.src = ev.target.result;
                    imagePreview.style.display = 'block';
                    imagePreviewText.style.display = 'none';
                };
                reader.readAsDataURL(this.files[0]);
            } else {
                imagePreview.src = '#';
                imagePreview.style.display = 'none';
                imagePreviewText.style.display = 'block';
            }
        });
    }
    </script>
</body>
</html> 