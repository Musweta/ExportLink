<?php
require_once 'header.php';
require_once 'db_conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'farmer') {
    header("Location: login.php");
    exit;
}

// Fetch only this farmer's products
$stmt = $pdo->prepare("SELECT id, name, price, quantity, image_path FROM products WHERE farmer_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Products - ExportLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f0f8ff; }
        .container { background-color: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2>My Listed Products</h2>
    <div class="row row-cols-1 row-cols-md-3 g-4">
        <?php if (empty($products)): ?>
            <div class="alert alert-info">You have not listed any products yet.</div>
        <?php endif; ?>
        <?php foreach ($products as $product): ?>
            <div class="col">
                <div class="card h-100">
                    <img src="<?php echo htmlspecialchars($product['image_path'] ?? '../Uploads/default.jpg'); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>" style="height: 200px; object-fit: cover;">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                        <p class="card-text">Price: <?php echo htmlspecialchars($product['price']); ?> USD</p>
                        <p class="card-text">Quantity: <?php echo htmlspecialchars($product['quantity']); ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php require_once 'footer.php'; ?>
