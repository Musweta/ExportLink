<?php
require_once 'header.php';

// Placeholder for password reset functionality
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    // Implement password reset logic (e.g., send email with reset link)
    echo "<div class='alert alert-info'>Password reset link sent to $email.</div>";
}
?>

<div class="container mt-5">
    <h2>Forgot Password</h2>
    <form method="POST" action="">
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <button type="submit" class="btn btn-primary">Send Reset Link</button>
    </form>
</div>

<?php require_once 'footer.php'; ?>