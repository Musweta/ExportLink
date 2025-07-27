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
$stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
$user_count = $stmt->fetch()['count'];
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
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">Order Statistics</h5>
            <canvas id="orderChart"></canvas>
            <p>Total Orders: <?php echo $total_orders; ?></p>
            <p>Pending: <?php echo $pending; ?></p>
            <p>Confirmed: <?php echo $confirmed; ?></p>
            <p>Shipped: <?php echo $shipped; ?></p>
            <p>Delivered: <?php echo $delivered; ?></p>
            <p>Total Users: <?php echo $user_count; ?></p>
        </div>
    </div>
    <a href="orderManagement.php" class="btn btn-primary mb-3">View All Orders</a>
    <a href="manageUsers.php" class="btn btn-primary mb-3">Manage Users</a>
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
<!-- Responsive admin dashboard -->
<div class="container mt-5">
    <h2>Admin Dashboard</h2>
    <div class="row row-cols-1 row-cols-md-3 g-4">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Total Users</h5>
                    <p class="card-text"><?php echo $user_count; ?></p>
                    <a href="manageUsers.php" class="btn btn-primary">Manage Users</a>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Pending Approvals</h5>
                    <p class="card-text"><?php echo $pending_users; ?></p>
                    <a href="approveUsers.php" class="btn btn-primary">Review Approvals</a>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Total Orders</h5>
                    <p class="card-text"><?php echo $order_count; ?></p>
                    <a href="orderManagement.php" class="btn btn-primary">View Orders</a>
                </div>
            </div>
        </div>
    </div>
    <!-- Recent Activity -->
    <h3 class="mt-4">Recent Activity</h3>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Action</th>
                    <th>Timestamp</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($activity_logs)): ?>
                    <tr><td colspan="3">No activity found.</td></tr>
                <?php else: ?>
                    <?php foreach ($activity_logs as $log): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($log['username']); ?></td>
                            <td><?php echo htmlspecialchars($log['action']); ?></td>
                            <td><?php echo htmlspecialchars($log['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <!-- Report Generation -->
    <form method="POST" action="">
        <input type="hidden" name="generate_report" value="1">
        <button type="submit" class="btn btn-primary mt-3">Download Activity Report (CSV)</button>
    </form>
</div>

<?php require_once 'footer.php'; ?>