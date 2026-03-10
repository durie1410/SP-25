<?php
/**
 * Test Gemini API directly - see full error response
 */
header('Content-Type: application/json');

$envFile = __DIR__ . '/../.env';
$envContent = file_get_contents($envFile);
preg_match('/^GEMINI_API_KEY=(.*)$/m', $envContent, $m);
$apiKey = trim($m[1] ?? '');

if (empty($apiKey)) {
    echo json_encode(['error' => 'NO API KEY']);
    exit;
}

// Test with gemini-2.0-flash
$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey}";
$payload = json_encode([
    'contents' => [
        ['role' => 'user', 'parts' => [['text' => 'Xin chào']]]
    ],
]);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// Also test with gemini-1.5-flash
$url2 = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}";
$ch2 = curl_init($url2);
curl_setopt($ch2, CURLOPT_POST, true);
curl_setopt($ch2, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch2, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_TIMEOUT, 30);
curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
$response2 = curl_exec($ch2);
$httpCode2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
curl_close($ch2);

// Also test list models
$urlModels = "https://generativelanguage.googleapis.com/v1beta/models?key={$apiKey}";
$ch3 = curl_init($urlModels);
curl_setopt($ch3, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch3, CURLOPT_TIMEOUT, 15);
curl_setopt($ch3, CURLOPT_SSL_VERIFYPEER, false);
$responseModels = curl_exec($ch3);
$httpCodeModels = curl_getinfo($ch3, CURLINFO_HTTP_CODE);
curl_close($ch3);

$models = json_decode($responseModels, true);
$modelNames = [];
if (isset($models['models'])) {
    foreach ($models['models'] as $model) {
        if (strpos($model['name'], 'gemini') !== false) {
            $modelNames[] = $model['name'];
        }
    }
}

echo json_encode([
    'api_key_preview' => substr($apiKey, 0, 15) . '...',
    'test_gemini_2_0_flash' => [
        'http_code' => $httpCode,
        'curl_error' => $curlError ?: null,
        'response' => json_decode($response, true),
    ],
    'test_gemini_1_5_flash' => [
        'http_code' => $httpCode2,
        'response' => json_decode($response2, true),
    ],
    'available_gemini_models' => [
        'http_code' => $httpCodeModels,
        'models' => $modelNames,
    ],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
