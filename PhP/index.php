<?php
// Include header
require_once 'header.php';
?>

<div class="container mt-5">
    <h1>Welcome to ExportLink</h1>
    <p>Connecting Kenyan smallholder farmers with international buyers.</p>
    <?php if (!isset($_SESSION['user_id'])): ?>
        <a href="registration.php" class="btn btn-primary">Get Started</a>
        <a href="login.php" class="btn btn-outline-primary">Login</a>
    <?php else: ?>
        <a href="<?php echo $user['role'] == 'admin' ? 'adminDashboard.php' : 'farmerDashboard.php'; ?>" class="btn btn-primary">Go to Dashboard</a>
    <?php endif; ?>
</div>

<?php
// Include footer
require_once 'footer.php';
?>