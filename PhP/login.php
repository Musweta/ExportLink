<?php
// Include header with session and database setup
require_once 'header.php';
require_once 'db_conn.php';

if (isset($_GET['logout'])) {
    // Handle logout request
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit;
}

if (isset($_SESSION['user_id'])) {
    // Redirect if already logged in
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    // Process login form submission
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW);

    if ($email && $password) {
        try {
            $stmt = $pdo->prepare("SELECT id, password, role, is_approved FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            if ($user && password_verify($password, $user['password']) && ($user['role'] != 'admin' || $user['is_approved'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role']; // Set role in session
                header("Location: index.php");
                exit;
            } else {
                echo "<div class='alert alert-danger'>Invalid credentials or account awaiting approval.</div>";
            }
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>Invalid input.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ExportLink - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f0f8ff; }
        .container { background-color: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2>Login</h2>
    <form method="POST" action="" class="col-md-6 mx-auto">
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <button type="submit" name="login" class="btn btn-primary">Login</button>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="?logout=1" class="btn btn-danger ms-2">Logout</a>
        <?php endif; ?>
    </form>
</div>
<?php require_once 'footer.php'; ?>