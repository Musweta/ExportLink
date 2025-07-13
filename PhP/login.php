<?php
require_once 'header.php';

// Handle user login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = $_POST['password'];

    // Validate inputs
    if (!$username || !$password) {
        echo "<div class='alert alert-danger'>Please fill in all fields.</div>";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            header("Location: " . ($user['role'] == 'admin' ? 'adminDashboard.php' : 'farmerDashboard.php'));
            exit;
        } else {
            echo "<div class='alert alert-danger'>Invalid username or password.</div>";
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
            <a href="forgot_password.php" class="btn btn-link">Forgot Password?</a>
        </div>
    </form>
</div>

<?php require_once 'footer.php'; ?>