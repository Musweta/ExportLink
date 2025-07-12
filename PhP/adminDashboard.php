<?php
require_once 'header.php';

// Restrict access to admins
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

// Fetch user and order statistics
$user_count = $pdo->query("SELECT COUNT(*) as count FROM users")->fetch()['count'];
$order_count = $pdo->query("SELECT COUNT(*) as count FROM orders")->fetch()['count'];
?>

<div class="container mt-5">
    <h2>Admin Dashboard</h2>
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Total Users</h5>
                    <p class="card-text"><?php echo $user_count; ?></p>
                    <a href="manageUsers.php" class="btn btn-primary">Manage Users</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Total Orders</h5>
                    <p class="card-text"><?php echo $order_count; ?></p>
                    <a href="orderManagement.php" class="btn btn-primary">View Orders</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>