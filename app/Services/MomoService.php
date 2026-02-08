<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MomoService
{
    public function createPayment(Order $order)
    {
        $endpoint    = config('services.momo.endpoint');
        $partnerCode = config('services.momo.partner_code');
        $accessKey   = config('services.momo.access_key');
        $secretKey   = config('services.momo.secret_key');
        $redirectUrl = config('services.momo.return_url');
        $ipnUrl      = config('services.momo.notify_url');

        // ===== DỮ LIỆU BẮT BUỘC =====
        $orderId   = (string) $order->order_number;
        $requestId = (string) time();
        $amount    = (string) intval($order->total_amount);

        // ❗ PHẢI GIỐNG 100% KHI KÝ & KHI GỬI
        $orderInfo = "Thanh toan don hang {$orderId}";
        $extraData = ""; // BẮT BUỘC RỖNG nếu không dùng

        // ===== TẠO CHUỖI KÝ =====
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

        // ===== LOG DEBUG =====
        Log::info('MOMO RAW HASH', ['rawHash' => $rawHash]);
        Log::info('MOMO SIGNATURE', ['signature' => $signature]);

        // ===== GỬI REQUEST SANG MOMO =====
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

        // ===== BẮT LỖI =====
        if (!isset($result['payUrl'])) {
            Log::error('MOMO ERROR', $result);
            throw new \Exception('MoMo error: ' . json_encode($result));
        }

        return $result['payUrl'];
    }
}
