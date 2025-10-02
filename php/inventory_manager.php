<?php
// inventory_manager.php - Complete HTML page with PHP integration
// Save this as: inventory_manager.php

require_once 'db_config.php';

// Test database connection
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
    <title>BL Moon Enterprises - Inventory Management</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>BL Moon Enterprises</h1>
            <p>Inventory Management System</p>
        </div>

        <div class="connection-status <?php echo $connectionClass; ?>">
            <?php echo $connectionStatus; ?>
        </div>

		
<button class="refresh-btn" onclick="loadInventory()">🔄 Refresh Inventory</button>
<a href="product_admin.php" class="refresh-btn" style="background: #e67e22; text-decoration: none; display: inline-block;">⚙️ Manage Products</a>
		
        <div class="summary-cards" id="summaryCards">
            <div class="summary-card">
                <h3>Total Products</h3>
                <p class="value" id="totalProducts">-</p>
            </div>
            <div class="summary-card">
                <h3>Current Items</h3>
                <p class="value" id="currentItems">-</p>
            </div>
            <div class="summary-card">
                <h3>Total Inventory</h3>
                <p class="value" id="totalInventory">-</p>
            </div>
            <div class="summary-card">
                <h3>Low Stock Items</h3>
                <p class="value" id="lowStockItems">-</p>
            </div>
        </div>

        <div id="messageArea"></div>

        <table class="inventory-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Status</th>
                    <th>Current Stock</th>
                    <th>Add Inventory</th>
                    <th>Pricing Info</th>
                </tr>
            </thead>
            <tbody id="inventoryTableBody">
                <!-- Data will be loaded here -->
            </tbody>
        </table>
    </div>

    <script>
        // Load inventory data from the PHP API
        async function loadInventory() {
            try {
                const response = await fetch('/php/api.php?action=get_inventory');
                const data = await response.json();
                
                if (data.success) {
                    updateSummaryCards(data.summary);
                    renderInventoryTable(data.products);
                    showMessage("Inventory refreshed successfully!", "success");
                } else {
                    showMessage("Error loading inventory: " + data.message, "error");
                }
            } catch (error) {
                showMessage("Network error: " + error.message, "error");
            }
        }

        function updateSummaryCards(summary) {
            document.getElementById('totalProducts').textContent = summary.total_products;
            document.getElementById('currentItems').textContent = summary.current_products;
            document.getElementById('totalInventory').textContent = summary.total_inventory;
            document.getElementById('lowStockItems').textContent = summary.low_stock_items;
        }

function renderInventoryTable(products) {
    const tbody = document.getElementById('inventoryTableBody');
    tbody.innerHTML = '';

    products.forEach(item => {
        const row = document.createElement('tr');
        
        // Determine stock status
        let stockClass = 'in-stock';
        if (item.current_inventory == 0) {
            stockClass = 'out-of-stock';
        } else if (item.current_inventory <= item.minimum_stock_level) {
            stockClass = 'low-stock';
        }

        row.innerHTML = `
            <td>
                <div style="display: flex; align-items: center; gap: 12px;">
                    ${item.image_url ? `<img src="${item.image_url}" alt="${item.product_name}" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px; flex-shrink: 0;">` : `<div style="width: 60px; height: 60px; background: #e0e0e0; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #999; font-size: 12px; flex-shrink: 0;">No Image</div>`}
                    <div>
                        <div class="product-name">${item.product_name}</div>
                        <div class="category">${item.category || ''}</div>
                    </div>
                </div>
            </td>
            <td>
                <span class="status-${item.is_current ? 'current' : 'discontinued'}">
                    ${item.is_current ? 'Current' : 'Discontinued'}
                </span>
            </td>
            <td>
                <div class="quantity ${stockClass}">${item.current_inventory}</div>
            </td>
            <td>
                <div class="add-inventory">
                    <input type="number" class="add-input" id="add-${item.product_id}" min="1" max="999" value="1">
                    <button class="add-btn" onclick="addInventory(${item.product_id})">Add</button>
                </div>
            </td>
            <td>
                <div>$${parseFloat(item.selling_price || 0).toFixed(2)} sale price</div>
                <div class="profit-info">$${parseFloat(item.profit_per_unit || 0).toFixed(2)} profit/unit</div>
            </td>
        `;
        
        tbody.appendChild(row);
    });
}
        async function addInventory(productId) {
            const inputElement = document.getElementById(`add-${productId}`);
            const quantityToAdd = parseInt(inputElement.value);
            
            if (quantityToAdd <= 0 || isNaN(quantityToAdd)) {
                showMessage("Please enter a valid quantity", "error");
                return;
            }

            try {
                const formData = new FormData();
                formData.append('action', 'add_inventory');
                formData.append('product_id', productId);
                formData.append('quantity', quantityToAdd);

                const response = await fetch('/php/api.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showMessage(data.message, "success");
                    inputElement.value = "1"; // Reset input
                    loadInventory(); // Refresh display
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
            
            // Clear message after 3 seconds
            setTimeout(() => {
                messageArea.innerHTML = '';
            }, 3000);
        }

        // Initialize the page
        document.addEventListener('DOMContentLoaded', function() {
            loadInventory();
        });
    </script>
</body>
</html>