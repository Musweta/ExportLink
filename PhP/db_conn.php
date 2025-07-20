<?php
// Database connection using PDO for secure MySQL interaction
try {
    $dsn = "mysql:host=localhost;dbname=isproject;charset=utf8mb4";
    $username = "root"; 
    $password = "student"; 

    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    // Handle connection errors 
    die("Connection failed: " . $e->getMessage());  
}
?>