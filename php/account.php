<?php
session_start();
require_once 'db_config.php';

// Check if user is logged in
if (!isset($_SESSION['customer_id'])) {
    header('Location: login.php?redirect=account.php');
    exit();
}

$customer_id = $_SESSION['customer_id'];

// Fetch customer information
$stmt = $conn->prepare("SELECT * FROM customers WHERE customer_id = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
$customer = $result->fetch_assoc();
$stmt->close();

// Fetch customer addresses
$stmt = $conn->prepare("SELECT * FROM customer_addresses WHERE customer_id = ? ORDER BY is_default DESC");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$addresses = $stmt->get_result();
$stmt->close();

// Fetch saved cards
$stmt = $conn->prepare("SELECT * FROM saved_cards WHERE customer_id = ? ORDER BY is_default DESC, card_slot ASC");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$cards = $stmt->get_result();
$stmt->close();

// Fetch recent orders
$stmt = $conn->prepare("SELECT order_id, order_number, total_amount, order_status, created_at FROM orders WHERE customer_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$orders = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - BL Moon Enterprises</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .account-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .account-header {
            background: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .account-header h1 {
            margin: 0 0 10px 0;
            color: #2c3e50;
        }
        
        .account-header p {
            margin: 0;
            color: #7f8c8d;
        }
        
        .account-nav {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        
        .nav-link {
            padding: 10px 20px;
            background: #ecf0f1;
            color: #2c3e50;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: background 0.3s;
        }
        
        .nav-link:hover {
            background: #d5dbdb;
        }
        
        .nav-link.active {
            background: #3498db;
            color: white;
        }
        
        .logout-btn {
            background: #e74c3c;
            color: white;
            margin-left: auto;
        }
        
        .logout-btn:hover {
            background: #c0392b;
        }
        
        .account-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
        }
        
        .account-section {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #ecf0f1;
        }
        
        .section-header h2 {
            margin: 0;
            color: #2c3e50;
            font-size: 20px;
        }
        
        .section-link {
            color: #3498db;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }
        
        .section-link:hover {
            text-decoration: underline;
        }
        
        .info-item {
            margin-bottom: 15px;
        }
        
        .info-label {
            font-weight: 600;
            color: #7f8c8d;
            font-size: 13px;
            margin-bottom: 5px;
        }
        
        .info-value {
            color: #2c3e50;
            font-size: 15px;
        }
        
        .address-card, .card-item {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        
        .default-badge {
            display: inline-block;
            background: #27ae60;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            margin-left: 10px;
        }
        
        .order-item {
            padding: 15px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        
        .order-number {
            font-weight: 600;
            color: #2c3e50;
        }
        
        .order-status {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-processing { background: #cce5ff; color: #004085; }
        .status-shipped { background: #d1ecf1; color: #0c5460; }
        .status-delivered { background: #d5f4e6; color: #27ae60; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        
        .order-details {
            font-size: 14px;
            color: #7f8c8d;
        }
        
        .empty-state {
            text-align: center;
            padding: 30px;
            color: #7f8c8d;
        }
        
        @media (max-width: 768px) {
            .account-grid {
                grid-template-columns: 1fr;
            }
            
            .account-nav {
                flex-direction: column;
            }
            
            .logout-btn {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="account-container">
        <div class="account-header">
            <h1>Welcome back, <?php echo htmlspecialchars($customer['first_name']); ?>!</h1>
            <p>Manage your account, orders, and preferences</p>
            
            <div class="account-nav">
                <a href="account.php" class="nav-link active">Dashboard</a>
                <a href="orders.php" class="nav-link">Orders</a>
                <a href="addresses.php" class="nav-link">Addresses</a>
                <a href="payment-methods.php" class="nav-link">Payment Methods</a>
                <a href="profile.php" class="nav-link">Profile Settings</a>
                <a href="logout.php" class="nav-link logout-btn">Logout</a>
            </div>
        </div>
        
        <div class="account-grid">
            <!-- Account Information -->
            <div class="account-section">
                <div class="section-header">
                    <h2>Account Information</h2>
                    <a href="profile.php" class="section-link">Edit</a>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Name</div>
                    <div class="info-value"><?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?></div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Email</div>
                    <div class="info-value"><?php echo htmlspecialchars($customer['email']); ?></div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Phone</div>
                    <div class="info-value"><?php echo $customer['phone'] ? htmlspecialchars($customer['phone']) : 'Not provided'; ?></div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Member Since</div>
                    <div class="info-value"><?php echo date('F j, Y', strtotime($customer['created_at'])); ?></div>
                </div>
            </div>
            
            <!-- Addresses -->
            <div class="account-section">
                <div class="section-header">
                    <h2>Addresses</h2>
                    <a href="addresses.php" class="section-link">Manage</a>
                </div>
                
                <?php if ($addresses->num_rows > 0): ?>
                    <?php while ($address = $addresses->fetch_assoc()): ?>
                        <div class="address-card">
                            <strong><?php echo ucfirst($address['address_type']); ?> Address</strong>
                            <?php if ($address['is_default']): ?>
                                <span class="default-badge">Default</span>
                            <?php endif; ?>
                            <div style="margin-top: 8px; font-size: 14px; color: #555;">
                                <?php echo htmlspecialchars($address['address_line1']); ?><br>
                                <?php if ($address['address_line2']): ?>
                                    <?php echo htmlspecialchars($address['address_line2']); ?><br>
                                <?php endif; ?>
                                <?php echo htmlspecialchars($address['city'] . ', ' . $address['state'] . ' ' . $address['zip_code']); ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state">No addresses saved yet.</div>
                <?php endif; ?>
            </div>
            
            <!-- Payment Methods -->
            <div class="account-section">
                <div class="section-header">
                    <h2>Payment Methods</h2>
                    <a href="payment-methods.php" class="section-link">Manage</a>
                </div>
                
                <?php if ($cards->num_rows > 0): ?>
                    <?php while ($card = $cards->fetch_assoc()): ?>
                        <div class="card-item">
                            <strong><?php echo htmlspecialchars($card['card_type']); ?> •••• <?php echo htmlspecialchars($card['last_four_digits']); ?></strong>
                            <?php if ($card['is_default']): ?>
                                <span class="default-badge">Default</span>
                            <?php endif; ?>
                            <?php if ($card['card_nickname']): ?>
                                <div style="margin-top: 5px; font-size: 13px; color: #7f8c8d;">
                                    <?php echo htmlspecialchars($card['card_nickname']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state">No payment methods saved yet.</div>
                <?php endif; ?>
            </div>
            
            <!-- Recent Orders -->
            <div class="account-section">
                <div class="section-header">
                    <h2>Recent Orders</h2>
                    <a href="orders.php" class="section-link">View All</a>
                </div>
                
                <?php if ($orders->num_rows > 0): ?>
                    <?php while ($order = $orders->fetch_assoc()): ?>
                        <div class="order-item">
                            <div class="order-header">
                                <span class="order-number">#<?php echo htmlspecialchars($order['order_number']); ?></span>
                                <span class="order-status status-<?php echo $order['order_status']; ?>">
                                    <?php echo ucfirst($order['order_status']); ?>
                                </span>
                            </div>
                            <div class="order-details">
                                <?php echo date('F j, Y', strtotime($order['created_at'])); ?> • 
                                $<?php echo number_format($order['total_amount'], 2); ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state">No orders yet. Start shopping!</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
