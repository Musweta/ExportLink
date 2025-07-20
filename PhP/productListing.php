<?php
require_once 'header.php';

// Restrict access to authenticated users
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Handle product creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_SESSION['role'] == 'farmer' && isset($_POST['create'])) {
    $name = filter_input(INPUT_POST, 'name', FILTER_UNSAFE_RAW);
    $name = trim(htmlspecialchars($name ?? ''));
    $type = filter_input(INPUT_POST, 'type', FILTER_UNSAFE_RAW);
    $type = trim(htmlspecialchars($type ?? ''));
    $description = filter_input(INPUT_POST, 'description', FILTER_UNSAFE_RAW);
    $description = trim(htmlspecialchars($description ?? ''));
    $price = filter_input(INPUT_POST, 'price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_SANITIZE_NUMBER_INT);
    $origin = filter_input(INPUT_POST, 'origin', FILTER_UNSAFE_RAW);
    $origin = trim(htmlspecialchars($origin ?? ''));
    $grade = filter_input(INPUT_POST, 'grade', FILTER_UNSAFE_RAW);
    $grade = trim(htmlspecialchars($grade ?? ''));
    $hs_code = filter_input(INPUT_POST, 'hs_code', FILTER_UNSAFE_RAW);
    $hs_code = trim(htmlspecialchars($hs_code ?? ''));
    $certification = '';

    // Handle certification file upload
    if (isset($_FILES['certification']) && $_FILES['certification']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = '../Uploads/';
        $file_name = $_SESSION['user_id'] . '_cert_' . time() . '_' . basename($_FILES['certification']['name']);
        $file_path = $upload_dir . $file_name;
        if (move_uploaded_file($_FILES['certification']['tmp_name'], $file_path)) {
            $certification = $file_path;
        }
    }

    if (empty($name) || empty($type) || empty($price) || empty($quantity) || empty($origin) || empty($grade) || empty($hs_code)) {
        echo "<div class='alert alert-danger'>Please fill in all required fields.</div>";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO products (farmer_id, name, type, description, price, quantity, origin, grade, hs_code, certification) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $name, $type, $description, $price, $quantity, $origin, $grade, $hs_code, $certification]);
            echo "<div class='alert alert-success'>Product listed successfully!</div>";
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
}

