<?php
require_once 'header.php';
require_once 'db_conn.php';

// Handle user login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize username manually
    $username = filter_input(INPUT_POST, 'username', FILTER_UNSAFE_RAW);
    $username = trim(htmlspecialchars($username ?? ''));
    $password = $_POST['password'] ?? '';

    // Validate inputs
    if (empty($username) || empty($password)) {
        echo "<div class='alert alert-danger'>Please fill in all fields.</div>";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Debug: Log is_approved and role
                error_log("Login attempt: username=$username, role={$user['role']}, is_approved=" . ($user['is_approved'] ?? 'NULL'));

                // Exempt admins from approval check; non-admins require is_approved = 1
                if ($user['role'] != 'admin' && !($user['is_approved'] ?? false)) {
                    echo "<div class='alert alert-warning'>Your account is awaiting admin approval.</div>";
                } else {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['role'] = $user['role'];
                    // Redirect based on role
                    $redirect = match ($user['role']) {
                        'admin' => 'adminDashboard.php',
                        'farmer' => 'farmerDashboard.php',
                        'importer' => 'importerDashboard.php',
                        default => 'index.php' // Fallback
                    };
                    header("Location: $redirect");
                    exit;
                }
            } else {
                echo "<div class='alert alert-danger'>Invalid username or password.</div>";
            }
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            echo "<div class='alert alert-danger'>Error: Database issue. Please try again later.</div>";
        }
    }
}
?>

<!-- Responsive login form -->
<div class="container mt-5">
    <h2>Login</h2>
    <form method="POST" action="" class="col-md-6 mx-auto">
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" id="username" name="username" required placeholder="Enter username">
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required placeholder="Enter password">
        </div>
        <div class="d-flex flex-column flex-md-row gap-2">
            <button type="submit" class="btn btn-primary">Login</button>
            <a href="forgotPassword.php" class="btn btn-link">Forgot Password?</a>
        </div>
    </form>
</div>

<?php require_once 'footer.php'; ?>