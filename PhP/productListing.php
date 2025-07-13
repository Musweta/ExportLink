<?php
require_once 'header.php';

// Restrict access to authenticated users
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Handle product listing for farmers
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_SESSION['role'] == 'farmer') {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $price = filter_input(INPUT_POST, 'price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_SANITIZE_NUMBER_INT);

    // Validate inputs
    if (!$name || !$price || !$quantity) {
        echo "<div class='alert alert-danger'>Please fill in all required fields.</div>";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO products (farmer_id, name, description, price, quantity) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $name, $description, $price, $quantity]);
            echo "<div class='alert alert-success'>Product listed successfully!</div>";
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
}

// Fetch products for display
$stmt = $pdo->query("SELECT p.*, u.username FROM products p JOIN users u ON p.farmer_id = u.id");
$products = $stmt->fetchAll();
?>

<!-- Responsive product listing -->
<div class="container mt-5">
    <h2>Product Listing</h2>
    <?php if ($_SESSION['role'] == 'farmer'): ?>
        <form method="POST" action="" class="col-md-6 mx-auto">
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
            <button type="submit" class="btn btn-primary">List Product</button>
        </form>
    <?php endif; ?>
    <h3 class="mt-4">Available Products</h3>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Farmer</th>
                    <?php if ($_SESSION['role'] == 'importer'): ?>
                        <th>Action</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td><?php echo htmlspecialchars($product['description'] ?? 'No description'); ?></td>
                        <td><?php echo htmlspecialchars(number_format($product['price'], 2)); ?></td>
                        <td><?php echo htmlspecialchars($product['quantity']); ?></td>
                        <td><?php echo htmlspecialchars($product['username']); ?></td>
                        <?php if ($_SESSION['role'] == 'importer'): ?>
                            <td><a href="orderManagement.php?product_id=<?php echo $product['id']; ?>" class="btn btn-primary">Order</a></td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'footer.php'; ?>