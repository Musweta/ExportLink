<?php
// Include header with session and database setup
require_once 'header.php';
require_once 'db_conn.php'; // defines $pdo

// Initialize $user with default values to avoid undefined index errors
$user = ['role' => '', 'username' => '', 'is_approved' => 0];

// Fetch the user details if logged in
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch() ?: $user; // Use fetched data or default
        if (!$user) {
            session_unset();
            session_destroy();
            header("Location: login.php");
            exit;
        }
        // Ensure role is available in session for consistency
        $_SESSION['role'] = $user['role'];
    } catch (PDOException $e) {
        error_log("Index user fetch error: " . $e->getMessage());
        echo "<div class='alert alert-danger'>Database error. Please try again later.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ExportLink - Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f0f8ff; }
        .container { background-color: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
<div class="container mt-5">
    <h1>Welcome to ExportLink</h1>
    <p>Connecting Kenyan smallholder farmers with international buyers.</p>
    <div class="d-flex flex-column flex-md-row gap-2">
        <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="registration.php" class="btn btn-primary">Get Started</a>
            <a href="login.php" class="btn btn-outline-primary">Login</a>
        <?php else: ?>
            <a href="<?php
                // Determine dashboard based on user role with debug logging
                $dashboard_url = '';
                if ($user['role'] == 'admin') {
                    $dashboard_url = 'adminDashboard.php';
                } elseif ($user['role'] == 'importer') {
                    $dashboard_url = 'importerDashboard.php';
                } else {
                    $dashboard_url = 'farmerDashboard.php';
                }
                error_log("Generated dashboard URL: $dashboard_url for role: {$user['role']}");
                echo $dashboard_url;
            ?>" class="btn btn-primary">Go to Dashboard</a>
            <?php if (in_array($user['role'], ['farmer', 'importer'])): ?>
                <a href="profile.php" class="btn btn-secondary">Profile</a>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
<?php require_once 'footer.php'; ?>
</body>
</html>