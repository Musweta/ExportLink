<?php
if (!isset($_GET['reference'])) {
    die('No reference supplied');
}

$reference = $_GET['reference'];

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . rawurlencode($reference),
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_HTTPHEADER => [
    "accept: application/json",
    "authorization: Bearer YOUR_TEST_SECRET_KEY", // Replace with your test secret key
    "cache-control: no-cache"
  ],
));

$response = curl_exec($curl);
$err = curl_error($curl);

if ($err) {
  echo "cURL Error #:" . $err;
} else {
  $result = json_decode($response, true);

  if ($result['data']['status'] == 'success') {
    echo "<h2>Payment Successful!</h2>";
    echo "Reference: " . $result['data']['reference'];
    // You can now store the transaction in your database
  } else {
    echo "<h2>Payment Failed or Cancelled!</h2>";
  }
}
?>
