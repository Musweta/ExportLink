<?php
require_once 'header.php';
require_once 'db_conn.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['farmer', 'importer'])) {
    header("Location: login.php");
    exit;
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW);
    $password_hash = !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : null;

    if ($email) {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
            $stmt->execute([$email, $_SESSION['user_id']]);
            if ($password_hash) {
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$password_hash, $_SESSION['user_id']]);
            }
            $pdo->commit();
            echo "<div class='alert alert-success'>Profile updated successfully!</div>";
        } catch (PDOException $e) {
            $pdo->rollBack();
            echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>Invalid email format.</div>";
    }
}

// Fetch user details
try {
    $stmt = $pdo->prepare("SELECT username, email, created_at FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Profile fetch error: " . $e->getMessage());
    echo "<div class='alert alert-danger'>Error fetching profile: " . htmlspecialchars($e->getMessage()) . "</div>";
}
?>

<div class="container mt-5">
    <h2>Profile</h2>
    <?php if ($user): ?>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><?php echo htmlspecialchars($user['username']); ?></h5>
                <p class="card-text"><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p class="card-text"><strong>Joined:</strong> <?php echo htmlspecialchars($user['created_at']); ?></p>
            </div>
        </div>
        <h3 class="mt-4">Update Profile</h3>
        <form method="POST" action="" class="col-md-6 mx-auto">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">New Password (leave blank to keep current)</label>
                <input type="password" class="form-control" id="password" name="password">
            </div>
            <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
        </form>
    <?php else: ?>
        <div class="alert alert-danger">Profile not found.</div>
    <?php endif; ?>
    <a href="<?php echo $_SESSION['role'] == 'farmer' ? 'farmerDashboard.php' : 'importerDashboard.php'; ?>" class="btn btn-secondary mt-3">Back to Dashboard</a>
</div>

<?php require_once 'footer.php'; ?>