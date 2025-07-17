<?php
require_once 'header.php';

// Restrict access to admins
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

// Fetch statistics
$user_count = $pdo->query("SELECT COUNT(*) as count FROM users")->fetch()['count'];
$pending_users = $pdo->query("SELECT COUNT(*) as count FROM users WHERE approval_status = 'pending'")->fetch()['count'];
$order_count = $pdo->query("SELECT COUNT(*) as count FROM orders")->fetch()['count'];
$activity_logs = $pdo->query("SELECT a.*, u.username FROM activity_logs a JOIN users u ON a.user_id = u.id ORDER BY a.created_at DESC LIMIT 10")->fetchAll();

// Generate report (CSV download)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['generate_report'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="activity_report.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Username', 'Action', 'Timestamp']);
    foreach ($activity_logs as $log) {
        fputcsv($output, [$log['id'], $log['username'], $log['action'], $log['created_at']]);
    }
    fclose($output);
    exit;
}
?>

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