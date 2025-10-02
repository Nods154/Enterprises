<?php
// product_admin.php - Product Administration Interface
require_once 'db_config.php';

try {
    $pdo = getDbConnection();
    $connectionStatus = "Connected to MySQL database successfully";
    $connectionClass = "success";
} catch (Exception $e) {
    $connectionStatus = "Database connection failed: " . $e->getMessage();
    $connectionClass = "error";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Administration - BL Moon Enterprises</title>
    <style>
        * {
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Product Administration</h1>
            <p>Manage Products - BL Moon Enterprises</p>
        </div>

        <div class="nav-buttons">
            <a href="inventory_manager.php" class="nav-btn nav-btn-secondary">Back to Inventory</a>
        </div>

        <button class="add-product-btn" onclick="showAddProductModal()">+ Add New Product</button>

        <div id="messageArea"></div>

        <table class="products-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Material Cost</th>
                    <th>Labor</th>
                    <th>Profit/Unit</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="productsTableBody">
                <!-- Products will be loaded here -->
            </tbody>
        </table>
    </div>

    <!-- Add Product Modal -->
    <div id="addProductModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Product</h2>
                <button class="close-btn" onclick="closeAddProductModal()">&times;</button>
            </div>
            <form id="addProductForm" onsubmit="handleAddProduct(event)">
                <div class="form-group">
                    <label for="product_name">Product Name *</label>
                    <input type="text" id="product_name" name="product_name" required>
                </div>
                
                <div class="form-group">
                    <label for="product_description">Description</label>
                    <textarea id="product_description" name="product_description"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="category">Category</label>
                    <input type="text" id="category" name="category" placeholder="e.g., Earrings, Pins">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="selling_price">Selling Price ($) *</label>
                        <input type="number" id="selling_price" name="selling_price" step="0.01" min="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="material_cost">Material Cost ($)</label>
                        <input type="number" id="material_cost" name="material_cost" step="0.01" min="0" value="0">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="labor_hours">Labor Hours</label>
                        <input type="number" id="labor_hours" name="labor_hours" step="0.1" min="0" value="0">
                    </div>
                    
                    <div class="form-group">
                        <label for="labor_rate_per_hour">Labor Rate ($/hour)</label>
                        <input type="number" id="labor_rate_per_hour" name="labor_rate_per_hour" step="0.01" min="0" value="0">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="minimum_stock_level">Minimum Stock Level</label>
                    <input type="number" id="minimum_stock_level" name="minimum_stock_level" min="0" value="5">
                </div>
                
                <div class="form-group">
                    <label>Product Image</label>
                    <div class="image-upload" onclick="document.getElementById('product_image').click()">
                        <input type="file" id="product_image" accept="image/*" onchange="previewImage(event)">
                        <p>Click to upload image</p>
                        <p style="font-size: 12px; color: #7f8c8d;">JPG, PNG, GIF, WebP (Max 5MB)</p>
                        <img id="imagePreview" class="preview-image" style="display: none;">
                    </div>
                    <input type="hidden" id="image_url" name="image_url">
                </div>
                
                <button type="submit" class="submit-btn">Add Product</button>
            </form>
        </div>
    </div>

    <!-- Edit Image Modal -->
    <div id="editImageModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Change Product Image</h2>
                <button class="close-btn" onclick="closeEditImageModal()">&times;</button>
            </div>
            <div class="form-group">
                <div class="image-upload" onclick="document.getElementById('edit_product_image').click()">
                    <input type="file" id="edit_product_image" accept="image/*" onchange="handleImageEdit(event)">
                    <p>Click to upload new image</p>
                    <img id="editImagePreview" class="preview-image" style="display: none;">
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentEditProductId = null;

        async function loadProducts() {
            try {
                const response = await fetch('https://www.blmoonenterprises.com/php/api.php?action=get_inventory');
                const data = await response.json();
                
                if (data.success) {
                    renderProductsTable(data.products);
                } else {
                    showMessage("Error loading products: " + data.message, "error");
                }
            } catch (error) {
                showMessage("Network error: " + error.message, "error");
            }
        }

        function renderProductsTable(products) {
            const tbody = document.getElementById('productsTableBody');
            tbody.innerHTML = '';

            products.forEach(product => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>
                        ${product.image_url 
                            ? `<img src="${product.image_url}" class="product-thumbnail" alt="${product.product_name}">`
                            : `<div class="no-image">No Image</div>`
                        }
                        <br><button class="action-btn btn-edit" onclick="showEditImageModal(${product.product_id})">Change</button>
                    </td>
                    <td>
                        <div class="editable" onclick="editField(${product.product_id}, 'product_name', this)">
                            <strong>${product.product_name}</strong>
                        </div>
                        <div class="editable" onclick="editField(${product.product_id}, 'category', this)" style="font-size: 12px; color: #7f8c8d;">
                            ${product.category || 'No category'}
                        </div>
                    </td>
                    <td class="editable" onclick="editField(${product.product_id}, 'selling_price', this)">
                        $${parseFloat(product.selling_price || 0).toFixed(2)}
                    </td>
                    <td class="editable" onclick="editField(${product.product_id}, 'material_cost', this)">
                        $${parseFloat(product.material_cost || 0).toFixed(2)}
                    </td>
                    <td>
                        <div class="editable" onclick="editField(${product.product_id}, 'labor_hours', this)">
                            ${parseFloat(product.labor_hours || 0).toFixed(1)} hrs
                        </div>
                        <div class="editable" onclick="editField(${product.product_id}, 'labor_rate_per_hour', this)" style="font-size: 12px;">
                            @ $${parseFloat(product.labor_rate_per_hour || 0).toFixed(2)}/hr
                        </div>
                    </td>
                    <td>
                        $${parseFloat(product.profit_per_unit || 0).toFixed(2)}
                    </td>
                    <td>
                        <button class="action-btn btn-delete" onclick="discontinueProduct(${product.product_id})">
                            Discontinue
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        function editField(productId, field, element) {
            const currentValue = element.textContent.trim().replace(/[^\d.]/g, '');
            const input = document.createElement('input');
            input.type = field.includes('price') || field.includes('cost') || field.includes('rate') || field.includes('hours') ? 'number' : 'text';
            input.step = '0.01';
            input.className = 'edit-input';
            input.value = currentValue;
            
            input.onblur = async function() {
                const newValue = this.value;
                if (newValue !== currentValue) {
                    await updateProductField(productId, field, newValue);
                    loadProducts();
                } else {
                    element.textContent = currentValue;
                }
            };
            
            input.onkeypress = function(e) {
                if (e.key === 'Enter') {
                    this.blur();
                }
            };
            
            element.textContent = '';
            element.appendChild(input);
            input.focus();
        }

        async function updateProductField(productId, field, value) {
            try {
                const formData = new FormData();
                formData.append('action', 'update_product_field');
                formData.append('product_id', productId);
                formData.append('field', field);
                formData.append('value', value);

                const response = await fetch('https://www.blmoonenterprises.com/php/api.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showMessage("Product updated successfully", "success");
                } else {
                    showMessage("Error: " + data.message, "error");
                }
            } catch (error) {
                showMessage("Network error: " + error.message, "error");
            }
        }

        function showAddProductModal() {
            document.getElementById('addProductModal').style.display = 'block';
        }

        function closeAddProductModal() {
            document.getElementById('addProductModal').style.display = 'none';
            document.getElementById('addProductForm').reset();
            document.getElementById('imagePreview').style.display = 'none';
            document.getElementById('image_url').value = '';
        }

        async function handleAddProduct(event) {
            event.preventDefault();
            
            // Upload image first if selected
            const imageFile = document.getElementById('product_image').files[0];
            if (imageFile) {
                const imageUrl = await uploadImage(imageFile);
                if (imageUrl) {
                    document.getElementById('image_url').value = imageUrl;
                }
            }
            
            const formData = new FormData(event.target);
            formData.append('action', 'add_product');

            try {
                const response = await fetch('https://www.blmoonenterprises.com/php/api.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showMessage("Product added successfully!", "success");
                    closeAddProductModal();
                    loadProducts();
                } else {
                    showMessage("Error: " + data.message, "error");
                }
            } catch (error) {
                showMessage("Network error: " + error.message, "error");
            }
        }

        async function uploadImage(file) {
            const formData = new FormData();
            formData.append('action', 'upload_image');
            formData.append('image', file);

            try {
                const response = await fetch('https://www.blmoonenterprises.com/php/api.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    return data.image_url;
                } else {
                    showMessage("Image upload error: " + data.message, "error");
                    return null;
                }
            } catch (error) {
                showMessage("Upload error: " + error.message, "error");
                return null;
            }
        }

        function previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('imagePreview');
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        }

      function showEditImageModal(productId) {
            currentEditProductId = productId;
            document.getElementById('editImageModal').style.display = 'block';
            document.getElementById('editImagePreview').style.display = 'none';
        }

        function closeEditImageModal() {
            document.getElementById('editImageModal').style.display = 'none';
            currentEditProductId = null;
        }

        async function handleImageEdit(event) {
            const file = event.target.files[0];
            if (!file) return;

            // Preview
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('editImagePreview');
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);

            // Upload
            const imageUrl = await uploadImage(file);
            if (imageUrl && currentEditProductId) {
                await updateProductField(currentEditProductId, 'image_url', imageUrl);
                showMessage("Image updated successfully!", "success");
                closeEditImageModal();
                loadProducts();
            }
        }

        async function discontinueProduct(productId) {
            if (!confirm('Are you sure you want to discontinue this product?')) {
                return;
            }

            try {
                const formData = new FormData();
                formData.append('action', 'delete_product');
                formData.append('product_id', productId);

                const response = await fetch('https://www.blmoonenterprises.com/php/api.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showMessage("Product discontinued", "success");
                    loadProducts();
                } else {
                    showMessage("Error: " + data.message, "error");
                }
            } catch (error) {
                showMessage("Network error: " + error.message, "error");
            }
        }

        function showMessage(text, type) {
            const messageArea = document.getElementById('messageArea');
            messageArea.innerHTML = `<div class="message ${type}">${text}</div>`;
            
            setTimeout(() => {
                messageArea.innerHTML = '';
            }, 3000);
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            const addModal = document.getElementById('addProductModal');
            const editModal = document.getElementById('editImageModal');
            if (event.target === addModal) {
                closeAddProductModal();
            }
            if (event.target === editModal) {
                closeEditImageModal();
            }
        };

        // Load products on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadProducts();
        });
    </script>
</body>
</html>