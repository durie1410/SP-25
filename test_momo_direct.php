<?php
/**
 * Test MoMo API trực tiếp
 * Chạy: php test_momo_direct.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$endpoint    = config('services.momo.endpoint');
$partnerCode = config('services.momo.partner_code');
$accessKey   = config('services.momo.access_key');
$secretKey   = config('services.momo.secret_key');

echo "=== MoMo Config Test ===\n";
echo "Endpoint: $endpoint\n";
echo "PartnerCode: $partnerCode\n";
echo "AccessKey: $accessKey\n";
echo "SecretKey: $secretKey\n";
echo "SecretKey Length: " . strlen($secretKey) . "\n\n";

// Test với số tiền 1000 VND
$amount = 1000;
$orderId = 'TEST_' . time();
$requestId = (string) time();
$orderInfo = 'Test_thanh_toan';
$redirectUrl = 'http://127.0.0.1:8000/test';
$ipnUrl = 'http://127.0.0.1:8000/test';
$extraData = base64_encode('test');

$rawHash = "accessKey={$accessKey}&amount={$amount}&extraData={$extraData}&ipnUrl={$ipnUrl}&orderId={$orderId}&orderInfo={$orderInfo}&partnerCode={$partnerCode}&redirectUrl={$redirectUrl}&requestId={$requestId}&requestType=captureWallet";

echo "=== RAW HASH ===\n$rawHash\n\n";

$signature = hash_hmac('sha256', $rawHash, $secretKey);
echo "Signature: $signature\n\n";

echo "=== Sending Request to MoMo ===\n";

$ch = curl_init($endpoint);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'partnerCode' => $partnerCode,
        'accessKey'   => $accessKey,
        'requestId'   => $requestId,
        'amount'      => (string) $amount,
        'orderId'     => $orderId,
        'orderInfo'   => $orderInfo,
        'redirectUrl' => $redirectUrl,
        'ipnUrl'      => $ipnUrl,
        'extraData'   => $extraData,
        'requestType' => 'captureWallet',
        'signature'   => $signature,
        'lang'        => 'vi',
    ]),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response: $response\n";
echo "cURL Error: $error\n";
