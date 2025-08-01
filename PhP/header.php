<?php
// Start the PHP session to manage user login state
session_start();
// Include database connection file
require_once 'db_conn.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Initialize $user array with default values to avoid undefined index errors
$user = ['role' => '', 'username' => '', 'is_approved' => 0];

// Fetch user data if logged in
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("SELECT username, role, is_approved FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch() ?: $user; // Use fetched data or default
        if (!$user) {
            session_unset();
            session_destroy();
            header("Location: login.php");
            exit;
        }
        if ($user['role'] != 'admin' && !$user['is_approved']) {
            session_unset();
            session_destroy();
            header("Location: login.php?error=awaiting_approval");
            exit;
        }
        // Set role in session for consistency across pages
        $_SESSION['role'] = $user['role'];
    } catch (PDOException $e) {
        error_log("Header user fetch error: " . $e->getMessage());
        echo "<div class='alert alert-danger'>Database error. Please try again later.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ExportLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Include Bootstrap JS for interactive elements like modals and buttons -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body { background-color: #f0f8ff; }
        .navbar { background-color: #4682b4; }
        .navbar-brand, .nav-link { color: #ffffff !important; }
        .nav-link:hover { color: #e0ffff !important; }
        .container { background-color: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    </style>
    <link rel="stylesheet" href="../CSS/<?php echo basename($_SERVER['PHP_SELF'], '.php'); ?>.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">ExportLink</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <div class="navbar-nav ms-auto">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a class="nav-link" href="<?php
                        // Determine dashboard based on user role with debug logging
                        $dashboard_url = '';
                        if ($user['role'] == 'admin') {
                            $dashboard_url = 'adminDashboard.php';
                        } elseif ($user['role'] == 'importer') {
                            $dashboard_url = 'importerDashboard.php';
                        } else {
                            $dashboard_url = 'farmerDashboard.php';
                        }
                        error_log("Header dashboard URL: $dashboard_url for role: {$user['role']}");
                        echo $dashboard_url;
                    ?>">Dashboard</a>
                    <?php if (in_array($user['role'], ['farmer', 'importer'])): ?>
                        <a class="nav-link" href="profile.php">Profile</a>
                    <?php endif; ?>
                    <a class="nav-link" href="login.php?logout=1">Logout</a>
                <?php else: ?>
                    <a class="nav-link" href="login.php">Login</a>
                    <a class="nav-link" href="registration.php">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
<div class="container mt-3">