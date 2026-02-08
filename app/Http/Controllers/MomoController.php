<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Order;

class MomoController extends Controller
{
    // Tạo thanh toán MoMo và TRẢ payUrl (không redirect)
public function createPayment(Order $order)
{
    $endpoint    = config('services.momo.endpoint');
    $partnerCode = config('services.momo.partner_code');
    $accessKey   = config('services.momo.access_key');
    $secretKey   = config('services.momo.secret_key');
    $redirectUrl = config('services.momo.return_url');
    $ipnUrl      = config('services.momo.notify_url');

    $orderId   = (string) $order->order_number;
    $requestId = (string) time();
    $amount    = (string) intval($order->total_amount);

    // ❗ ASCII – KHÔNG DẤU – KHÔNG SPACE
    $orderInfo = 'Thanh_toan_don_hang_' . $orderId;

    $extraData = base64_encode('momo');

    $rawHash =
        "accessKey={$accessKey}" .
        "&amount={$amount}" .
        "&extraData={$extraData}" .
        "&ipnUrl={$ipnUrl}" .
        "&orderId={$orderId}" .
        "&orderInfo={$orderInfo}" .
        "&partnerCode={$partnerCode}" .
        "&redirectUrl={$redirectUrl}" .
        "&requestId={$requestId}" .
        "&requestType=captureWallet";

    $signature = hash_hmac('sha256', $rawHash, $secretKey);

    \Log::info('MOMO RAW HASH', ['rawHash' => $rawHash]);
    \Log::info('MOMO SIGNATURE', ['signature' => $signature]);

    $response = Http::post($endpoint, [
        'partnerCode' => $partnerCode,
        'accessKey'   => $accessKey,
        'requestId'   => $requestId,
        'amount'      => $amount,
        'orderId'     => $orderId,
        'orderInfo'   => $orderInfo,
        'redirectUrl' => $redirectUrl,
        'ipnUrl'      => $ipnUrl,
        'extraData'   => $extraData,
        'requestType' => 'captureWallet',
        'signature'   => $signature,
        'lang'        => 'vi',
    ]);

    $result = $response->json();

    if (!isset($result['payUrl'])) {
        throw new \Exception('MoMo error: ' . json_encode($result));
    }

    return $result['payUrl'];
}

}