// Handle product update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_SESSION['role'] == 'farmer' && isset($_POST['update'])) {
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_SANITIZE_NUMBER_INT);
    $name = filter_input(INPUT_POST, 'name', FILTER_UNSAFE_RAW);
    $name = trim(htmlspecialchars($name ?? ''));
    $type = filter_input(INPUT_POST, 'type', FILTER_UNSAFE_RAW);
    $type = trim(htmlspecialchars($type ?? ''));
    $description = filter_input(INPUT_POST, 'description', FILTER_UNSAFE_RAW);
    $description = trim(htmlspecialchars($description ?? ''));
    $price = filter_input(INPUT_POST, 'price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_SANITIZE_NUMBER_INT);
    $origin = filter_input(INPUT_POST, 'origin', FILTER_UNSAFE_RAW);
    $origin = trim(htmlspecialchars($origin ?? ''));
    $grade = filter_input(INPUT_POST, 'grade', FILTER_UNSAFE_RAW);
    $grade = trim(htmlspecialchars($grade ?? ''));
    $hs_code = filter_input(INPUT_POST, 'hs_code', FILTER_UNSAFE_RAW);
    $hs_code = trim(htmlspecialchars($hs_code ?? ''));
    $certification = '';

    if (isset($_FILES['certification']) && $_FILES['certification']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = '../Uploads/';
        $file_name = $_SESSION['user_id'] . '_cert_' . time() . '_' . basename($_FILES['certification']['name']);
        $file_path = $upload_dir . $file_name;
        if (move_uploaded_file($_FILES['certification']['tmp_name'], $file_path)) {
            $certification = $file_path;
        }
    }

    if (empty($product_id) || empty($name) || empty($type) || empty($price) || empty($quantity) || empty($origin) || empty($grade) || empty($hs_code)) {
        echo "<div class='alert alert-danger'>Please fill in all required fields.</div>";
    } else {
        try {
            $query = "UPDATE products SET name = ?, type = ?, description = ?, price = ?, quantity = ?, origin = ?, grade = ?, hs_code = ?" . ($certification ? ", certification = ?" : "") . " WHERE id = ? AND farmer_id = ?";
            $stmt = $pdo->prepare($query);
            $params = [$name, $type, $description, $price, $quantity, $origin, $grade, $hs_code];
            if ($certification) $params[] = $certification;
            $params[] = $product_id;
            $params[] = $_SESSION['user_id'];
            $stmt->execute($params);
            echo "<div class='alert alert-success'>Product updated successfully!</div>";
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
}

// Handle product deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_SESSION['role'] == 'farmer' && isset($_POST['delete'])) {
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_SANITIZE_NUMBER_INT);
    try {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ? AND farmer_id = ?");
        $stmt->execute([$product_id, $_SESSION['user_id']]);
        echo "<div class='alert alert-success'>Product deleted successfully!</div>";
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

// Fetch products for display
$where_clause = $_SESSION['role'] == 'farmer' ? "WHERE p.farmer_id = ?" : "";
$stmt = $pdo->prepare("SELECT p.*, u.username FROM products p JOIN users u ON p.farmer_id = u.id $where_clause");
if ($_SESSION['role'] == 'farmer') {
    $stmt->execute([$_SESSION['user_id']]);
} else {
    $stmt->execute();
}
$products = $stmt->fetchAll();

// Fetch product for editing
$edit_product = null;
if ($_SESSION['role'] == 'farmer' && isset($_GET['edit_id'])) {
    $edit_id = filter_input(INPUT_GET, 'edit_id', FILTER_SANITIZE_NUMBER_INT);
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND farmer_id = ?");
    $stmt->execute([$edit_id, $_SESSION['user_id']]);
    $edit_product = $stmt->fetch();
}
?>

<!-- Responsive product listing -->
<div class="container mt-5">
    <h2>Product Listing</h2>
    <?php if ($_SESSION['role'] == 'farmer'): ?>
        <!-- Product creation/update form -->
        <form method="POST" action="" enctype="multipart/form-data" class="col-md-6 mx-auto">
            <input type="hidden" name="product_id" value="<?php echo $edit_product ? $edit_product['id'] : ''; ?>">
            <div class="mb-3">
                <label for="name" class="form-label">Product Name</label>
                <input type="text" class="form-control" id="name" name="name" required placeholder="Enter product name" value="<?php echo $edit_product ? htmlspecialchars($edit_product['name']) : ''; ?>">
            </div>
            <div class="mb-3">
                <label for="type" class="form-label">Product Type</label>
                <input type="text" class="form-control" id="type" name="type" required placeholder="e.g., Fruit, Vegetable" value="<?php echo $edit_product ? htmlspecialchars($edit_product['type']) : ''; ?>">
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" placeholder="Enter description"><?php echo $edit_product ? htmlspecialchars($edit_product['description']) : ''; ?></textarea>
            </div>
            <div class="mb-3">
                <label for="price" class="form-label">Price per unit(USD)</label>
                <input type="number" step="0.01" class="form-control" id="price" name="price" required placeholder="Enter price" value="<?php echo $edit_product ? htmlspecialchars($edit_product['price']) : ''; ?>">
            </div>
            <div class="mb-3">
                <label for="quantity" class="form-label">Quantity</label>
                <input type="number" class="form-control" id="quantity" name="quantity" required placeholder="Enter quantity" value="<?php echo $edit_product ? htmlspecialchars($edit_product['quantity']) : ''; ?>">
            </div>
            <div class="mb-3">
                <label for="origin" class="form-label">Country of Origin</label>
                <input type="text" class="form-control" id="origin" name="origin" required placeholder="e.g., Kenya" value="<?php echo $edit_product ? htmlspecialchars($edit_product['origin']) : ''; ?>">
            </div>
            <div class="mb-3">
                <label for="grade" class="form-label">Grade</label>
                <input type="text" class="form-control" id="grade" name="grade" required placeholder="e.g., Grade A" value="<?php echo $edit_product ? htmlspecialchars($edit_product['grade']) : ''; ?>">
            </div>
            <div class="mb-3">
                <label for="hs_code" class="form-label">HS Code</label>
                <input type="text" class="form-control" id="hs_code" name="hs_code" required placeholder="e.g., 080450" value="<?php echo $edit_product ? htmlspecialchars($edit_product['hs_code']) : ''; ?>">
            </div>
            <div class="mb-3">
                <label for="certification" class="form-label">Certification (e.g., Organic, Phytosanitary)</label>
                <input type="file" class="form-control" id="certification" name="certification" accept=".pdf,.jpg,.png">
            </div>
            <button type="submit" name="<?php echo $edit_product ? 'update' : 'create'; ?>" class="btn btn-primary"><?php echo $edit_product ? 'Update Product' : 'List Product'; ?></button>
            <?php if ($edit_product): ?>
                <a href="productListing.php" class="btn btn-secondary">Cancel</a>
            <?php endif; ?>
        </form>
    <?php endif; ?>
    <h3 class="mt-4">Available Products</h3>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Origin</th>
                    <th>Grade</th>
                    <th>HS Code</th>
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
                    <tr><td colspan="<?php echo $_SESSION['role'] == 'farmer' ? 10 : 9; ?>">No products found.</td></tr>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo htmlspecialchars($product['type']); ?></td>
                            <td><?php echo htmlspecialchars($product['description'] ?? 'No description'); ?></td>
                            <td><?php echo htmlspecialchars(number_format($product['price'], 2)); ?></td>
                            <td><?php echo htmlspecialchars($product['quantity']); ?></td>
                            <td><?php echo htmlspecialchars($product['origin']); ?></td>
                            <td><?php echo htmlspecialchars($product['grade']); ?></td>
                            <td><?php echo htmlspecialchars($product['hs_code']); ?></td>
                            <td><?php echo htmlspecialchars($product['username']); ?></td>
                            <?php if ($_SESSION['role'] == 'farmer'): ?>
                                <td>
                                    <a href="productListing.php?edit_id=<?php echo $product['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                        <button type="submit" name="delete" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this product?');">Delete</button>
                                    </form>
                                </td>
                            <?php elseif ($_SESSION['role'] == 'importer'): ?>
                                <td><a href="orderManagement.php?product_id=<?php echo $product['id']; ?>" class="btn btn-primary btn-sm">Order</a></td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'footer.php'; ?>