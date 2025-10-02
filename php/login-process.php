<?php
session_start();
require_once 'db_config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);
    
    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Please enter both email and password.']);
        exit();
    }
    
    // Check credentials
    $stmt = $conn->prepare("SELECT customer_id, password_hash, first_name, last_name, account_status FROM customers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Check account status
        if ($user['account_status'] !== 'active') {
            echo json_encode(['success' => false, 'message' => 'Your account is not active. Please contact support.']);
            exit();
        }
        
        if (password_verify($password, $user['password_hash'])) {
            // Successful login
            $_SESSION['customer_id'] = $user['customer_id'];
            $_SESSION['customer_email'] = $email;
            $_SESSION['customer_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['customer_first_name'] = $user['first_name'];
            
            // Update last login
            $update_stmt = $conn->prepare("UPDATE customers SET last_login = NOW() WHERE customer_id = ?");
            $update_stmt->bind_param("i", $user['customer_id']);
            $update_stmt->execute();
            $update_stmt->close();
            
            // Handle "Remember Me"
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                setcookie('remember_token', $token, time() + (86400 * 30), '/', '', true, true); // 30 days
            }
            
            echo json_encode([
                'success' => true, 
                'message' => 'Login successful!',
                'name' => $user['first_name'] . ' ' . $user['last_name']
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
