<?php
require_once 'header.php';
require_once 'db_conn.php';

// Restrict access
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['farmer', 'importer', 'admin'])) {
    header("Location: login.php");
    exit;
}

// Handle order placement (importers)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_SESSION['role'] == 'importer' && isset($_POST['place_order'])) {
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_SANITIZE_NUMBER_INT);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_SANITIZE_NUMBER_INT);
    $payment_terms = filter_input(INPUT_POST, 'payment_terms', FILTER_UNSAFE_RAW);
    $payment_terms = trim(htmlspecialchars($payment_terms ?? ''));
    $currency = filter_input(INPUT_POST, 'currency', FILTER_UNSAFE_RAW);
    $currency = trim(htmlspecialchars($currency ?? 'USD'));

    if (empty($product_id) || empty($quantity) || $quantity <= 0 || empty($payment_terms) || !in_array($currency, ['USD', 'KES', 'EUR'])) {
        echo "<div class='alert alert-danger'>Invalid order details.</div>";
    } else {
        $stmt = $pdo->prepare("SELECT quantity, price FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();
        if ($product && $quantity <= $product['quantity']) {
            try {
                $pdo->beginTransaction();
                $stmt = $pdo->prepare("INSERT INTO orders (importer_id, product_id, quantity, payment_terms, currency) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$_SESSION['user_id'], $product_id, $quantity, $payment_terms, $currency]);
                $order_id = $pdo->lastInsertId();
                $stmt = $pdo->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
                $stmt->execute([$quantity, $product_id]);
                // Generate invoice
                $stmt = $pdo->prepare("SELECT p.*, u1.username as farmer, u2.username as importer, u2.email 
                    FROM products p 
                    JOIN users u1 ON p.farmer_id = u1.id 
                    JOIN users u2 ON u2.id = ? 
                    WHERE p.id = ?");
                $stmt->execute([$_SESSION['user_id'], $product_id]);
                $product = $stmt->fetch();
                $invoice_content = "Commercial Invoice\n" .
                    "Invoice Number: INV-$order_id\n" .
                    "Date: " . date('Y-m-d H:i:s') . "\n" .
                    "Seller: {$product['farmer']}\n" .
                    "Importer: {$product['importer']} ({$product['email']})\n" .
                    "Product: {$product['name']}\n" .
                    "Type: {$product['type']}\n" .
                    "Quantity: $quantity\n" .
                    "Unit Price: {$product['price']} $currency\n" .
                    "Total: " . ($quantity * $product['price']) . " $currency\n" .
                    "Payment Terms: $payment_terms\n" .
                    "HS Code: {$product['hs_code']}\n" .
                    "Country of Origin: {$product['origin']}\n" .
                    "Grade: {$product['grade']}";
                $stmt = $pdo->prepare("INSERT INTO export_documents (order_id, document_type, document_content) VALUES (?, ?, ?)");
                $stmt->execute([$order_id, 'invoice', $invoice_content]);
                // Generate receipt for importer
                $receipt_content = "Receipt\n" .
                    "Order ID: $order_id\n" .
                    "Date: " . date('Y-m-d H:i:s') . "\n" .
                    "Product: {$product['name']}\n" .
                    "Quantity: $quantity\n" .
                    "Total: " . ($quantity * $product['price']) . " $currency\n" .
                    "Payment Terms: $payment_terms";
                $stmt->execute([$order_id, 'receipt', $receipt_content]);
                $pdo->commit();
                echo "<div class='alert alert-success'>Order placed successfully! Invoice and receipt generated.</div>";
            } catch (PDOException $e) {
                $pdo->rollBack();
                echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
            }
        } else {
            echo "<div class='alert alert-danger'>Insufficient product quantity or invalid product.</div>";
        }
    }
}

// Handle order status update (farmers)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_SESSION['role'] == 'farmer' && isset($_POST['update_status'])) {
    $order_id = filter_input(INPUT_POST, 'order_id', FILTER_SANITIZE_NUMBER_INT);
    $status = filter_input(INPUT_POST, 'status', FILTER_UNSAFE_RAW);
    $status = trim(htmlspecialchars($status ?? ''));

    if (empty($order_id) || !in_array($status, ['pending', 'confirmed', 'shipped', 'delivered'])) {
        echo "<div class='alert alert-danger'>Invalid order or status.</div>";
    } else {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ? AND product_id IN (SELECT id FROM products WHERE farmer_id = ?)");
            $stmt->execute([$status, $order_id, $_SESSION['user_id']]);
            if ($status == 'shipped' && $stmt->rowCount() > 0) {
                $tracking_number = 'TRK-' . strtoupper(substr(md5($order_id . time()), 0, 10));
                $stmt = $pdo->prepare("INSERT INTO export_documents (order_id, document_type, document_content) VALUES (?, ?, ?)");
                $stmt->execute([$order_id, 'bill_of_lading', "Tracking Number: $tracking_number"]);
            }
            $pdo->commit();
            echo "<div class='alert alert-success'>Order status updated!" . ($status == 'shipped' ? " Tracking Number: $tracking_number" : "") . "</div>";
        } catch (PDOException $e) {
            $pdo->rollBack();
            echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
}

// Handle customs clearance (importers)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_SESSION['role'] == 'importer' && isset($_POST['clear_customs'])) {
    $order_id = filter_input(INPUT_POST, 'order_id', FILTER_SANITIZE_NUMBER_INT);
    try {
        $stmt = $pdo->prepare("UPDATE orders SET status = 'cleared' WHERE id = ? AND importer_id = ?");
        $stmt->execute([$order_id, $_SESSION['user_id']]);
        echo "<div class='alert alert-success'>Customs clearance confirmed!</div>";
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

// Fetch orders based on role
$query = $_SESSION['role'] == 'farmer'
    ? "SELECT o.*, p.name, p.price, u.username as counterparty, 
              (SELECT document_content FROM export_documents WHERE order_id = o.id AND document_type = 'invoice') as invoice,
              (SELECT document_content FROM export_documents WHERE order_id = o.id AND document_type = 'bill_of_lading') as tracking_number,
              (SELECT document_content FROM export_documents WHERE order_id = o.id AND document_type = 'receipt') as receipt
       FROM orders o 
       JOIN products p ON o.product_id = p.id 
       JOIN users u ON o.importer_id = u.id 
       WHERE p.farmer_id = ?"
    : ($_SESSION['role'] == 'admin'
        ? "SELECT o.*, p.name, p.price, u1.username as farmer, u2.username as importer,
                  (SELECT document_content FROM export_documents WHERE order_id = o.id AND document_type = 'invoice') as invoice,
                  (SELECT document_content FROM export_documents WHERE order_id = o.id AND document_type = 'bill_of_lading') as tracking_number,
                  (SELECT document_content FROM export_documents WHERE order_id = o.id AND document_type = 'receipt') as receipt
           FROM orders o 
           JOIN products p ON o.product_id = p.id 
           JOIN users u1 ON p.farmer_id = u1.id 
           JOIN users u2 ON o.importer_id = u2.id"
        : "SELECT o.*, p.name, p.price, u.username as farmer,
                  (SELECT document_content FROM export_documents WHERE order_id = o.id AND document_type = 'invoice') as invoice,
                  (SELECT document_content FROM export_documents WHERE order_id = o.id AND document_type = 'bill_of_lading') as tracking_number,
                  (SELECT document_content FROM export_documents WHERE order_id = o.id AND document_type = 'receipt') as receipt
           FROM orders o 
           JOIN products p ON o.product_id = p.id 
           JOIN users u ON p.farmer_id = u.id 
           WHERE o.importer_id = ?");
$stmt = $pdo->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();
?>

<div class="container mt-5">
    <h2>Order Management</h2>
    <?php if ($_SESSION['role'] == 'importer' && isset($_GET['product_id'])): ?>
        <form method="POST" action="" class="col-md-6 mx-auto">
            <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($_GET['product_id']); ?>">
            <div class="mb-3">
                <label for="quantity" class="form-label">Quantity</label>
                <input type="number" class="form-control" id="quantity" name="quantity" required placeholder="Enter quantity">
            </div>
            <div class="mb-3">
                <label for="payment_terms" class="form-label">Payment Terms</label>
                <input type="text" class="form-control" id="payment_terms" name="payment_terms" required placeholder="e.g.Bank Cheque, M-Pesa">
            </div>
            <div class="mb-3">
                <label for="currency" class="form-label">Currency</label>
                <select class="form-select" id="currency" name="currency" required>
                    <option value="USD">USD</option>
                    <option value="KES">KES</option>
                    <option value="EUR">EUR</option>
                </select>
            </div>
            <button type="submit" name="place_order" class="btn btn-primary">Place Order</button>
        </form>
    <?php endif; ?>
    <h3 class="mt-4">Your Orders</h3>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Product</th>
                    <th>Counterparty</th>
                    <th>Quantity</th>
                    <th>Payment Terms</th>
                    <th>Currency</th>
                    <th>Total Amount</th>
                    <th>Status</th>
                    <th>Updated At</th>
                    <th>Tracking Number</th>
                    <th>Invoice</th>
                    <?php if ($_SESSION['role'] == 'importer'): ?>
                        <th>Receipt</th>
                    <?php endif; ?>
                    <?php if ($_SESSION['role'] == 'farmer'): ?>
                        <th>Action</th>
                    <?php elseif ($_SESSION['role'] == 'importer'): ?>
                        <th>Customs Clearance</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr><td colspan="<?php echo $_SESSION['role'] == 'importer' ? 12 : 11; ?>">No orders found.</td></tr>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['id']); ?></td>
                            <td><?php echo htmlspecialchars($order['name']); ?></td>
                            <td><?php echo htmlspecialchars($order['counterparty'] ?? ($order['farmer'] ?? $order['importer'])); ?></td>
                            <td><?php echo htmlspecialchars($order['quantity']); ?></td>
                            <td><?php echo htmlspecialchars($order['payment_terms']); ?></td>
                            <td><?php echo htmlspecialchars($order['currency']); ?></td>
                            <td><?php echo htmlspecialchars($order['quantity'] * $order['price']) . " " . htmlspecialchars($order['currency']); ?></td>
                            <td><?php echo htmlspecialchars($order['status']); ?></td>
                            <td><?php echo htmlspecialchars($order['updated_at'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($order['tracking_number'] ?? 'N/A'); ?></td>
                            <td><pre><?php echo htmlspecialchars($order['invoice'] ?? 'Not generated'); ?></pre></td>
                            <?php if ($_SESSION['role'] == 'importer'): ?>
                                <td><pre><?php echo htmlspecialchars($order['receipt'] ?? 'Not generated'); ?></pre></td>
                            <?php endif; ?>
                            <?php if ($_SESSION['role'] == 'farmer'): ?>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <select name="status" class="form-select d-inline w-auto">
                                            <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="confirmed" <?php echo $order['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                            <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                            <option value="delivered" <?php echo $order['status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                        </select>
                                        <button type="submit" name="update_status" class="btn btn-primary btn-sm">Update</button>
                                    </form>
                                </td>
                            <?php elseif ($_SESSION['role'] == 'importer'): ?>
                                <td>
                                    <?php if ($order['status'] == 'delivered'): ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                            <input type="checkbox" name="clear_customs" id="clear_customs_<?php echo $order['id']; ?>" onchange="this.form.submit()" <?php echo $order['status'] == 'cleared' ? 'checked disabled' : ''; ?>>
                                            <label for="clear_customs_<?php echo $order['id']; ?>">Cleared</label>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'footer.php'; ?>