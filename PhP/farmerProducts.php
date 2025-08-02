<?php
require_once 'header.php';
require_once 'db_conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'farmer') {
    header("Location: login.php");
    exit;
}

// Handle product edit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_product'])) {
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_SANITIZE_NUMBER_INT);
    $name = trim(filter_input(INPUT_POST, 'name', FILTER_UNSAFE_RAW)); // Replaced FILTER_SANITIZE_STRING
    $price = filter_input(INPUT_POST, 'price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_SANITIZE_NUMBER_INT);
    
    // Validate and sanitize name
    $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    if (empty($product_id) || empty($name) || $price <= 0 || $quantity < 0) {
        echo "<div class='alert alert-danger'>Invalid product details.</div>";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE products SET name = ?, price = ?, quantity = ? WHERE id = ? AND farmer_id = ?");
            $stmt->execute([$name, $price, $quantity, $product_id, $_SESSION['user_id']]);
            echo "<div class='alert alert-success'>Product updated successfully!</div>";
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>Error updating product: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
}

// Handle product deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_product'])) {
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_SANITIZE_NUMBER_INT);
    
    try {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ? AND farmer_id = ?");
        $stmt->execute([$product_id, $_SESSION['user_id']]);
        echo "<div class='alert alert-success'>Product deleted successfully!</div>";
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Error deleting product: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

// Fetch only this farmer's products
$stmt = $pdo->prepare("SELECT id, name, price, quantity, image_path FROM products WHERE farmer_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Products - ExportLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f0f8ff; }
        .container { background-color: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<div class="container mt-5">
    <h2>My Listed Products</h2>
    <div class="row row-cols-1 row-cols-md-3 g-4">
        <?php if (empty($products)): ?>
            <div class="alert alert-info">You have not listed any products yet.</div>
        <?php endif; ?>
        <?php foreach ($products as $product): ?>
            <div class="col">
                <div class="card h-100">
                    <img src="<?php echo htmlspecialchars($product['image_path'] ?? '../Uploads/default.jpg'); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>" style="height: 200px; object-fit: cover;">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                        <p class="card-text">Price: <?php echo htmlspecialchars($product['price']); ?> USD</p>
                        <p class="card-text">Quantity: <?php echo htmlspecialchars($product['quantity']); ?></p>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $product['id']; ?>">Edit</button>
                        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $product['id']; ?>">Delete</button>
                    </div>
                </div>
            </div>

            <!-- Edit Product Modal -->
            <div class="modal fade" id="editModal<?php echo $product['id']; ?>" tabindex="-1" aria-labelledby="editModalLabel<?php echo $product['id']; ?>" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editModalLabel<?php echo $product['id']; ?>">Edit Product</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="POST" action="">
                            <div class="modal-body">
                                <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['id']); ?>">
                                <div class="mb-3">
                                    <label for="name<?php echo $product['id']; ?>" class="form-label">Product Name</label>
                                    <input type="text" class="form-control" id="name<?php echo $product['id']; ?>" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="price<?php echo $product['id']; ?>" class="form-label">Price (USD)</label>
                                    <input type="number" step="0.01" class="form-control" id="price<?php echo $product['id']; ?>" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="quantity<?php echo $product['id']; ?>" class="form-label">Quantity</label>
                                    <input type="number" class="form-control" id="quantity<?php echo $product['id']; ?>" name="quantity" value="<?php echo htmlspecialchars($product['quantity']); ?>" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" name="edit_product" class="btn btn-primary">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Delete Product Modal -->
            <div class="modal fade" id="deleteModal<?php echo $product['id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo $product['id']; ?>" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteModalLabel<?php echo $product['id']; ?>">Confirm Delete</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            Are you sure you want to delete the product "<?php echo htmlspecialchars($product['name']); ?>"?
                        </div>
                        <form method="POST" action="">
                            <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['id']); ?>">
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" name="delete_product" class="btn btn-danger">Delete</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php require_once 'footer.php'; ?>