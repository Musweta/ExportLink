<?php
require_once 'header.php';

// Database connection
$host = "localhost";
$dbUser = "root";
$dbPass = "student";
$dbName = "isproject";

// Create MySQL connection
$conn = new mysqli($host, $dbUser, $dbPass, $dbName);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables for form repopulation
$username = '';
$email = '';
$role = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $email    = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) ?? '';
    $role     = trim($_POST['role'] ?? '');

    // Validate fields
    if ($username === '' || $password === '' || $email === '' || $role === '') {
        $errors[] = "All fields are required!";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    // Proceed if no errors
    if (empty($errors)) {
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Check if username exists
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $checkStmt->bind_param("s", $username);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            $errors[] = "Username already taken. Please choose another.";
        } else {
            // Insert user
            $insertStmt = $conn->prepare("INSERT INTO users (username, password, role, email) VALUES (?, ?, ?, ?)");
            $insertStmt->bind_param("ssss", $username, $hashedPassword, $role, $email);

            if ($insertStmt->execute()) {
                echo "<div class='alert alert-success'>Registration successful! You can now <a href='login.php'>login</a>.</div>";
                header("Location: productListing.php");
                exit;
            } else {
                $errors[] = "Database error: " . $insertStmt->error;
            }

            $insertStmt->close();
        }

        $checkStmt->close();
    }

    $conn->close();
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
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Register</button>
    </form>
</div>

<?php require_once 'footer.php'; ?>