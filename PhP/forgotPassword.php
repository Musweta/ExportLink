<?php
require_once 'header.php';

// Placeholder for password reset (email functionality not implemented)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    if (!$email) {
        echo "<div class='alert alert-danger'>Please enter a valid email.</div>";
    } else {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            echo "<div class='alert alert-info'>Password reset link sent to $email (not implemented).</div>";
        } else {
            echo "<div class='alert alert-danger'>Email not found.</div>";
        }
    }
}
?>

<!-- Responsive forgot password form -->
<div class="container mt-5">
    <h2>Forgot Password</h2>
    <form method="POST" action="" class="col-md-6 mx-auto">
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required placeholder="Enter email">
        </div>
        <button type="submit" class="btn btn-primary">Send Reset Link</button>
    </form>
</div>

<?php require_once 'footer.php'; ?>