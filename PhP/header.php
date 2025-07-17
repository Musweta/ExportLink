<?php
// Start session for user authentication
session_start();

// Include database connection
require_once 'db_conn.php';

// Check if user is logged in and fetch role
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT role, username, approval_status FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    // Restrict access if not approved
    if ($user && $user['approval_status'] != 'approved') {
        session_unset();
        session_destroy();
        header("Location: login.php?error=account_not_approved");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta tags for responsive viewport -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ExportLink</title>
    <!-- Bootstrap for responsive design -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Link to page-specific CSS -->
    <link rel="stylesheet" href="../CSS/<?php echo basename($_SERVER['PHP_SELF'], '.php'); ?>.css">
</head>
<body>
    <!-- Responsive header with navbar -->
    <header class="bg-light p-3">
        <nav class="navbar navbar-expand-lg navbar-light">
            <div class="container-fluid">
                <a class="navbar-brand" href="index.php">ExportLink</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                        <?php if (isset($user)): ?>
                            <li class="nav-item"><span class="nav-link">Welcome, <?php echo htmlspecialchars($user['username']); ?></span></li>
                            <?php if ($user['role'] == 'farmer'): ?>
                                <li class="nav-item"><a class="nav-link" href="farmerDashboard.php">Dashboard</a></li>
                                <li class="nav-item"><a class="nav-link" href="productListing.php">List Products</a></li>
                            <?php elseif ($user['role'] == 'importer'): ?>
                                <li class="nav-item"><a class="nav-link" href="farmerDashboard.php">Dashboard</a></li>
                                <li class="nav-item"><a class="nav-link" href="orderManagement.php">Orders</a></li>
                            <?php elseif ($user['role'] == 'admin'): ?>
                                <li class="nav-item"><a class="nav-link" href="adminDashboard.php">Admin Dashboard</a></li>
                                <li class="nav-item"><a class="nav-link" href="approveUsers.php">Approve Users</a></li>
                                <li class="nav-item"><a class="nav-link" href="manageUsers.php">Manage Users</a></li>
                            <?php endif; ?>
                            <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                        <?php else: ?>
                            <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                            <li class="nav-item"><a class="nav-link" href="registration.php">Register</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>