<?php
require_once 'header.php';

// Handle user registration
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize username and role manually
    $username = filter_input(INPUT_POST, 'username', FILTER_UNSAFE_RAW);
    $username = trim(htmlspecialchars($username ?? ''));
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = password_hash($_POST['password'] ?? '', PASSWORD_BCRYPT);
    $role = filter_input(INPUT_POST, 'role', FILTER_UNSAFE_RAW);
    $role = trim(htmlspecialchars($role ?? ''));

    // Validate inputs
    if (empty($username) || empty($email) || empty($password) || !in_array($role, ['farmer', 'importer', 'admin'])) {
        echo "<div class='alert alert-danger'>Invalid input data.</div>";
    } else {
        try {
            // Exempt admins from approval by setting is_approved = 1
            $is_approved = $role == 'admin' ? 1 : 0;
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, is_approved) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$username, $email, $password, $role, $is_approved]);
            $message = $role == 'admin' 
                ? "Registration successful! Please <a href='login.php'>login</a>." 
                : "Registration successful! Awaiting admin approval.";
            echo "<div class='alert alert-success'>$message</div>";
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
                <option value="admin">Admin</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Register</button>
    </form>
</div>

<?php require_once 'footer.php'; ?>