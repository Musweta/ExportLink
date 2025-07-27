<?php
require_once 'header.php';

// Restrict access to admins
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

// Handle user approval
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['approve_id'])) {
    $approve_id = filter_input(INPUT_POST, 'approve_id', FILTER_SANITIZE_NUMBER_INT);
    try {
        $stmt = $pdo->prepare("UPDATE users SET is_approved = 1 WHERE id = ? AND role != 'admin'");
        $stmt->execute([$approve_id]);
        echo "<div class='alert alert-success'>User approved successfully.</div>";
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

// Handle user deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_id'])) {
    $delete_id = filter_input(INPUT_POST, 'delete_id', FILTER_SANITIZE_NUMBER_INT);
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("DELETE FROM importer_documents WHERE user_id = ?");
        $stmt->execute([$delete_id]);
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
        $stmt->execute([$delete_id]);
        $pdo->commit();
        echo "<div class='alert alert-success'>User and documents deleted successfully.</div>";
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

// Fetch all users
$stmt = $pdo->query("SELECT * FROM users");
$users = $stmt->fetchAll();

// Fetch importer documents
$documents = [];
foreach ($users as $user) {
    if ($user['role'] == 'importer') {
        $stmt = $pdo->prepare("SELECT document_type, file_path FROM importer_documents WHERE user_id = ?");
        $stmt->execute([$user['id']]);
        $documents[$user['id']] = $stmt->fetchAll();
    }
}
?>

<!-- Responsive user management -->
<div class="container mt-5">
    <h2>Manage Users</h2>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Actions</th>
                    <th>Documents</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr><td colspan="7">No users found.</td></tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['id']); ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['role']); ?></td>
                            <td><?php echo $user['is_approved'] ? 'Approved' : 'Pending'; ?></td>
                            <td>
                                <?php if (!$user['is_approved'] && $user['role'] != 'admin'): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="approve_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                    </form>
                                <?php endif; ?>
                                <?php if ($user['role'] != 'admin'): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="delete_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user?');">Archive</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($user['role'] == 'importer' && isset($documents[$user['id']])): ?>
                                    <ul>
                                        <?php foreach ($documents[$user['id']] as $doc): ?>
                                            <li><?php echo htmlspecialchars($doc['document_type']); ?>: <a href="<?php echo htmlspecialchars($doc['file_path']); ?>" target="_blank">View</a></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    None
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'footer.php'; ?>