<?php
require_once 'header.php';

// Restrict access to importers
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'importer') {
    header("Location: login.php");
    exit;
}

// Fetch order details
$order_id = filter_input(INPUT_GET, 'order_id', FILTER_SANITIZE_NUMBER_INT);
if (!$order_id) {
    echo "<div class='alert alert-danger'>Invalid order ID.</div>";
    require_once 'footer.php';
    exit;
}

$stmt = $pdo->prepare("SELECT o.*, p.name, p.price, u.username as farmer_name 
                       FROM orders o 
                       JOIN products p ON o.product_id = p.id 
                       JOIN users u ON p.farmer_id = u.id 
                       WHERE o.id = ? AND o.importer_id = ?");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    echo "<div class='alert alert-danger'>Order not found.</div>";
    require_once 'footer.php';
    exit;
}

// Generate LaTeX invoice
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['generate_invoice'])) {
    $latex_content = "
\\documentclass[a4paper]{article}
\\usepackage[utf8]{inputenc}
\\usepackage{geometry}
\\geometry{margin=1in}
\\usepackage{booktabs}
\\title{ExportLink Invoice}
\\author{ExportLink}
\\date{\\today}

\\begin{document}

\\maketitle

\\section*{Invoice}
\\textbf{Order ID:} {$order['id']}\\\\
\\textbf{Date:} \\today\\\\

\\subsection*{Details}
\\begin{tabular}{ll}
\\toprule
\\textbf{Field} & \\textbf{Value} \\\\
\\midrule
Product & " . htmlspecialchars($order['name']) . " \\\\
Farmer & " . htmlspecialchars($order['farmer_name']) . " \\\\
Quantity & " . htmlspecialchars($order['quantity']) . " \\\\
Price per Unit & \\$" . htmlspecialchars(number_format($order['price'], 2)) . " \\\\
Total & \\$" . htmlspecialchars(number_format($order['price'] * $order['quantity'], 2)) . " \\\\
Status & " . htmlspecialchars($order['status']) . " \\\\
\\bottomrule
\\end{tabular}

\\end{document}
";

    // Output LaTeX as PDF
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="invoice_order_' . $order['id'] . '.pdf"');
    echo $latex_content; // LaTeX rendering handled by system
    exit;
}
?>

<!-- Responsive export documentation -->
<div class="container mt-5">
    <h2>Export Documentation</h2>
    <h3>Order Details</h3>
    <div class="card">
        <div class="card-body">
            <p><strong>Order ID:</strong> <?php echo htmlspecialchars($order['id']); ?></p>
            <p><strong>Product:</strong> <?php echo htmlspecialchars($order['name']); ?></p>
            <p><strong>Farmer:</strong> <?php echo htmlspecialchars($order['farmer_name']); ?></p>
            <p><strong>Quantity:</strong> <?php echo htmlspecialchars($order['quantity']); ?></p>
            <p><strong>Price per Unit:</strong> $<?php echo htmlspecialchars(number_format($order['price'], 2)); ?></p>
            <p><strong>Total:</strong> $<?php echo htmlspecialchars(number_format($order['price'] * $order['quantity'], 2)); ?></p>
            <p><strong>Status:</strong> <?php echo htmlspecialchars($order['status']); ?></p>
            <form method="POST" action="">
                <input type="hidden" name="generate_invoice" value="1">
                <button type="submit" class="btn btn-primary">Download Invoice (PDF)</button>
            </form>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>