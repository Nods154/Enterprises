<?php
session_start();

header('Content-Type: application/json');

if (isset($_SESSION['customer_id'])) {
    echo json_encode([
        'logged_in' => true,
        'customer_id' => $_SESSION['customer_id'],
        'email' => $_SESSION['customer_email'],
        'name' => $_SESSION['customer_name'],
        'first_name' => $_SESSION['customer_first_name']
    ]);
} else {
    echo json_encode(['logged_in' => false]);
}
?>
