<?php
// Include header with session and database setup
require_once 'header.php';
require_once 'db_conn.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ExportLink - Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f0f8ff; }
        .container { background-color: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
<div class="container mt-5">
    <h1>Welcome to ExportLink</h1>
    <p>Connecting Kenyan smallholder farmers with international buyers.</p>
    <div class="d-flex flex-column flex-md-row gap-2">
        <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="registration.php" class="btn btn-primary">Get Started</a>
            <a href="login.php" class="btn btn-outline-primary">Login</a>
        <?php else: ?>
            <a href="<?php
                // Determine dashboard based on user role
                echo $user['role'] == 'admin' ? 'adminDashboard.php' :
                     ($user['role'] == 'importer' ? 'importerDashboard.php' : 'farmerDashboard.php');
            ?>" class="btn btn-primary">Go to Dashboard</a>
        <?php endif; ?>
    </div>
</div>
<?php require_once 'footer.php'; ?>