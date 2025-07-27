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
    // index.php, after db_conn.php
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
}

} catch (PDOException $e) {
    // Handle connection errors 
    die("Connection failed: " . $e->getMessage());  
}
?>