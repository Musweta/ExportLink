<?php
// Include header with session and database setup
require_once 'header.php';
require_once 'db_conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'farmer') {
    // Redirect non-farmers to login
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_product'])) {
    // Process product creation form
    $name = filter_input(INPUT_POST, 'name', FILTER_UNSAFE_RAW);
    $type = filter_input(INPUT_POST, 'type', FILTER_UNSAFE_RAW);
    $description = filter_input(INPUT_POST, 'description', FILTER_UNSAFE_RAW);
    $price = filter_input(INPUT_POST, 'price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_SANITIZE_NUMBER_INT);
    $origin = filter_input(INPUT_POST, 'origin', FILTER_UNSAFE_RAW);
    $grade = filter_input(INPUT_POST, 'grade', FILTER_UNSAFE_RAW);
    $hs_code = filter_input(INPUT_POST, 'hs_code', FILTER_UNSAFE_RAW);

    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = '../Uploads/';
        $file_name = $_SESSION['user_id'] . '_' . time() . '_' . basename($_FILES['image']['name']);
        $image_path = $upload_dir . $file_name;
        move_uploaded_file($_FILES['image']['tmp_name'], $image_path);
    }

    if (empty($name) || empty($type) || empty($price) || $quantity <= 0 || empty($origin) || empty($grade) || empty($hs_code)) {
        echo "<div class='alert alert-danger'>Invalid product details.</div>";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO products (farmer_id, name, type, description, price, quantity, origin, grade, image_path, hs_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $name, $type, $description, $price, $quantity, $origin, $grade, $image_path, $hs_code]);
            echo "<div class='alert alert-success'>Product added successfully!</div>";
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
}

// Fetch products listed by the farmer
$stmt = $pdo->prepare("SELECT * FROM products WHERE farmer_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ExportLink - Product Listing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f0f8ff; }
        .container { background-color: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2>Product Listing</h2>
    <h3>Add New Product</h3>
    <form method="POST" action="" enctype="multipart/form-data" class="col-md-6 mx-auto mb-4">
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="mb-3">
            <label for="type" class="form-label">Type</label>
            <input type="text" class="form-control" id="type" name="type" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description"></textarea>
        </div>
        <div class="mb-3">
            <label for="price" class="form-label">Price (USD)</label>
            <input type="number" step="0.01" class="form-control" id="price" name="price" required>
        </div>
        <div class="mb-3">
            <label for="quantity" class="form-label">Quantity</label>
            <input type="number" class="form-control" id="quantity" name="quantity" required>
        </div>
        <div class="mb-3">
            <label for="origin" class="form-label">Origin</label>
            <input type="text" class="form-control" id="origin" name="origin" required>
        </div>
        <div class="mb-3">
            <label for="grade" class="form-label">Grade</label>
            <input type="text" class="form-control" id="grade" name="grade" required>
        </div>
        <div class="mb-3">
            <label for="hs_code" class="form-label">HS Code</label>
            <input type="text" class="form-control" id="hs_code" name="hs_code" required>
        </div>
        <div class="mb-3">
            <label for="image" class="form-label">Product Image</label>
            <input type="file" class="form-control" id="image" name="image" accept="image/*">
        </div>
        <button type="submit" name="create_product" class="btn btn-primary">Add Product</button>
    </form>
    <h3>Available Products</h3>
    <div class="row row-cols-1 row-cols-md-3 g-4">
        <?php foreach ($products as $product): ?>
            <div class="col">
                <div class="card h-100">
                    <img src="<?php echo htmlspecialchars($product['image_path'] ?? '../Uploads/default.jpg'); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>" style="height: 200px; object-fit: cover;">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                        <p class="card-text">Price: <?php echo htmlspecialchars($product['price']); ?> USD</p>
                        <p class="card-text">Quantity: <?php echo htmlspecialchars($product['quantity']); ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php require_once 'footer.php'; ?>