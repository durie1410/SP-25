<?php

namespace App\\Http\\Controllers;

use Illuminate\\Http\\Request;
use Illuminate\\Support\\Facades\\Redirect;
use Illuminate\\Support\\Facades\\Session;

class MomoPaymentController
{
    const MOMO_API_ENDPOINT = 'https://api.momo.vn/v2/gateway/api/create';
    const ACCESS_KEY = 'your_access_key'; // Replace with your Momo access key
    const SECRET_KEY = 'your_secret_key'; // Replace with your Momo secret key
    const M0RE_MOMO_URL = 'https://sandbox.momo.vn/v2/gateway/api/create';

    public function initiatePayment(Request $request)
    {
        $requestData = [
            'accessKey' => self::ACCESS_KEY,
            'partnerCode' => 'your_partner_code', // Replace with your partner code
            'orderId' => uniqid(),
            'amount' => $request->input('amount'),
            'orderInfo' => $request->input('orderInfo'),
            'returnUrl' => route('payment.callback'),
            'notifyUrl' => '',
            'extraData' => ''
        ];

        $requestData['signature'] = $this->generateSignature($requestData);

        $response = $this->callMomoAPI($requestData);

        return Redirect::to($response['payUrl']);
    }

    private function generateSignature($data)
    {
        ksort($data);
        $string = http_build_query($data);
        return hash_hmac('sha256', $string, self::SECRET_KEY);
    }

    private function callMomoAPI($data)
    {
        $client = new \GuzzleHttp\\Client();
        $response = $client->request('POST', self::MOMO_API_ENDPOINT, [
            'json' => $data
        ]);
        return json_decode($response->getBody(), true);
    }

    public function callback(Request $request)
    {
        // Handle Momo callback logic here
        // Validate transaction and update order status accordingly
        $status = $request->input('status');
        if ($status == 'success') {
            $orderId = $request->input('orderId');
            // Update order in database based on $orderId
            // Show success message
            return view('payment.success', compact('orderId'));
        } else {
            // Show failure message
            return view('payment.fail');
        }
    }

    public function result()
    {
        // Display payment result page
        return view('payment.result');
    }
}