<?php
// Include header with session and database setup
require_once 'header.php';
require_once 'db_conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'farmer') {
    // Redirect non-farmers to login
    header("Location: login.php");
    exit;
}

// Fetch products
$stmt = $pdo->prepare("SELECT * FROM products WHERE farmer_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch summary statistics
$stmt = $pdo->prepare("SELECT status, COUNT(*) as count FROM orders WHERE product_id IN (SELECT id FROM products WHERE farmer_id = ?) GROUP BY status");
$order_stats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
$total_orders = array_sum($order_stats);
$pending = $order_stats['pending'] ?? 0;
$confirmed = $order_stats['confirmed'] ?? 0;
$shipped = $order_stats['shipped'] ?? 0;
$delivered = $order_stats['delivered'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ExportLink - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f0f8ff; }
        .container { background-color: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        canvas { max-width: 100%; }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="container mt-5">
    <a href="productListing.php" class="btn btn-primary mb-3">List Products</a>
    <a href="orderManagement.php" class="btn btn-primary mb-3">Manage Orders</a>
    <a href="farmerProducts.php" class="btn btn-primary mb-3">View Products</a>
    <div class="row row-cols-1 row-cols-md-3 g-4">
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
    <!-- <h3>Dashboard</h3> 
    <div class="card mb-3">
        <div class="card-body">
            <canvas id="orderChart"></canvas>
            <p>Total Orders: <?php echo $total_orders; ?></p>
            <p>Pending: <?php echo $pending; ?></p>
            <p>Confirmed: <?php echo $confirmed; ?></p>
            <p>Shipped: <?php echo $shipped; ?></p>
            <p>Delivered: <?php echo $delivered; ?></p>
        </div>
    </div> -->
</div>

<?php require_once 'footer.php'; ?>