<?php
// Include header with session and database setup
require_once 'header.php';
require_once 'db_conn.php';
require_once '../dompdf/autoload.inc.php'; // Assuming Dompdf is installed via Composer
use Dompdf\Dompdf;

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['farmer', 'importer', 'admin'])) {
    // Redirect unauthorized users to login
    header("Location: login.php");
    exit;
}

// Handle order placement (importers)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_SESSION['role'] == 'importer' && isset($_POST['place_order'])) {
    // Process order placement
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_SANITIZE_NUMBER_INT);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_SANITIZE_NUMBER_INT);
    $payment_terms = filter_input(INPUT_POST, 'payment_terms', FILTER_UNSAFE_RAW);
    $currency = filter_input(INPUT_POST, 'currency', FILTER_UNSAFE_RAW);
    $delivery_address = filter_input(INPUT_POST, 'delivery_address', FILTER_UNSAFE_RAW);

    // Check importer details (simulated license validation)
    $stmt = $pdo->prepare("SELECT certification_path FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $importer = $stmt->fetch();
    if (!$importer || !$importer['certification_path']) {
        echo "<div class='alert alert-danger'>Importer license not verified. Please upload certification.</div>";
    } elseif (empty($product_id) || empty($quantity) || $quantity <= 0 || empty($payment_terms) || !in_array($currency, ['USD', 'KES', 'EUR']) || empty($delivery_address)) {
        echo "<div class='alert alert-danger'>Invalid order details.</div>";
    } else {
        $stmt = $pdo->prepare("SELECT quantity, price, farmer_id, name, type, hs_code, origin, grade FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();
        if ($product && $quantity <= $product['quantity']) {
            try {
                $pdo->beginTransaction();
                $stmt = $pdo->prepare("INSERT INTO orders (importer_id, product_id, quantity, payment_terms, currency, delivery_address) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$_SESSION['user_id'], $product_id, $quantity, $payment_terms, $currency, $delivery_address]);
                $order_id = $pdo->lastInsertId();
                $stmt = $pdo->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
                $stmt->execute([$quantity, $product_id]);
                $stmt = $pdo->prepare("SELECT u.username as farmer, u.email as importer_email, p.* FROM products p JOIN users u ON p.farmer_id = u.id WHERE p.id = ?");
                $stmt->execute([$product_id]);
                $product = $stmt->fetch();
                $conversion_rates = ['USD' => 1, 'KES' => 130, 'EUR' => 0.185];
                $base_price = $product['price'];
                $converted_price = $base_price * $conversion_rates[$currency];
                $total = $quantity * $converted_price;

                // Prepare invoice content for Dompdf
                $invoice_content = "<h2>Commercial Invoice</h2>
                    Invoice Number: INV-$order_id<br>
                    Date: " . date('Y-m-d H:i:s') . "<br>
                    Seller: {$product['farmer']}<br>
                    Importer: {$user['username']} ({$user['email']})<br>
                    Product: {$product['name']}<br>
                    Type: {$product['type']}<br>
                    Quantity: $quantity<br>
                    Unit Price: $converted_price $currency<br>
                    Total: $total $currency<br>
                    Payment Terms: $payment_terms<br>
                    HS Code: {$product['hs_code']}<br>
                    Country of Origin: {$product['origin']}<br>
                    Grade: {$product['grade']}<br>
                    Delivery Address: $delivery_address";

                // Store invoice content for later retrieval
                $stmt = $pdo->prepare("INSERT INTO export_documents (order_id, document_type, document_content) VALUES (?, ?, ?)");
                $stmt->execute([$order_id, 'invoice', $invoice_content]);
                $pdo->commit();
                echo "<div class='alert alert-success'>Order placed successfully! <a href='generatedocs.php?order_id=$order_id&action=view' target='_blank'>View Invoice</a> | <a href='generatedocs.php?order_id=$order_id&action=download' target='_blank'>Download Invoice</a></div>";
            } catch (PDOException $e) {
                $pdo->rollBack();
                echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
            }
        } else {
            echo "<div class='alert alert-danger'>Insufficient product quantity or invalid product.</div>";
        }
    }
}

// Handle order status update and export document upload (farmers)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_SESSION['role'] == 'farmer' && isset($_POST['update_status'])) {
    // Process status update and export document upload
    $order_id = filter_input(INPUT_POST, 'order_id', FILTER_SANITIZE_NUMBER_INT);
    $status = filter_input(INPUT_POST, 'status', FILTER_UNSAFE_RAW);
    $export_doc_path = null;
    if (isset($_FILES['export_doc']) && $_FILES['export_doc']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = '../Uploads/';
        $file_name = $_SESSION['user_id'] . '_' . $order_id . '_' . time() . '_' . basename($_FILES['export_doc']['name']);
        $export_doc_path = $upload_dir . $file_name;
        move_uploaded_file($_FILES['export_doc']['tmp_name'], $export_doc_path);
    }

    if (empty($order_id) || !in_array($status, ['pending', 'confirmed', 'shipped'])) {
        echo "<div class='alert alert-danger'>Invalid order or status.</div>";
    } else {
        try {
            $pdo->beginTransaction();
            error_log("Executing update with status: $status, order_id: $order_id, farmer_id: {$_SESSION['user_id']}");
            $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ? AND product_id IN (SELECT id FROM products WHERE farmer_id = ?)");
            $stmt->execute([$status, $order_id, $_SESSION['user_id']]);
            if ($status == 'shipped' && $stmt->rowCount() > 0 && $export_doc_path) {
                $stmt = $pdo->prepare("INSERT INTO export_documents (order_id, document_type, document_content) VALUES (?, ?, ?)");
                $stmt->execute([$order_id, 'export_doc', $export_doc_path]);
            }
            $pdo->commit();
            echo "<div class='alert alert-success'>Order status updated to $status!</div>";
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("PDO Error: " . $e->getMessage() . " at line " . $e->getLine());
            echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
}

// Handle delivery update (importers)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_SESSION['role'] == 'importer' && isset($_POST['update_delivered'])) {
    // Process delivery update for importers
    $order_id = filter_input(INPUT_POST, 'order_id', FILTER_SANITIZE_NUMBER_INT);
    try {
        $stmt = $pdo->prepare("UPDATE orders SET status = 'delivered' WHERE id = ? AND importer_id = ?");
        $stmt->execute([$order_id, $_SESSION['user_id']]);
        echo "<div class='alert alert-success'>Order marked as delivered!</div>";
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

// Fetch orders based on role
$query = $_SESSION['role'] == 'farmer'
    ? "SELECT o.*, p.name, p.price, u.username as importer, o.delivery_address,
              (SELECT document_content FROM export_documents WHERE order_id = o.id AND document_type = 'invoice') as invoice_content,
              (SELECT document_content FROM export_documents WHERE order_id = o.id AND document_type = 'export_doc') as export_doc_path
       FROM orders o JOIN products p ON o.product_id = p.id 
       JOIN users u ON o.importer_id = u.id 
       WHERE p.farmer_id = ?"
    : ($_SESSION['role'] == 'admin'
        ? "SELECT o.*, p.name, p.price, u1.username as farmer, u2.username as importer, o.delivery_address,
                  (SELECT document_content FROM export_documents WHERE order_id = o.id AND document_type = 'invoice') as invoice_content,
                  (SELECT document_content FROM export_documents WHERE order_id = o.id AND document_type = 'export_doc') as export_doc_path
           FROM orders o JOIN products p ON o.product_id = p.id 
           JOIN users u1 ON p.farmer_id = u1.id 
           JOIN users u2 ON o.importer_id = u2.id"
        : "SELECT o.*, p.name, p.price, u.username as farmer, o.delivery_address,
                  (SELECT document_content FROM export_documents WHERE order_id = o.id AND document_type = 'invoice') as invoice_content,
                  (SELECT document_content FROM export_documents WHERE order_id = o.id AND document_type = 'export_doc') as export_doc_path
           FROM orders o JOIN products p ON o.product_id = p.id 
           JOIN users u ON p.farmer_id = u.id 
           WHERE o.importer_id = ?");
$stmt = $pdo->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ExportLink - Order Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f0f8ff; }
        .container { background-color: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<div class="container mt-5">
    <h2>Order Management</h2>
    <?php if ($_SESSION['role'] == 'importer' && isset($_GET['product_id'])): ?>
        <form method="POST" action="" class="col-md-6 mx-auto">
            <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($_GET['product_id']); ?>">
            <div class="mb-3">
                <label for="quantity" class="form-label">Quantity</label>
                <input type="number" class="form-control" id="quantity" name="quantity" required>
            </div>
            <div class="mb-3">
                <label for="payment_terms" class="form-label">Payment Terms</label>
                <input type="text" class="form-control" id="payment_terms" name="payment_terms" required>
            </div>
            <div class="mb-3">
                <label for="currency" class="form-label">Currency</label>
                <select class="form-select" id="currency" name="currency" required>
                    <option value="USD">USD ($1 = KES 130, €0.185)</option>
                    <option value="KES">KES ($1 = KES 130)</option>
                    <option value="EUR">EUR ($1 = €0.185, €1 = KES 152)</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="delivery_address" class="form-label">Delivery Address</label>
                <textarea class="form-control" id="delivery_address" name="delivery_address" required></textarea>
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
                    <th>Delivery Address</th>
                    <th>Invoice</th>
                    <th>Export Doc</th>
                    <?php if ($_SESSION['role'] == 'farmer' || $_SESSION['role'] == 'importer'): ?>
                        <th>Action</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr><td colspan="13">No orders found.</td></tr>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['id']); ?></td>
                            <td><?php echo htmlspecialchars($order['name']); ?></td>
                            <td><?php echo htmlspecialchars($order['importer'] ?? ($order['farmer'] ?? '')); ?></td>
                            <td><?php echo htmlspecialchars($order['quantity']); ?></td>
                            <td><?php echo htmlspecialchars($order['payment_terms']); ?></td>
                            <td><?php echo htmlspecialchars($order['currency']); ?></td>
                            <td><?php echo htmlspecialchars($order['quantity'] * $order['price']) . " " . htmlspecialchars($order['currency']); ?></td>
                            <td><?php echo htmlspecialchars($order['status']); ?></td>
                            <td><?php echo htmlspecialchars($order['updated_at'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($order['delivery_address'] ?? 'N/A'); ?></td>
                            <td><a href="generatedocs.php?order_id=<?php echo $order['id']; ?>&action=view" target="_blank" class="btn btn-sm btn-secondary">View</a> | <a href="generatedocs.php?order_id=<?php echo $order['id']; ?>&action=download" target="_blank" class="btn btn-sm btn-secondary">Download</a></td>
                            <td><a href="<?php echo htmlspecialchars($order['export_doc_path']); ?>" target="_blank" class="btn btn-sm btn-secondary">View</a></td>
                            <?php if ($_SESSION['role'] == 'farmer'): ?>
                                <td>
                                    <form method="POST" enctype="multipart/form-data" style="display:inline;">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <select name="status" class="form-select d-inline w-auto">
                                            <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="confirmed" <?php echo $order['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                            <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                        </select>
                                        <input type="file" class="form-control form-control-sm d-inline w-auto" id="export_doc" name="export_doc" accept="application/pdf">
                                        <button type="submit" name="update_status" class="btn btn-primary btn-sm">Update</button>
                                    </form>
                                </td>
                            <?php elseif ($_SESSION['role'] == 'importer'): ?>
                                <td>
                                    <?php if ($order['status'] == 'shipped'): ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                            <input type="checkbox" name="update_delivered" id="delivered_<?php echo $order['id']; ?>" onchange="this.form.submit()" <?php echo $order['status'] == 'delivered' ? 'checked disabled' : ''; ?>>
                                            <label for="delivered_<?php echo $order['id']; ?>">Delivered</label>
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