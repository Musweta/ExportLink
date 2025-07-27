<?php
// Include header with session and database setup
require_once 'header.php';
require_once 'db_conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'importer') {
    // Redirect non-importers to login
    header("Location: login.php");
    exit;
}

// Fetch summary statistics
$stmt = $pdo->prepare("SELECT status, COUNT(*) as count FROM orders WHERE importer_id = ? GROUP BY status");
$order_stats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
$total_orders = array_sum($order_stats);
$pending = $order_stats['pending'] ?? 0;
$confirmed = $order_stats['confirmed'] ?? 0;
$shipped = $order_stats['shipped'] ?? 0;
$delivered = $order_stats['delivered'] ?? 0;
$stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'importer'");
$user_count = $stmt->fetch()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ExportLink - Importer Dashboard</title>
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
    <h2>Importer Dashboard</h2>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">Order Statistics</h5>
            <canvas id="orderChart"></canvas>
            <p>Total Orders: <?php echo $total_orders; ?></p>
            <p>Pending: <?php echo $pending; ?></p>
            <p>Confirmed: <?php echo $confirmed; ?></p>
            <p>Shipped: <?php echo $shipped; ?></p>
            <p>Delivered: <?php echo $delivered; ?></p>
            <p>Total Importers: <?php echo $user_count; ?></p>
        </div>
    </div>
    <a href="viewProducts.php" class="btn btn-primary mb-3">View Products</a>
    <a href="orderManagement.php" class="btn btn-primary mb-3">Manage Orders</a>
</div>
<script>
    const ctx = document.getElementById('orderChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Pending', 'Confirmed', 'Shipped', 'Delivered'],
            datasets: [{
                label: 'Order Status',
                data: [<?php echo $pending; ?>, <?php echo $confirmed; ?>, <?php echo $shipped; ?>, <?php echo $delivered; ?>],
                backgroundColor: ['#ff6384', '#36a2eb', '#ffcd56', '#4bc0c0']
            }]
        },
        options: { scales: { y: { beginAtZero: true } } }
    });
</script>
<?php require_once 'footer.php'; ?>