<?php
require_once 'db_config.php';

// Test database connection
try {
    $pdo = getDbConnection();
} catch (Exception $e) {
    die("Database connection failed");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Event Inventory - BL Moon Enterprises</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <div class="container mobile">
        <div class="header mobile-header">
            <h1>BL Moon Inventory</h1>
            <p>Live Event Tracking</p>
        </div>

        <div class="main-card">
            <button class="refresh-btn" onclick="loadInventory()">Refresh Inventory</button>
            
            <div class="product-selector">
                <label for="productSelect">Product:</label>
                <select id="productSelect" onchange="selectProduct(this.value)">
                    <option value="">Loading products...</option>
                </select>
            </div>

            <div class="current-product" id="currentProduct">Select a product</div>
            
            <div class="quantity-section">
                <div class="quantity-label">Quantity to Sell</div>
                <div class="quantity-controls">
                    <button class="qty-btn qty-btn-minus" onclick="changeQuantity(-1)">−</button>
                    <div class="quantity-display" id="quantityDisplay">1</div>
                    <button class="qty-btn qty-btn-plus" onclick="changeQuantity(1)">+</button>
                </div>
                <button class="sold-button" id="soldButton" onclick="recordSale()">
                    SOLD <span id="soldQuantityText">1</span>
                </button>
            </div>
            
            <div id="statusMessage"></div>
        </div>

        <div class="inventory-card">
            <div class="inventory-title">Current Inventory</div>
            
            <div class="current-stock">
                <div class="current-stock-label" id="currentStockLabel">Select a product</div>
                <div class="current-stock-value" id="currentStockValue">-</div>
            </div>
            
            <div class="inventory-list">
                <div id="inventoryList"></div>
                <div class="inventory-item total-row">
                    <div class="inventory-name">Total Items</div>
                    <div class="inventory-count" id="totalCount">-</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let products = [];
        let currentProductId = null;
        let quantityToSell = 1;

        // Load inventory from database
        async function loadInventory() {
            try {
                const response = await fetch('https://www.blmoonenterprises.com/php/api.php?action=get_inventory');
                const data = await response.json();
                
                if (data.success) {
                    products = data.products.filter(p => p.is_current);
                    populateProductSelector();
                    if (products.length > 0 && !currentProductId) {
                        selectProduct(products[0].product_id);
                    } else if (currentProductId) {
                        updateDisplay();
                    }
                } else {
                    showStatus('Error loading inventory: ' + data.message, 'danger');
                }
            } catch (error) {
                showStatus('Network error: ' + error.message, 'danger');
            }
        }

        // Populate product selector
        function populateProductSelector() {
            const select = document.getElementById('productSelect');
            select.innerHTML = '';
            
            products.forEach(product => {
                const option = document.createElement('option');
                option.value = product.product_id;
                option.textContent = product.product_name;
                select.appendChild(option);
            });
        }

        // Select product
        function selectProduct(productId) {
            currentProductId = parseInt(productId);
            document.getElementById('productSelect').value = productId;
            quantityToSell = 1;
            updateDisplay();
        }

        // Get current product
        function getCurrentProduct() {
            return products.find(p => p.product_id === currentProductId);
        }

        // Change quantity
        function changeQuantity(change) {
            const product = getCurrentProduct();
            if (!product) return;
            
            const newQuantity = quantityToSell + change;
            const maxQuantity = product.current_inventory;
            
            if (newQuantity >= 1 && newQuantity <= maxQuantity) {
                quantityToSell = newQuantity;
                updateQuantityDisplay();
            }
        }

        // Update quantity display
        function updateQuantityDisplay() {
            const product = getCurrentProduct();
            if (!product) return;
            
            document.getElementById('quantityDisplay').textContent = quantityToSell;
            document.getElementById('soldQuantityText').textContent = quantityToSell;
            
            const soldButton = document.getElementById('soldButton');
            const currentStock = product.current_inventory;
            
            if (currentStock === 0) {
                soldButton.disabled = true;
                soldButton.textContent = 'OUT OF STOCK';
            } else if (quantityToSell > currentStock) {
                soldButton.disabled = true;
                soldButton.innerHTML = 'NOT ENOUGH STOCK';
            } else {
                soldButton.disabled = false;
                soldButton.innerHTML = `SOLD <span id="soldQuantityText">${quantityToSell}</span>`;
            }
        }

        // Record sale
        async function recordSale() {
            const product = getCurrentProduct();
            if (!product) return;
            
            const currentStock = product.current_inventory;
            
            if (currentStock < quantityToSell || quantityToSell <= 0) {
                showStatus(`Cannot sell ${quantityToSell} - insufficient stock!`, 'danger');
                return;
            }
            
            try {
                // Subtract from inventory (negative quantity to reduce stock)
                const formData = new FormData();
                formData.append('action', 'add_inventory');
                formData.append('product_id', currentProductId);
                formData.append('quantity', -quantityToSell);

                const response = await fetch('https://www.blmoonenterprises.com/php/api.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showStatus(`SOLD ${quantityToSell} ${product.product_name}`, 'sold');
                    quantityToSell = 1;
                    await loadInventory(); // Refresh inventory
                } else {
                    showStatus('Error: ' + data.message, 'danger');
                }
            } catch (error) {
                showStatus('Network error: ' + error.message, 'danger');
            }
        }

        // Update all displays
        function updateDisplay() {
            updateProductDisplay();
            updateQuantityDisplay();
            updateInventoryDisplay();
        }

        // Update current product display
        function updateProductDisplay() {
            const product = getCurrentProduct();
            if (!product) return;
            
            document.getElementById('currentProduct').textContent = product.product_name;
            
            const currentStock = product.current_inventory;
            document.getElementById('currentStockLabel').textContent = `${product.product_name} in Stock`;
            document.getElementById('currentStockValue').textContent = currentStock;
            
            const stockElement = document.getElementById('currentStockValue');
            stockElement.classList.remove('low-stock', 'out-of-stock');
            
            if (currentStock === 0) {
                stockElement.classList.add('out-of-stock');
            } else if (currentStock <= product.minimum_stock_level) {
                stockElement.classList.add('low-stock');
            }
        }

        // Update inventory list
        function updateInventoryDisplay() {
            const listDiv = document.getElementById('inventoryList');
            listDiv.innerHTML = '';
            
            let total = 0;
            
            products.forEach(product => {
                const count = product.current_inventory;
                total += count;
                
                const div = document.createElement('div');
                div.className = 'inventory-item';
                
                const name = document.createElement('div');
                name.className = 'inventory-name';
                name.textContent = product.product_name;
                
                const countSpan = document.createElement('div');
                countSpan.className = 'inventory-count';
                countSpan.textContent = count;
                
                if (count === 0) {
                    countSpan.classList.add('out-of-stock');
                } else if (count <= product.minimum_stock_level) {
                    countSpan.classList.add('low-stock');
                }
                
                div.appendChild(name);
                div.appendChild(countSpan);
                listDiv.appendChild(div);
            });
            
            document.getElementById('totalCount').textContent = total;
        }

        // Show status message
        function showStatus(message, type) {
            const statusDiv = document.getElementById('statusMessage');
            statusDiv.textContent = message;
            statusDiv.className = `status-message status-${type}`;
            
            setTimeout(() => {
                statusDiv.textContent = '';
                statusDiv.className = '';
            }, 3000);
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadInventory();
        });

        // Auto-refresh every 30 seconds
        setInterval(loadInventory, 30000);
    </script>
</body>
</html>