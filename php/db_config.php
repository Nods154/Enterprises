<?php
// config.php - Database configuration
// Save this as: config.php

// Database connection settings
define('DB_HOST', 'localhost');
define('DB_NAME', 'blmoon_enterprises');
define('DB_USER', 'root');  // Change this to your MySQL username
define('DB_PASS', '3CalicoSister$');  // Change this to your MySQL password

// Create database connection
function getDbConnection() {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", 
                       DB_USER, 
                       DB_PASS, 
                       [
                           PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                           PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                           PDO::ATTR_EMULATE_PREPARES => false
                       ]);
        return $pdo;
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}

// Test connection function
function testConnection() {
    try {
        $pdo = getDbConnection();
        return "Database connection successful!";
    } catch (Exception $e) {
        return "Database connection failed: " . $e->getMessage();
    }
}
?>
