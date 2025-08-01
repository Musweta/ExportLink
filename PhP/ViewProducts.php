<?php
// Include header with session and database setup
require_once 'header.php';
require_once 'db_conn.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['importer', 'admin', ])) {
    // Redirect unauthorized users to login
    header("Location: login.php");
    exit;
}

$stmt = $pdo->query("SELECT id, name, price, image_path FROM products");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ExportLink - View Products</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f0f8ff; }
        .container { background-color: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2>View Products</h2>
    <div class="row row-cols-1 row-cols-md-3 g-4">
        <?php foreach ($products as $product): ?>
            <div class="col">
                <div class="card h-100">
                    <img src="<?php echo htmlspecialchars($product['image_path'] ?? '../Uploads/default.jpg'); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>" style="height: 200px; object-fit: cover;">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                        <p class="card-text">Price: <?php echo htmlspecialchars($product['price']); ?> USD</p>
                        <a href="?product_id=<?php echo $product['id']; ?>" class="btn btn-primary">View Details</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php if (isset($_GET['product_id'])): ?>
        <?php
        $stmt = $pdo->prepare("SELECT id, name, type, description, price, quantity, origin, grade, image_path, hs_code FROM products WHERE id = ?");
        $stmt->execute([$_GET['product_id']]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($product): ?>
            <div class="modal fade show" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" style="display:block;" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="productModalLabel"><?php echo htmlspecialchars($product['name']); ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="window.history.back();"></button>
                        </div>
                        <div class="modal-body">
                            <img src="<?php echo htmlspecialchars($product['image_path'] ?? '../Uploads/default.jpg'); ?>" class="img-fluid mb-3" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <p><strong>Type:</strong> <?php echo htmlspecialchars($product['type']); ?></p>
                            <p><strong>Description:</strong> <?php echo htmlspecialchars($product['description'] ?? 'N/A'); ?></p>
                            <p><strong>Price:</strong> <?php echo htmlspecialchars($product['price']); ?> USD</p>
                            <p><strong>Quantity Available:</strong> <?php echo htmlspecialchars($product['quantity']); ?></p>
                            <p><strong>Origin:</strong> <?php echo htmlspecialchars($product['origin']); ?></p>
                            <p><strong>Grade:</strong> <?php echo htmlspecialchars($product['grade']); ?></p>
                            <p><strong>HS Code:</strong> <?php echo htmlspecialchars($product['hs_code']); ?></p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="window.history.back();">Close</button>
                            <a href="orderManagement.php?product_id=<?php echo $product['id']; ?>" class="btn btn-primary">Place Order</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-danger mt-3">Product not found.</div>
        <?php endif; ?>
    <?php endif; ?>
</div>
<?php require_once 'footer.php'; ?>