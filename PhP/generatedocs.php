<?php
// Include Dompdf autoloader
require_once '../dompdf/autoload.inc.php';

use Dompdf\Dompdf;

// Include database connection
require_once 'db_conn.php';

// Create an instance of Dompdf
$dompdf = new Dompdf();

// Fetch order details for invoice and receipt generation
$order_id = filter_input(INPUT_GET, 'order_id', FILTER_SANITIZE_NUMBER_INT);
if ($order_id) {
    $stmt = $pdo->prepare("SELECT o.*, p.name, p.type, p.price, p.hs_code, p.origin, p.grade, u1.username as farmer, u2.username as importer, u2.email as importer_email 
                          FROM orders o 
                          JOIN products p ON o.product_id = p.id 
                          JOIN users u1 ON p.farmer_id = u1.id 
                          JOIN users u2 ON o.importer_id = u2.id 
                          WHERE o.id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($order) {
        $conversion_rates = ['USD' => 1, 'KES' => 130, 'EUR' => 0.185];
        $base_price = $order['price'];
        $converted_price = $base_price * $conversion_rates[$order['currency']];
        $total = $order['quantity'] * $converted_price;

        // Generate combined commercial invoice and receipt content
        $html = "<h1>ExportLink Order Document</h1>
                 <h2>Commercial Invoice</h2>
                 Invoice Number: INV-$order_id<br>
                 Date: " . date('Y-m-d H:i:s') . "<br>
                 Seller: {$order['farmer']}<br>
                 Importer: {$order['importer']} ({$order['importer_email']})<br>
                 Product: {$order['name']}<br>
                 Type: {$order['type']}<br>
                 Quantity: {$order['quantity']}<br>
                 Unit Price: $converted_price {$order['currency']}<br>
                 Total: $total {$order['currency']}<br>
                 Payment Terms: {$order['payment_terms']}<br>
                 HS Code: {$order['hs_code']}<br>
                 Country of Origin: {$order['origin']}<br>
                 Grade: {$order['grade']}<br>
                 Delivery Address: {$order['delivery_address']}<br><br>

                 <h2>Receipt</h2>
                 Receipt Number: REC-$order_id<br>
                 Date: " . date('Y-m-d H:i:s') . "<br>
                 Paid By: {$order['importer']}<br>
                 Product: {$order['name']}<br>
                 Quantity: {$order['quantity']}<br>
                 Unit Price: $converted_price {$order['currency']}<br>
                 Total Paid: $total {$order['currency']}<br>
                 Payment Method: {$order['payment_terms']}<br>
                 Status: " . ($order['status'] == 'delivered' ? 'Paid' : 'Pending') . "<br>";
        $dompdf->loadHtml($html);
    } else {
        $html = '<h1>Error</h1><p>Order not found.</p>';
        $dompdf->loadHtml($html);
    }
} else {
    // Default content if no order_id is provided
    $html = '<h1>Hello from ExportLink!</h1><p>This PDF was generated today.</p>';
    $dompdf->loadHtml($html);
}

// Set paper size and orientation
$dompdf->setPaper('A4', 'portrait');

// Render the HTML as PDF
$dompdf->render();

// Check if action is "download" or "view"
$action = isset($_GET['action']) && $_GET['action'] === 'download' ? 'download' : 'view';

// Stream the PDF
$dompdf->stream("exportlink_order_$order_id.pdf", array("Attachment" => $action === 'download'));
?>