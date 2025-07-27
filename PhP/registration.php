<?php
// Include header with session and database setup
require_once 'header.php';
require_once 'db_conn.php';

if (isset($_SESSION['user_id'])) {
    // Redirect if already logged in
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    // Process registration form submission
    $username = filter_input(INPUT_POST, 'username', FILTER_UNSAFE_RAW);
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW);
    $confirm_password = filter_input(INPUT_POST, 'confirm_password', FILTER_UNSAFE_RAW);
    $role = filter_input(INPUT_POST, 'role', FILTER_UNSAFE_RAW);

    // Validate password match
    if ($password !== $confirm_password) {
        echo "<div class='alert alert-danger'>Passwords do not match.</div>";
    } elseif ($username && $email && $password && in_array($role, ['farmer', 'importer'])) {
        try {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $certification_path = null;
            if (isset($_FILES['certification']) && $_FILES['certification']['error'] == UPLOAD_ERR_OK) {
                $upload_dir = '../Uploads/';
                $file_name = uniqid() . '_' . time() . '_' . basename($_FILES['certification']['name']);
                $certification_path = $upload_dir . $file_name;
                move_uploaded_file($_FILES['certification']['tmp_name'], $certification_path);
            }

            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, certification_path, is_approved) VALUES (?, ?, ?, ?, ?, 1)");
            $stmt->execute([$username, $email, $password_hash, $role, $certification_path]);
            $user_id = $pdo->lastInsertId();

            // Send registration email
            $to = $email;
            $subject = "Welcome to ExportLink";
            $message = "<html><body style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f9f9f9;'>"
                . "<h2 style='color: #4682b4;'>Welcome to ExportLink, $username!</h2>"
                . "<p>Your account has been successfully created.</p>"
                . "<p><strong>Email:</strong> $email<br><strong>Role:</strong> $role</p>"
                . "<p>Click <a href='http://localhost/project2/PhP/login.php'>here</a> to log in.</p>"
                . "<p style='font-size: 12px; color: #666;'>This is an automated message. Please do not reply.</p>"
                . "</body></html>";
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= "From: no-reply@exportlink.com";
            mail($to, $subject, $message, $headers);

            echo "<div class='alert alert-success'>Registration successful! Check your email.</div>";
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
    <title>ExportLink - Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f0f8ff; }
        .container { background-color: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2>Register</h2>
    <form method="POST" action="" enctype="multipart/form-data" class="col-md-6 mx-auto">
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" id="username" name="username" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <div class="mb-3">
            <label for="confirm_password" class="form-label">Confirm Password</label>
            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
        </div>
        <div class="mb-3">
            <label for="role" class="form-label">Role</label>
            <select class="form-select" id="role" name="role" required>
                <option value="farmer">Farmer</option>
                <option value="importer">Importer</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="certification" class="form-label">Certification Document (Optional)</label>
            <input type="file" class="form-control" id="certification" name="certification" accept="application/pdf">
        </div>
        <button type="submit" name="register" class="btn btn-primary">Register</button>
    </form>
</div>
<script>
function toggleImporterDocs() {
    const role = document.getElementById('role').value;
    const importerDocs = document.getElementById('importer-docs');
    importerDocs.style.display = role === 'importer' ? 'block' : 'none';
    // Make importer document fields required only when role is importer
    const inputs = importerDocs.querySelectorAll('input[type="file"]');
    inputs.forEach(input => input.required = role === 'importer');
}
</script>

<?php require_once 'footer.php'; ?>