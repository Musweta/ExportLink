<?php
require_once 'header.php';

// Restrict access to admins
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

// Handle report generation
$report_type = filter_input(INPUT_GET, 'type', FILTER_SANITIZE_STRING);
$data = [];
$filename = '';

if ($report_type == 'users') {
    $stmt = $pdo->query("SELECT username, email, role, is_approved, created_at FROM users");
    $data = $stmt->fetchAll();
    $filename = 'user_report_' . date('Ymd') . '.csv';
    $report_title = 'User Report';
} elseif ($report_type == 'orders') {
    $stmt = $pdo->query("SELECT o.id, p.name, u1.username as importer, u2.username as farmer, o.quantity, o.status, o.created_at 
        FROM orders o 
        JOIN products p ON o.product_id = p.id 
        JOIN users u1 ON o.importer_id = u1.id 
        JOIN users u2 ON p.farmer_id = u2.id");
    $data = $stmt->fetchAll();
    $filename = 'order_report_' . date('Ymd') . '.csv';
    $report_title = 'Order Report';
}

// Generate CSV if download requested
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['download']) && !empty($data)) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    $output = fopen('php://output', 'w');
    if ($report_type == 'users') {
        fputcsv($output, ['Username', 'Email', 'Role', 'Approved', 'Created At']);
        foreach ($data as $row) {
            fputcsv($output, [
                $row['username'],
                $row['email'],
                $row['role'],
                $row['is_approved'] ? 'Yes' : 'No',
                $row['created_at']
            ]);
        }
    } elseif ($report_type == 'orders') {
        fputcsv($output, ['Order ID', 'Product', 'Importer', 'Farmer', 'Quantity', 'Status', 'Created At']);
        foreach ($data as $row) {
            fputcsv($output, [
                $row['id'],
                $row['name'],
                $row['importer'],
                $row['farmer'],
                $row['quantity'],
                $row['status'],
                $row['created_at']
            ]);
        }
    }
    fclose($output);
    exit;
}
?>

<!-- Responsive report generation page -->
<div class="container mt-5">
    <h2><?php echo htmlspecialchars($report_title); ?></h2>
    <?php if (empty($data)): ?>
        <p>No data available for this report.</p>
    <?php else: ?>
        <form method="POST" action="">
            <button type="submit" name="download" class="btn btn-primary mb-3">Download CSV</button>
        </form>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <?php if ($report_type == 'users'): ?>
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Approved</th>
                            <th>Created At</th>
                        </tr>
                    <?php elseif ($report_type == 'orders'): ?>
                        <tr>
                            <th>Order ID</th>
                            <th>Product</th>
                            <th>Importer</th>
                            <th>Farmer</th>
                            <th>Quantity</th>
                            <th>Status</th>
                            <th>Created At</th>
                        </tr>
                    <?php endif; ?>
                </thead>
                <tbody>
                    <?php foreach ($data as $row): ?>
                        <?php if ($report_type == 'users'): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['role']); ?></td>
                                <td><?php echo $row['is_approved'] ? 'Yes' : 'No'; ?></td>
                                <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                            </tr>
                        <?php elseif ($report_type == 'orders'): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['id']); ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['importer']); ?></td>
                                <td><?php echo htmlspecialchars($row['farmer']); ?></td>
                                <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                                <td><?php echo htmlspecialchars($row['status']); ?></td>
                                <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
    <a href="adminDashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
</div>

<?php require_once 'footer.php'; ?>