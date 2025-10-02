<?php
// api.php - Main API endpoint
// Save this as: api.php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once 'db_config.php';

// Get the action from the request
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    $pdo = getDbConnection();
    
    switch ($action) {
        case 'get_inventory':
            getInventory($pdo);
            break;
            
        case 'add_inventory':
            addInventory($pdo);
            break;
            
        case 'update_product':
            updateProduct($pdo);
            break;
            
        case 'test_connection':
            echo json_encode(['success' => true, 'message' => 'Database connection successful!']);
            break;
			
		case 'add_product':
			addProduct($pdo);
			break;
    
		case 'update_product_field':
			updateProductField($pdo);
			break;
    
		case 'upload_image':
			uploadImage();
			break;
    
		case 'delete_product':
			deleteProduct($pdo);
			break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

function getInventory($pdo) {
    $sql = "SELECT 
                product_id,
                product_name,
                product_description,
				image_url,
                category,
                selling_price,
                material_cost,
                labor_hours,
                labor_rate_per_hour,
                total_production_cost,
                profit_per_unit,
                current_inventory,
                minimum_stock_level,
                is_current,
                status,
                created_at,
                updated_at
            FROM products 
            ORDER BY category, product_name";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $products = $stmt->fetchAll();
    
    // Calculate summary statistics
    $totalProducts = count($products);
    $currentProducts = count(array_filter($products, function($p) { return $p['is_current']; }));
    $totalInventory = array_sum(array_column($products, 'current_inventory'));
    $lowStockItems = count(array_filter($products, function($p) { 
        return $p['current_inventory'] <= $p['minimum_stock_level'] && $p['is_current']; 
    }));
    
    $summary = [
        'total_products' => $totalProducts,
        'current_products' => $currentProducts,
        'total_inventory' => $totalInventory,
        'low_stock_items' => $lowStockItems
    ];
    
    echo json_encode([
        'success' => true,
        'products' => $products,
        'summary' => $summary
    ]);
}

function addInventory($pdo) {
    $productId = $_POST['product_id'] ?? 0;
    $quantityToAdd = $_POST['quantity'] ?? 0;
    
    if (!$productId || !$quantityToAdd || $quantityToAdd <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product ID or quantity']);
        return;
    }
    
    // Update the inventory
    $sql = "UPDATE products 
            SET current_inventory = current_inventory + :quantity,
                updated_at = CURRENT_TIMESTAMP
            WHERE product_id = :product_id";
    
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        ':quantity' => $quantityToAdd,
        ':product_id' => $productId
    ]);
    
    if ($result && $stmt->rowCount() > 0) {
        // Get the updated product info
        $sql = "SELECT product_name, current_inventory FROM products WHERE product_id = :product_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':product_id' => $productId]);
        $product = $stmt->fetch();
        
        echo json_encode([
            'success' => true,
            'message' => "Added {$quantityToAdd} units to {$product['product_name']}",
            'new_inventory' => $product['current_inventory']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update inventory']);
    }
}

function updateProduct($pdo) {
    $productId = $_POST['product_id'] ?? 0;
    $field = $_POST['field'] ?? '';
    $value = $_POST['value'] ?? '';
    
    if (!$productId || !$field) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        return;
    }
    
    // Whitelist allowed fields for security
    $allowedFields = [
        'product_name', 'product_description', 'category', 'selling_price',
        'material_cost', 'labor_hours', 'labor_rate_per_hour',
        'current_inventory', 'minimum_stock_level', 'is_current', 'status'
    ];
    
    if (!in_array($field, $allowedFields)) {
        echo json_encode(['success' => false, 'message' => 'Field not allowed']);
        return;
    }
    
    $sql = "UPDATE products SET {$field} = :value, updated_at = CURRENT_TIMESTAMP WHERE product_id = :product_id";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        ':value' => $value,
        ':product_id' => $productId
    ]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Product updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update product']);
    }
}
function addProduct($pdo) {
    $data = [
        'product_name' => $_POST['product_name'] ?? '',
        'product_description' => $_POST['product_description'] ?? '',
        'category' => $_POST['category'] ?? '',
        'selling_price' => $_POST['selling_price'] ?? 0,
        'material_cost' => $_POST['material_cost'] ?? 0,
        'labor_hours' => $_POST['labor_hours'] ?? 0,
        'labor_rate_per_hour' => $_POST['labor_rate_per_hour'] ?? 0,
        'minimum_stock_level' => $_POST['minimum_stock_level'] ?? 5,
        'image_url' => $_POST['image_url'] ?? null
    ];
    
    if (empty($data['product_name'])) {
        echo json_encode(['success' => false, 'message' => 'Product name is required']);
        return;
    }
    
    // Calculate production cost and profit
    $totalProductionCost = $data['material_cost'] + ($data['labor_hours'] * $data['labor_rate_per_hour']);
    $profitPerUnit = $data['selling_price'] - $totalProductionCost;
    
    $sql = "INSERT INTO products (
                product_name, product_description, category, selling_price,
                material_cost, labor_hours, labor_rate_per_hour,
                total_production_cost, profit_per_unit, current_inventory,
                minimum_stock_level, is_current, status, image_url
            ) VALUES (
                :product_name, :product_description, :category, :selling_price,
                :material_cost, :labor_hours, :labor_rate_per_hour,
                :total_production_cost, :profit_per_unit, 0,
                :minimum_stock_level, 1, 'active', :image_url
            )";
    
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        ':product_name' => $data['product_name'],
        ':product_description' => $data['product_description'],
        ':category' => $data['category'],
        ':selling_price' => $data['selling_price'],
        ':material_cost' => $data['material_cost'],
        ':labor_hours' => $data['labor_hours'],
        ':labor_rate_per_hour' => $data['labor_rate_per_hour'],
        ':total_production_cost' => $totalProductionCost,
        ':profit_per_unit' => $profitPerUnit,
        ':minimum_stock_level' => $data['minimum_stock_level'],
        ':image_url' => $data['image_url']
    ]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Product added successfully',
            'product_id' => $pdo->lastInsertId()
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add product']);
    }
}

