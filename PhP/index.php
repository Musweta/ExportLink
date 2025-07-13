<?php
// Include header with database connection
require_once 'header.php';
?>

<!-- Responsive main content -->
<div class="container mt-5">
    <h1>Welcome to ExportLink</h1>
    <p>Connecting Kenyan smallholder farmers with international buyers.</p>
    <div class="d-flex flex-column flex-md-row gap-2">
        <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="registration.php" class="btn btn-primary">Get Started</a>
            <a href="login.php" class="btn btn-outline-primary">Login</a>
        <?php else: ?>
            <a href="<?php echo $user['role'] == 'admin' ? 'adminDashboard.php' : 'farmerDashboard.php'; ?>" class="btn btn-primary">Go to Dashboard</a>
        <?php endif; ?>
    </div>
</div>

<?php
// Include footer
require_once 'footer.php';
?>