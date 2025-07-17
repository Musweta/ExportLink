<?php
require_once 'header.php';

// Restrict access to farmers and importers
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['farmer', 'importer'])) {
    header("Location: login.php");
    exit;
}

// Handle order placement
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_SESSION['role'] == 'importer' && isset($_POST['action']) && $_POST['action'] == 'place_order') {
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_SANITIZE_NUMBER_INT);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_SANITIZE_NUMBER_INT);

    if (!$product_id || !$quantity || $quantity <= 0) {
        echo "<div class='alert alert-danger'>Invalid product or quantity.</div>";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO orders (importer_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $product_id, $quantity]);
            $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action) VALUES (?, ?)");
            $stmt->execute([$_SESSION['user_id'], "Placed order for product ID: $product_id"]);
            echo "<div class='alert alert-success'>Order placed successfully! <a href='exportDocs.php?order_id=" . $pdo->lastInsertId() . "'>Generate Export Document</a></div>";
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
}

// Handle order status update (for farmers)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_SESSION['role'] == 'farmer' && isset($_POST['action']) && $_POST['action'] == 'update_status') {
    $order_id = filter_input(INPUT_POST, 'order_id', FILTER_SANITIZE_NUMBER_INT);
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);

    if (!$order_id || !in_array($status, ['pending', 'confirmed', 'shipped', 'delivered'])) {
        echo "<div class='alert alert-danger'>Invalid order or status.</div>";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ? AND product_id IN (SELECT id FROM products WHERE farmer_id = ?)");
            $stmt->execute([$status, $order_id, $_SESSION['user_id']]);
            $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action) VALUES (?, ?)");
            $stmt->execute([$_SESSION['user_id'], "Updated order ID: $order_id to status: $status"]);
            echo "<div class='alert alert-success'>Order status updated successfully!</div>";
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
}

// Fetch orders
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
        <!-- Order Placement Form -->
        <form method="POST" action="" class="col-md-6 mx-auto">
            <input type="hidden" name="action" value="place_order">
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
                    <?php if ($_SESSION['role'] == 'farmer'): ?>
                        <th>Update Status</th>
                    <?php elseif ($_SESSION['role'] == 'importer'): ?>
                        <th>Export Document</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr><td colspan="<?php echo $_SESSION['role'] == 'farmer' ? 5 : 4; ?>">No orders found.</td></tr>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['name']); ?></td>
                            <td><?php echo htmlspecialchars($order['username']); ?></td>
                            <td><?php echo htmlspecialchars($order['quantity']); ?></td>
                            <td><?php echo htmlspecialchars($order['status']); ?></td>
                            <?php if ($_SESSION['role'] == 'farmer'): ?>
                                <td>
                                    <form method="POST" action="">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <select name="status" class="form-select d-inline w-auto">
                                            <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="confirmed" <?php echo $order['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                            <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                            <option value="delivered" <?php echo $order['status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                        </select>
                                        <button type="submit" class="btn btn-primary btn-sm">Update</button>
                                    </form>
                                </td>
                            <?php elseif ($_SESSION['role'] == 'importer'): ?>
                                <td><a href="exportDocs.php?order_id=<?php echo $order['id']; ?>" class="btn btn-primary">Generate Document</a></td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'footer.php'; ?>