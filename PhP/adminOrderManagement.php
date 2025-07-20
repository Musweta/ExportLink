<?php
require_once 'header.php';
require_once 'db_conn.php';

// Restrict access to admins
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['create_order'])) {
        $importer_id = filter_input(INPUT_POST, 'importer_id', FILTER_SANITIZE_NUMBER_INT);
        $product_id = filter_input(INPUT_POST, 'product_id', FILTER_SANITIZE_NUMBER_INT);
        $quantity = filter_input(INPUT_POST, 'quantity', FILTER_SANITIZE_NUMBER_INT);
        $payment_terms = filter_input(INPUT_POST, 'payment_terms', FILTER_UNSAFE_RAW);
        $currency = filter_input(INPUT_POST, 'currency', FILTER_UNSAFE_RAW);
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO orders (importer_id, product_id, quantity, payment_terms, currency) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$importer_id, $product_id, $quantity, $payment_terms, $currency]);
            $order_id = $pdo->lastInsertId();
            $pdo->commit();
            echo "<div class='alert alert-success'>Order created successfully! Order ID: $order_id</div>";
        } catch (PDOException $e) {
            $pdo->rollBack();
            echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    } elseif (isset($_POST['update_order'])) {
        $order_id = filter_input(INPUT_POST, 'order_id', FILTER_SANITIZE_NUMBER_INT);
        $status = filter_input(INPUT_POST, 'status', FILTER_UNSAFE_RAW);
        try {
            $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$status, $order_id]);
            echo "<div class='alert alert-success'>Order updated successfully!</div>";
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    } elseif (isset($_POST['delete_order'])) {
        $order_id = filter_input(INPUT_POST, 'order_id', FILTER_SANITIZE_NUMBER_INT);
        try {
            $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
            $stmt->execute([$order_id]);
            echo "<div class='alert alert-success'>Order deleted successfully!</div>";
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
}

// Fetch all orders
try {
    $stmt = $pdo->query("SELECT o.*, p.name, p.price, u1.username as farmer, u2.username as importer,
                        (SELECT document_content FROM export_documents WHERE order_id = o.id AND document_type = 'invoice') as invoice,
                        (SELECT document_content FROM export_documents WHERE order_id = o.id AND document_type = 'bill_of_lading') as tracking_number,
                        (SELECT document_content FROM export_documents WHERE order_id = o.id AND document_type = 'receipt') as receipt
                 FROM orders o 
                 JOIN products p ON o.product_id = p.id 
                 JOIN users u1 ON p.farmer_id = u1.id 
                 JOIN users u2 ON o.importer_id = u2.id");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Admin order fetch error: " . $e->getMessage());
    echo "<div class='alert alert-danger'>Error fetching orders: " . htmlspecialchars($e->getMessage()) . "</div>";
}
?>

<div class="container mt-5">
    <h2>Admin Order Management</h2>
    <h3>Create New Order</h3>
    <form method="POST" class="col-md-6 mx-auto mb-4">
        <div class="mb-3">
            <label for="importer_id" class="form-label">Importer ID</label>
            <input type="number" class="form-control" id="importer_id" name="importer_id" required>
        </div>
        <div class="mb-3">
            <label for="product_id" class="form-label">Product ID</label>
            <input type="number" class="form-control" id="product_id" name="product_id" required>
        </div>
        <div class="mb-3">
            <label for="quantity" class="form-label">Quantity</label>
            <input type="number" class="form-control" id="quantity" name="quantity" required>
        </div>
        <div class="mb-3">
            <label for="payment_terms" class="form-label">Payment Terms</label>
            <input type="text" class="form-control" id="payment_terms" name="payment_terms" required placeholder="e.g., Net 30">
        </div>
        <div class="mb-3">
            <label for="currency" class="form-label">Currency</label>
            <select class="form-select" id="currency" name="currency" required>
                <option value="USD">USD</option>
                <option value="KES">KES</option>
                <option value="EUR">EUR</option>
            </select>
        </div>
        <button type="submit" name="create_order" class="btn btn-primary">Create Order</button>
    </form>
    <h3>All Orders</h3>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Product</th>
                    <th>Farmer</th>
                    <th>Importer</th>
                    <th>Quantity</th>
                    <th>Payment Terms</th>
                    <th>Currency</th>
                    <th>Total Amount</th>
                    <th>Status</th>
                    <th>Updated At</th>
                    <th>Tracking Number</th>
                    <th>Invoice</th>
                    <th>Receipt</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr><td colspan="14">No orders found.</td></tr>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['id']); ?></td>
                            <td><?php echo htmlspecialchars($order['name']); ?></td>
                            <td><?php echo htmlspecialchars($order['farmer']); ?></td>
                            <td><?php echo htmlspecialchars($order['importer']); ?></td>
                            <td><?php echo htmlspecialchars($order['quantity']); ?></td>
                            <td><?php echo htmlspecialchars($order['payment_terms']); ?></td>
                            <td><?php echo htmlspecialchars($order['currency']); ?></td>
                            <td><?php echo htmlspecialchars($order['quantity'] * $order['price']) . " " . htmlspecialchars($order['currency']); ?></td>
                            <td><?php echo htmlspecialchars($order['status']); ?></td>
                            <td><?php echo htmlspecialchars($order['updated_at'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($order['tracking_number'] ?? 'N/A'); ?></td>
                            <td><pre><?php echo htmlspecialchars($order['invoice'] ?? 'Not generated'); ?></pre></td>
                            <td><pre><?php echo htmlspecialchars($order['receipt'] ?? 'Not generated'); ?></pre></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <select name="status" class="form-select d-inline w-auto">
                                        <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="confirmed" <?php echo $order['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                        <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                        <option value="delivered" <?php echo $order['status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                        <option value="cleared" <?php echo $order['status'] == 'cleared' ? 'selected' : ''; ?>>Cleared</option>
                                    </select>
                                    <button type="submit" name="update_order" class="btn btn-primary btn-sm">Update</button>
                                    <button type="submit" name="delete_order" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'footer.php'; ?>