<?php
require_once 'header.php';

// Restrict access to farmers and importers
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['farmer', 'importer'])) {
    header("Location: login.php");
    exit;
}
?>

<div class="container mt-5">
    <h2><?php echo $_SESSION['role'] == 'farmer' ? 'Farmer' : 'Importer'; ?> Dashboard</h2>
    <p>Welcome, <?php echo htmlspecialchars($_SESSION['role']); ?>!</p>
    <?php if ($_SESSION['role'] == 'farmer'): ?>
        <a href="productListing.php" class="btn btn-primary">List New Product</a>
        <a href="orderManagement.php" class="btn btn-primary">View Orders</a>
    <?php else: ?>
        <a href="productListing.php" class="btn btn-primary">Browse Products</a>
        <a href="orderManagement.php" class="btn btn-primary">View Orders</a>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>