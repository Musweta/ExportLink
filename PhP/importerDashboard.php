<?php
require_once 'header.php';
require_once 'db_conn.php';

// Restrict access to importers
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'importer') {
    header("Location: login.php");
    exit;
}

// Fetch importer's orders
$stmt = $pdo->prepare("SELECT o.*, p.name, u.username as farmer 
    FROM orders o 
    JOIN products p ON o.product_id = p.id 
    JOIN users u ON p.farmer_id = u.id 
    WHERE o.importer_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();
?>

<!-- Responsive importer dashboard -->
<div class="container mt-5">
    <h2>Dashboard</h2>
    <p>Welcome, <?php echo htmlspecialchars($_SESSION['role']); ?>!</p>
    <a href="productListing.php" class="btn btn-primary mb-3">Browse Products</a>
    <h3>Your Orders</h3>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Product</th>
                    <th>Farmer</th>
                    <th>Quantity</th>
                    <th>Payment Terms</th>
                    <th>Currency</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr><td colspan="7">No orders found.</td></tr>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['id']); ?></td>
                            <td><?php echo htmlspecialchars($order['name']); ?></td>
                            <td><?php echo htmlspecialchars($order['farmer']); ?></td>
                            <td><?php echo htmlspecialchars($order['quantity']); ?></td>
                            <td><?php echo htmlspecialchars($order['payment_terms']); ?></td>
                            <td><?php echo htmlspecialchars($order['currency']); ?></td>
                            <td><?php echo htmlspecialchars($order['status']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'footer.php'; ?>