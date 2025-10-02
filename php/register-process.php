<?php
session_start();
require_once 'db_config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $phone = trim($_POST['phone']);
    
    // Validation
    if (empty($email) || empty($password) || empty($first_name) || empty($last_name)) {
        echo json_encode(['success' => false, 'message' => 'All required fields must be filled out.']);
        exit();
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
        exit();
    }
    
    if (strlen($password) < 8) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters long.']);
        exit();
    }
    
    if ($password !== $confirm_password) {
        echo json_encode(['success' => false, 'message' => 'Passwords do not match.']);
        exit();
    }
    
    // Check if email already exists
    $stmt = $conn->prepare("SELECT customer_id FROM customers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'An account with this email already exists.']);
        exit();
    }
    
    // Hash password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Generate verification token
    $verification_token = bin2hex(random_bytes(32));
    
    // Insert new customer
    $stmt = $conn->prepare("INSERT INTO customers (email, password_hash, first_name, last_name, phone, verification_token, email_verified) VALUES (?, ?, ?, ?, ?, ?, 0)");
    $stmt->bind_param("ssssss", $email, $password_hash, $first_name, $last_name, $phone, $verification_token);
    
    if ($stmt->execute()) {
        $customer_id = $conn->insert_id();
        
        // Set session variables
        $_SESSION['customer_id'] = $customer_id;
        $_SESSION['customer_email'] = $email;
        $_SESSION['customer_name'] = $first_name . ' ' . $last_name;
        $_SESSION['customer_first_name'] = $first_name;
        
        // In production, send verification email here
        // mail($email, "Verify your account", "Click here to verify: https://yoursite.com/verify.php?token=$verification_token");
        
        echo json_encode([
            'success' => true, 
            'message' => 'Account created successfully!',
            'name' => $first_name . ' ' . $last_name
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Registration failed. Please try again.']);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
