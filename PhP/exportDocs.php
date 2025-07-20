<?php
require_once 'header.php';

// Restrict access to admins and farmers
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'farmer'])) {
    header("Location: login.php");
    exit;
}

// Handle document generation/upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['generate_doc'])) {
    $order_id = filter_input(INPUT_POST, 'order_id', FILTER_SANITIZE_NUMBER_INT);
    $document_type = filter_input(INPUT_POST, 'document_type', FILTER_UNSAFE_RAW);
    $document_type = trim(htmlspecialchars($document_type ?? ''));

    if (empty($order_id) || !in_array($document_type, ['packing_list', 'phytosanitary', 'certificate_of_origin', 'export_permit'])) {
        echo "<div class='alert alert-danger'>Invalid order or document type.</div>";
    } else {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("SELECT o.*, p.name, p.type, p.origin, p.grade, p.hs_code, u1.username as importer, u2.username as farmer 
                FROM orders o 
                JOIN products p ON o.product_id = p.id 
                JOIN users u1 ON o.importer_id = u1.id 
                JOIN users u2 ON p.farmer_id = u2.id 
                WHERE o.id = ?");
            $stmt->execute([$order_id]);
            $order = $stmt->fetch();

            if ($order) {
                $content = '';
                switch ($document_type) {
                    case 'packing_list':
                        $content = "Packing List for Order #{$order['id']}\n" .
                            "Product: {$order['name']}\n" .
                            "Type: {$order['type']}\n" .
                            "Quantity: {$order['quantity']}\n" .
                            "Farmer: {$order['farmer']}\n" .
                            "Status: {$order['status']}\n" .
                            "Date: {$order['created_at']}";
                        break;
                    case 'phytosanitary':
                        $content = "Phytosanitary Certificate for Order #{$order['id']}\n" .
                            "Product: {$order['name']}\n" .
                            "Origin: {$order['origin']}\n" .
                            "Grade: {$order['grade']}\n" .
                            "Date: {$order['created_at']}";
                        break;
                    case 'certificate_of_origin':
                        $content = "Certificate of Origin for Order #{$order['id']}\n" .
                            "Product: {$order['name']}\n" .
                            "Origin: {$order['origin']}\n" .
                            "HS Code: {$order['hs_code']}\n" .
                            "Date: {$order['created_at']}";
                        break;
                    case 'export_permit':
                        $content = "Export Permit for Order #{$order['id']}\n" .
                            "Product: {$order['name']}\n" .
                            "Importer: {$order['importer']}\n" .
                            "Quantity: {$order['quantity']}\n" .
                            "Date: {$order['created_at']}";
                        break;
                }
                $stmt = $pdo->prepare("INSERT INTO export_documents (order_id, document_type, document_content) VALUES (?, ?, ?)");
                $stmt->execute([$order_id, $document_type, $content]);

                // Check if all required documents are generated
                $required_docs = ['packing_list', 'phytosanitary', 'certificate_of_origin', 'export_permit'];
                $stmt = $pdo->prepare("SELECT COUNT(DISTINCT document_type) as doc_count 
                    FROM export_documents 
                    WHERE order_id = ? AND document_type IN ('packing_list', 'phytosanitary', 'certificate_of_origin', 'export_permit')");
                $stmt->execute([$order_id]);
                $doc_count = $stmt->fetch()['doc_count'];

                if ($doc_count == count($required_docs)) {
                    // All documents generated, set status to shipped and generate tracking number
                    $tracking_number = 'TRK-' . strtoupper(substr(md5($order_id . time()), 0, 10));
                    $stmt = $pdo->prepare("UPDATE orders SET status = 'shipped' WHERE id = ? AND product_id IN (SELECT id FROM products WHERE farmer_id = ?)");
                    $stmt->execute(['shipped', $order_id, $_SESSION['user_id']]);
                    $stmt = $pdo->prepare("INSERT INTO export_documents (order_id, document_type, document_content) VALUES (?, ?, ?)");
                    $stmt->execute([$order_id, 'bill_of_lading', "Tracking Number: $tracking_number"]);
                }

                $pdo->commit();
                echo "<div class='alert alert-success'>Document generated successfully!" . ($doc_count == count($required_docs) ? " Order status updated to shipped. Tracking Number: $tracking_number" : "") . "</div>";
            } else {
                $pdo->rollBack();
                echo "<div class='alert alert-danger'>Order not found.</div>";
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
}

// Fetch orders for document generation
$where_clause = $_SESSION['role'] == 'farmer' ? "WHERE p.farmer_id = ?" : "";
$stmt = $pdo->prepare("SELECT o.id, p.name, u.username as importer 
    FROM orders o 
    JOIN products p ON o.product_id = p.id 
    JOIN users u ON o.importer_id = u.id $where_clause");
if ($_SESSION['role'] == 'farmer') {
    $stmt->execute([$_SESSION['user_id']]);
} else {
    $stmt->execute();
}
$orders = $stmt->fetchAll();

// Fetch generated documents
$where_clause = $_SESSION['role'] == 'farmer' ? "WHERE o.product_id IN (SELECT id FROM products WHERE farmer_id = ?)" : "";
$stmt = $pdo->prepare("SELECT d.*, o.id as order_id, p.name 
    FROM export_documents d 
    JOIN orders o ON d.order_id = o.id 
    JOIN products p ON o.product_id = p.id $where_clause");
if ($_SESSION['role'] == 'farmer') {
    $stmt->execute([$_SESSION['user_id']]);
} else {
    $stmt->execute();
}
$documents = $stmt->fetchAll();
?>

<!-- Responsive export documents page -->
<div class="container mt-5">
    <h2>Export Documents</h2>
    <?php if ($_SESSION['role'] == 'farmer'): ?>
        <h3>Generate Document</h3>
        <form method="POST" action="" class="col-md-6 mx-auto">
            <div class="mb-3">
                <label for="order_id" class="form-label">Select Order</label>
                <select class="form-select" id="order_id" name="order_id" required>
                    <option value="" disabled selected>Select order</option>
                    <?php foreach ($orders as $order): ?>
                        <option value="<?php echo $order['id']; ?>">Order #<?php echo $order['id']; ?> - <?php echo htmlspecialchars($order['name']); ?> (Importer: <?php echo htmlspecialchars($order['importer']); ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="document_type" class="form-label">Document Type</label>
                <select class="form-select" id="document_type" name="document_type" required>
                    <option value="" disabled selected>Select document type</option>
                    <option value="packing_list">Packing List</option>
                    <option value="phytosanitary">Phytosanitary Certificate</option>
                    <option value="certificate_of_origin">Certificate of Origin</option>
                    <option value="export_permit">Export Permit</option>
                </select>
            </div>
            <button type="submit" name="generate_doc" class="btn btn-primary">Generate Document</button>
        </form>
    <?php endif; ?>
    <h3 class="mt-4">Generated Documents</h3>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Product</th>
                    <th>Document Type</th>
                    <th>Content</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($documents)): ?>
                    <tr><td colspan="5">No documents found.</td></tr>
                <?php else: ?>
                    <?php foreach ($documents as $doc): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($doc['order_id']); ?></td>
                            <td><?php echo htmlspecialchars($doc['name']); ?></td>
                            <td><?php echo htmlspecialchars($doc['document_type']); ?></td>
                            <td><pre><?php echo htmlspecialchars($doc['document_content']); ?></pre></td>
                            <td><?php echo htmlspecialchars($doc['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'footer.php'; ?>