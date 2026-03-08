<?php
/**
 * Diagnostic script to test Gemini chatbox connectivity
 * Access: http://localhost:8000/quanlythuviennn (1)/quanlythuviennn/public/test-gemini-debug.php
 */

header('Content-Type: application/json');

// 1. Check .env file
$envFile = __DIR__ . '/../.env';
$envContent = file_exists($envFile) ? file_get_contents($envFile) : 'NOT FOUND';
$apiKey = '';
if (preg_match('/^GEMINI_API_KEY=(.*)$/m', $envContent, $m)) {
    $apiKey = trim($m[1]);
}

// 2. Test Gemini API directly
$apiResult = null;
$apiError = null;
if (!empty($apiKey)) {
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey}";
    $payload = json_encode([
        'contents' => [
            ['role' => 'user', 'parts' => [['text' => 'Xin chào, trả lời ngắn gọn bằng tiếng Việt.']]]
        ],
        'generationConfig' => [
            'maxOutputTokens' => 100,
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

    if ($curlError) {
        $apiError = "cURL error: {$curlError}";
    } else {
        $apiResult = json_decode($response, true);
    }
}

// 3. Check route file exists
$routeFile = __DIR__ . '/../routes/web.php';
$routeContent = file_exists($routeFile) ? file_get_contents($routeFile) : 'NOT FOUND';
$hasGeminiRoute = strpos($routeContent, 'gemini-chat/send') !== false;

// 4. Check controller exists
$controllerFile = __DIR__ . '/../app/Http/Controllers/GeminiChatController.php';
$controllerExists = file_exists($controllerFile);

// 5. Check config
$configFile = __DIR__ . '/../config/services.php';
$configContent = file_exists($configFile) ? file_get_contents($configFile) : 'NOT FOUND';
$hasGeminiConfig = strpos($configContent, "'gemini'") !== false;

// 6. Test Laravel bootstrap
$laravelError = null;
try {
    require __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    
    // Simulate request
    $request = Illuminate\Http\Request::create('/gemini-chat/send', 'POST', ['message' => 'test'], [], [], [
        'HTTP_ACCEPT' => 'application/json',
        'HTTP_X_CSRF_TOKEN' => 'test', // This will fail CSRF, but we can see the error
    ]);
    
    $response = $kernel->handle($request);
    $laravelStatus = $response->getStatusCode();
    $laravelBody = $response->getContent();
    $kernel->terminate($request, $response);
} catch (Exception $e) {
    $laravelError = $e->getMessage();
    $laravelStatus = 'EXCEPTION';
    $laravelBody = $e->getTraceAsString();
}

echo json_encode([
    'timestamp' => date('Y-m-d H:i:s'),
    '1_env' => [
        'api_key_exists' => !empty($apiKey),
        'api_key_length' => strlen($apiKey),
        'api_key_preview' => !empty($apiKey) ? substr($apiKey, 0, 10) . '...' : 'EMPTY',
    ],
    '2_files' => [
        'controller_exists' => $controllerExists,
        'route_has_gemini' => $hasGeminiRoute,
        'config_has_gemini' => $hasGeminiConfig,
    ],
    '3_gemini_api_direct_test' => [
        'http_code' => $httpCode ?? null,
        'curl_error' => $apiError,
        'response_preview' => $apiResult 
            ? ($apiResult['candidates'][0]['content']['parts'][0]['text'] ?? 'no text in response')
            : ($response ?? 'no response'),
    ],
    '4_laravel_route_test' => [
        'status' => $laravelStatus ?? null,
        'error' => $laravelError,
        'body_preview' => isset($laravelBody) ? substr($laravelBody, 0, 500) : null,
    ],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
