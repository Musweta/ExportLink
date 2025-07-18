<?php
require_once 'header.php';

// Handle user registration
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_STRING);

    // Validate inputs
    if (!$username || !$email || !$password || !in_array($role, ['farmer', 'importer', 'admin'])) {
        echo "<div class='alert alert-danger'>Invalid input data.</div>";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, approval_status) VALUES (?, ?, ?, ?, 'pending')");
            $stmt->execute([$username, $email, $password, $role]);
            // Log activity
            $user_id = $pdo->lastInsertId();
            $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action) VALUES (?, ?)");
            $stmt->execute([$user_id, "Registered as $role"]);
            echo "<div class='alert alert-success'>Registration successful! Awaiting admin approval.</div>";
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
}
?>

<!-- Responsive registration form -->
<div class="container mt-5">
    <h2>Register</h2>
    <form method="POST" action="" class="col-md-6 mx-auto">
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" id="username" name="username" required placeholder="Enter username">
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required placeholder="Enter email">
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required placeholder="Enter password">
        </div>
        <div class="mb-3">
            <label for="role" class="form-label">Role</label>
            <select class="form-select" id="role" name="role" required>
                <option value="" disabled selected>Select role</option>
                <option value="farmer">Farmer</option>
                <option value="importer">Importer</option>
                <option value="admin">Administrator</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Register</button>
    </form>
</div>

<?php require_once 'footer.php'; ?>