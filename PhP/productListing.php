<?php
require_once 'header.php';

// Restrict access to authenticated users
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Handle product listing
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_SESSION['role'] == 'farmer' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $price = filter_input(INPUT_POST, 'price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_SANITIZE_NUMBER_INT);

    if (!$name || !$price || !$quantity) {
        echo "<div class='alert alert-danger'>Please fill in all required fields.</div>";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO products (farmer_id, name, description, price, quantity) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $name, $description, $price, $quantity]);
            $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action) VALUES (?, ?)");
            $stmt->execute([$_SESSION['user_id'], "Listed product: $name"]);
            echo "<div class='alert alert-success'>Product listed successfully!</div>";
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
}

// Handle product update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_SESSION['role'] == 'farmer' && isset($_POST['action']) && $_POST['action'] == 'update') {
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_SANITIZE_NUMBER_INT);
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $price = filter_input(INPUT_POST, 'price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_SANITIZE_NUMBER_INT);

    if (!$product_id || !$name || !$price || !$quantity) {
        echo "<div class='alert alert-danger'>Please fill in all required fields.</div>";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, quantity = ? WHERE id = ? AND farmer_id = ?");
            $stmt->execute([$name, $description, $price, $quantity, $product_id, $_SESSION['user_id']]);
            $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action) VALUES (?, ?)");
            $stmt->execute([$_SESSION['user_id'], "Updated product: $name"]);
            echo "<div class='alert alert-success'>Product updated successfully!</div>";
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
}

// Handle product deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_SESSION['role'] == 'farmer' && isset($_POST['action']) && $_POST['action'] == 'delete') {
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_SANITIZE_NUMBER_INT);
    try {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ? AND farmer_id = ?");
        $stmt->execute([$product_id, $_SESSION['user_id']]);
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action) VALUES (?, ?)");
        $stmt->execute([$_SESSION['user_id'], "Deleted product ID: $product_id"]);
        echo "<div class='alert alert-success'>Product deleted successfully!</div>";
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

// Fetch products
$where_clause = $_SESSION['role'] == 'farmer' ? "WHERE p.farmer_id = ?" : "";
$stmt = $pdo->prepare("SELECT p.*, u.username FROM products p JOIN users u ON p.farmer_id = u.id $where_clause");
if ($_SESSION['role'] == 'farmer') {
    $stmt->execute([$_SESSION['user_id']]);
} else {
    $stmt->execute();
}
$products = $stmt->fetchAll();
?>

<!-- Responsive product listing -->
<div class="container mt-5">
    <h2>Product Listing</h2>
    <?php if ($_SESSION['role'] == 'farmer'): ?>
        <!-- Add Product Form -->
        <h3>Add New Product</h3>
        <form method="POST" action="" class="col-md-6 mx-auto">
            <input type="hidden" name="action" value="add">
            <div class="mb-3">
                <label for="name" class="form-label">Product Name</label>
                <input type="text" class="form-control" id="name" name="name" required placeholder="Enter product name">
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" placeholder="Enter description"></textarea>
            </div>
            <div class="mb-3">
                <label for="price" class="form-label">Price</label>
                <input type="number" step="0.01" class="form-control" id="price" name="price" required placeholder="Enter price">
            </div>
            <div class="mb-3">
                <label for="quantity" class="form-label">Quantity</label>
                <input type="number" class="form-control" id="quantity" name="quantity" required placeholder="Enter quantity">
            </div>
            <button type="submit" class="btn btn-primary">Add Product</button>
        </form>
    <?php endif; ?>
    <!-- Product List -->
    <h3 class="mt-4"><?php echo $_SESSION['role'] == 'farmer' ? 'Your Products' : 'Available Products'; ?></h3>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Farmer</th>
                    <?php if ($_SESSION['role'] == 'farmer'): ?>
                        <th>Actions</th>
                    <?php elseif ($_SESSION['role'] == 'importer'): ?>
                        <th>Action</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                    <tr><td colspan="<?php echo $_SESSION['role'] == 'farmer' ? 6 : 5; ?>">No products found.</td></tr>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo htmlspecialchars($product['description'] ?? 'No description'); ?></td>
                            <td><?php echo htmlspecialchars(number_format($product['price'], 2)); ?></td>
                            <td><?php echo htmlspecialchars($product['quantity']); ?></td>
                            <td><?php echo htmlspecialchars($product['username']); ?></td>
                            <?php if ($_SESSION['role'] == 'farmer'): ?>
                                <td>
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $product['id']; ?>">Edit</button>
                                    <form method="POST" action="" style="display:inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this product?');">Delete</button>
                                    </form>
                                </td>
                            <?php elseif ($_SESSION['role'] == 'importer'): ?>
                                <td><a href="orderManagement.php?product_id=<?php echo $product['id']; ?>" class="btn btn-primary">Order</a></td>
                            <?php endif; ?>
                        </tr>
                        <!-- Edit Product Modal -->
                        <?php if ($_SESSION['role'] == 'farmer'): ?>
                            <div class="modal fade" id="editModal<?php echo $product['id']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Product</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form method="POST" action="">
                                                <input type="hidden" name="action" value="update">
                                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                <div class="mb-3">
                                                    <label for="name<?php echo $product['id']; ?>" class="form-label">Product Name</label>
                                                    <input type="text" class="form-control" id="name<?php echo $product['id']; ?>" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="description<?php echo $product['id']; ?>" class="form-label">Description</label>
                                                    <textarea class="form-control" id="description<?php echo $product['id']; ?>" name="description"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="price<?php echo $product['id']; ?>" class="form-label">Price</label>
                                                    <input type="number" step="0.01" class="form-control" id="price<?php echo $product['id']; ?>" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="quantity<?php echo $product['id']; ?>" class="form-label">Quantity</label>
                                                    <input type="number" class="form-control" id="quantity<?php echo $product['id']; ?>" name="quantity" value="<?php echo htmlspecialchars($product['quantity']); ?>" required>
                                                </div>
                                                <button type="submit" class="btn btn-primary">Update Product</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'footer.php'; ?>