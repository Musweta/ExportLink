<?php
require_once 'header.php';
require_once 'db_conn.php';

// Restrict access to admins
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

// Count unapproved users
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE is_approved = 0");
    $unapproved_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
} catch (PDOException $e) {
    error_log("Admin dashboard error: " . $e->getMessage());
    $unapproved_count = 'N/A';
    echo "<div class='alert alert-danger'>Error fetching unapproved users: " . htmlspecialchars($e->getMessage()) . "</div>";
}
?>

<div class="container mt-5">
    <h2>Admin Dashboard</h2>
    <p>Welcome, Admin!</p>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">Pending Approvals</h5>
            <p class="card-text"><?php echo htmlspecialchars($unapproved_count); ?> user(s) awaiting approval.</p>
            <a href="manageUsers.php" class="btn btn-primary">Manage Users</a>
        </div>
    </div>
    <div class="d-flex flex-column flex-md-row gap-2">
        <a href="adminOrderManagement.php" class="btn btn-primary">View All Orders</a>
        <a href="viewProducts.php" class="btn btn-primary">View Products</a>
    </div>
</div>

<?php require_once 'footer.php'; ?>

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