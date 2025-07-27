<?php
// Include header with session and database setup
require_once 'header.php';
require_once 'db_conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    // Redirect non-admins to login
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['approve'])) {
    $user_id = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT);
    $action = filter_input(INPUT_POST, 'action', FILTER_UNSAFE_RAW);
    if ($user_id && in_array($action, ['approve', 'revoke'])) {
        try {
            $stmt = $pdo->prepare("UPDATE users SET is_approved = ? WHERE id = ?");
            $approved = ($action == 'approve') ? 1 : 0;
            $stmt->execute([$approved, $user_id]);
            echo "<div class='alert alert-success'>User " . ($action == 'approve' ? 'approved' : 'revoked') . " successfully!</div>";
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
}

$stmt = $pdo->query("SELECT u.*, (SELECT document_path FROM user_documents WHERE user_id = u.id AND document_type = 'import_export_license') as license_path FROM users u");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ExportLink - Manage Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f0f8ff; }
        .container { background-color: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2>Manage Users</h2>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Approved</th>
                    <th>Certification</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr><td colspan="6">No users found.</td></tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['role']); ?></td>
                            <td><?php echo $user['is_approved'] ? 'Yes' : 'No'; ?></td>
                            <td><a href="<?php echo htmlspecialchars($user['license_path'] ?? '#'); ?>" target="_blank" class="btn btn-sm btn-secondary">View</a></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <?php if ($user['is_approved']): ?>
                                        <button type="submit" name="action" value="revoke" class="btn btn-warning btn-sm">Revoke</button>
                                    <?php else: ?>
                                        <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">Approve</button>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once 'footer.php'; ?>