function updateProductField($pdo) {
    $productId = $_POST['product_id'] ?? 0;
    $field = $_POST['field'] ?? '';
    $value = $_POST['value'] ?? '';
    
    if (!$productId || !$field) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        return;
    }
    
    // Whitelist allowed fields
    $allowedFields = [
        'product_name', 'product_description', 'category', 'selling_price',
        'material_cost', 'labor_hours', 'labor_rate_per_hour',
        'minimum_stock_level', 'is_current', 'status', 'image_url'
    ];
    
    if (!in_array($field, $allowedFields)) {
        echo json_encode(['success' => false, 'message' => 'Field not allowed']);
        return;
    }
    
    // If updating pricing fields, recalculate costs
    if (in_array($field, ['selling_price', 'material_cost', 'labor_hours', 'labor_rate_per_hour'])) {
        // Get current values
        $stmt = $pdo->prepare("SELECT material_cost, labor_hours, labor_rate_per_hour, selling_price FROM products WHERE product_id = ?");
        $stmt->execute([$productId]);
        $current = $stmt->fetch();
        
        // Update the changed field
        $current[$field] = $value;
        
        // Recalculate
        $totalProductionCost = $current['material_cost'] + ($current['labor_hours'] * $current['labor_rate_per_hour']);
        $profitPerUnit = $current['selling_price'] - $totalProductionCost;
        
        // Update all related fields
        $sql = "UPDATE products 
                SET {$field} = :value,
                    total_production_cost = :total_cost,
                    profit_per_unit = :profit,
                    updated_at = CURRENT_TIMESTAMP
                WHERE product_id = :product_id";
        
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            ':value' => $value,
            ':total_cost' => $totalProductionCost,
            ':profit' => $profitPerUnit,
            ':product_id' => $productId
        ]);
    } else {
        // Simple field update
        $sql = "UPDATE products SET {$field} = :value, updated_at = CURRENT_TIMESTAMP WHERE product_id = :product_id";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([':value' => $value, ':product_id' => $productId]);
    }
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Product updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update product']);
    }
}

function uploadImage() {
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error']);
        return;
    }
    
    $file = $_FILES['image'];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    
    if (!in_array($file['type'], $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, GIF, and WebP allowed']);
        return;
    }
    
    // Limit file size to 5MB
    if ($file['size'] > 5 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'File too large. Maximum 5MB']);
        return;
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('product_') . '.' . $extension;
    $uploadPath = __DIR__ . '/images/products/' . $filename;
    
    // Create directory if it doesn't exist
    if (!file_exists(__DIR__ . '/images/products')) {
        mkdir(__DIR__ . '/images/products', 0755, true);
    }
    
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        $webPath = '/php/images/products/' . $filename;
        echo json_encode([
            'success' => true,
            'message' => 'Image uploaded successfully',
            'image_url' => $webPath
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save image']);
    }
}

function deleteProduct($pdo) {
    $productId = $_POST['product_id'] ?? 0;
    
    if (!$productId) {
        echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
        return;
    }
    
    // Soft delete by setting is_current to 0
    $sql = "UPDATE products SET is_current = 0, status = 'discontinued', updated_at = CURRENT_TIMESTAMP WHERE product_id = :product_id";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([':product_id' => $productId]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Product discontinued']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to discontinue product']);
    }
}
?>