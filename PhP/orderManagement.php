<?php
require_once 'header.php';

// Restrict access to farmers and importers
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['farmer', 'importer'])) {
    header("Location: login.php");
    exit;
}

// Handle order placement
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_SESSION['role'] == 'importer') {
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_SANITIZE_NUMBER_INT);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_SANITIZE_NUMBER_INT);

    // Validate inputs
    if (!$product_id || !$quantity || $quantity <= 0) {
        echo "<div class='alert alert-danger'>Invalid product or quantity.</div>";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO orders (importer_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $product_id, $quantity]);
            echo "<div class='alert alert-success'>Order placed successfully!</div>";
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
}

// Fetch orders based on user role
$query = $_SESSION['role'] == 'farmer' 
    ? "SELECT o.*, p.name, u.username FROM orders o JOIN products p ON o.product_id = p.id JOIN users u ON o.importer_id = u.id WHERE p.farmer_id = ?"
    : "SELECT o.*, p.name, u.username FROM orders o JOIN products p ON o.product_id = p.id JOIN users u ON p.farmer_id = u.id WHERE o.importer_id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();
?>

<!-- Responsive order management -->
<div class="container mt-5">
    <h2>Order Management</h2>
    <?php if ($_SESSION['role'] == 'importer' && isset($_GET['product_id'])): ?>
        <form method="POST" action="" class="col-md-6 mx-auto">
            <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($_GET['product_id']); ?>">
            <div class="mb-3">
                <label for="quantity" class="form-label">Quantity</label>
                <input type="number" class="form-control" id="quantity" name="quantity" required placeholder="Enter quantity">
            </div>
            <button type="submit" class="btn btn-primary">Place Order</button>
        </form>
    <?php endif; ?>
    <h3 class="mt-4">Your Orders</h3>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th><?php echo $_SESSION['role'] == 'farmer' ? 'Importer' : 'Farmer'; ?></th>
                    <th>Quantity</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr><td colspan="4">No orders found.</td></tr>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['name']); ?></td>
                            <td><?php echo htmlspecialchars($order['username']); ?></td>
                            <td><?php echo htmlspecialchars($order['quantity']); ?></td>
                            <td><?php echo htmlspecialchars($order['status']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'footer.php'; ?>