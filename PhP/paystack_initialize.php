<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $amount = $_POST['amount'] * 129;   // Convert to KES (1 KES = 129 USD)
    
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => "https://api.paystack.co/transaction/initialize",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => json_encode([
        'email' => $email,
        'amount' => $amount,
        'callback_url' => 'http://localhost/verify_payment.php' 
      ]),
      CURLOPT_HTTPHEADER => [
        "authorization: Bearer YOUR_TEST_SECRET_KEY", // Replace with your test secret key
        "content-type: application/json",
        "cache-control: no-cache"
      ],
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    if ($err) {
      echo "cURL Error #:" . $err;
    } else {
      $tranx = json_decode($response, true);
      if (!$tranx['status']) {
        echo "API returned error: " . $tranx['message'];
      } else {
        header('Location: ' . $tranx['data']['authorization_url']);
        exit;
      }
    }
}
?>
