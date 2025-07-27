<?php
require_once '../dompdf/autoload.inc.php';

use Dompdf\Dompdf;

// Create an instance of Dompdf
$dompdf = new Dompdf();

// Load your HTML content
$html = '<h1>Hello from ExportLink!</h1><p>This PDF was generated today.</p>';
$dompdf->loadHtml($html);

// Set paper size and orientation
$dompdf->setPaper('A4', 'portrait');

// Render the HTML as PDF
$dompdf->render();

// Check if action is "download" or "view"
$action = isset($_GET['action']) && $_GET['action'] === 'download' ? 'download' : 'view';

// Stream the PDF
$dompdf->stream("exportlink_report.pdf", array("Attachment" => $action === 'download'));
?>
