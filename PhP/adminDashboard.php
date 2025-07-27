<?php
// Include header with session and database setup
require_once 'header.php';
require_once 'db_conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    // Redirect non-admins to login
    header("Location: login.php");
    exit;
}

// Fetch summary statistics
$stmt = $pdo->query("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
$order_stats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
$total_orders = array_sum($order_stats);
$pending = $order_stats['pending'] ?? 0;
$confirmed = $order_stats['confirmed'] ?? 0;
$shipped = $order_stats['shipped'] ?? 0;
$delivered = $order_stats['delivered'] ?? 0;

$stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'farmer'");
$farmer_count = $stmt->fetch()['count'];
$stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'importer'");
$importer_count = $stmt->fetch()['count'];
$stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
$total_users = $stmt->fetch()['count'];
$stmt = $pdo->query("SELECT COUNT(*) as count FROM products");
$total_products = $stmt->fetch()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ExportLink - Admin Dashboard</title>
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
    <h2>Admin Dashboard</h2>
    <a href="orderManagement.php" class="btn btn-primary mb-3">View All Orders</a>
    <a href="manageUsers.php" class="btn btn-primary mb-3">Manage Users</a>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">System Report</h5>
            <p>Farmers: <?php echo $farmer_count; ?></p>
            <p>Importers: <?php echo $importer_count; ?></p>
            <p>Total Users: <?php echo $total_users; ?></p>
            <p>Total Orders: <?php echo $total_orders; ?></p>
            <p>Total Products: <?php echo $total_products; ?></p>
            <canvas id="systemChart" style="max-height: 200px;"></canvas>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">Order Statistics</h5>
            <canvas id="orderChart"></canvas>
            <p>Total Orders: <?php echo $total_orders; ?></p>
            <p>Pending: <?php echo $pending; ?></p>
            <p>Confirmed: <?php echo $confirmed; ?></p>
            <p>Shipped: <?php echo $shipped; ?></p>
            <p>Delivered: <?php echo $delivered; ?></p>
        </div>
    </div>
    
</div>
<script>
    const ctxOrder = document.getElementById('orderChart').getContext('2d');
    new Chart(ctxOrder, {
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

    const ctxSystem = document.getElementById('systemChart').getContext('2d');
    new Chart(ctxSystem, {
        type: 'pie',
        data: {
            labels: ['Farmers', 'Importers', 'Orders', 'Products'],
            datasets: [{
                data: [<?php echo $farmer_count; ?>, <?php echo $importer_count; ?>, <?php echo $total_orders; ?>, <?php echo $total_products; ?>],
                backgroundColor: ['#ff6384', '#36a2eb', '#ffcd56', '#4bc0c0']
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });
</script>
<?php require_once 'footer.php'; ?>