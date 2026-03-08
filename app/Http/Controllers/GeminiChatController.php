<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiChatController extends Controller
{
    /**
     * Route debug: GET /gemini-chat/debug
     * Truy cập trực tiếp trên trình duyệt để xem log chi tiết
     */
    public function debug()
    {
        $results = [];

        // Step 1: Check .env
        $apiKey = config('services.gemini.api_key');
        $envRaw = env('GEMINI_API_KEY');
        $results['step1_env'] = [
            'config_value_length' => strlen($apiKey ?? ''),
            'config_value_preview' => $apiKey ? substr($apiKey, 0, 15) . '...' : 'EMPTY/NULL',
            'env_raw_length' => strlen($envRaw ?? ''),
            'env_raw_preview' => $envRaw ? substr($envRaw, 0, 15) . '...' : 'EMPTY/NULL',
            'are_same' => $apiKey === $envRaw,
        ];

        if (empty($apiKey)) {
            $results['FINAL_ERROR'] = 'API key trống. Kiểm tra .env và chạy php artisan config:clear';
            return response()->json($results, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }

        // Step 2: Test SSL/cURL connectivity
        $results['step2_connectivity'] = [];
        try {
            $testUrl = "https://generativelanguage.googleapis.com/v1beta/models?key={$apiKey}";
            $response = Http::withOptions([
                'verify' => false, // Bypass SSL verify
            ])->timeout(15)->get($testUrl);

            $results['step2_connectivity'] = [
                'http_status' => $response->status(),
                'body_length' => strlen($response->body()),
            ];

            if ($response->successful()) {
                $models = $response->json();
                $geminiModels = [];
                foreach (($models['models'] ?? []) as $m) {
                    if (str_contains($m['name'] ?? '', 'gemini')) {
                        $geminiModels[] = $m['name'];
                    }
                }
                $results['step2_connectivity']['available_gemini_models'] = array_slice($geminiModels, 0, 10);
            } else {
                $results['step2_connectivity']['error_body'] = $response->json();
            }
        } catch (\Exception $e) {
            $results['step2_connectivity']['exception'] = $e->getMessage();
        }

        // Test all candidate models
        $modelsToTest = [
            'gemini-2.5-flash',
            'gemini-2.0-flash',
            'gemini-2.0-flash-lite',
        ];

        $working = [];
        foreach ($modelsToTest as $i => $model) {
            $key = 'step' . ($i + 3) . '_' . str_replace(['.', '-'], '_', $model);
            $results[$key] = $this->testModel($apiKey, $model);
            if (($results[$key]['success'] ?? false) === true) {
                $working[] = $model;
            }
        }

        if (!empty($working)) {
            $results['RECOMMENDATION'] = "Model hoạt động: " . implode(', ', $working) . ". Sử dụng model đầu tiên.";
            $results['WORKING_MODEL'] = $working[0];
        } else {
            $results['RECOMMENDATION'] = "Không có model nào hoạt động. Kiểm tra API key hoặc tạo key mới tại https://aistudio.google.com/apikey";
        }

        return response()->json($results, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Test một model Gemini cụ thể
     */
    private function testModel(string $apiKey, string $model): array
    {
        try {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

            $response = Http::withOptions([
                'verify' => false,
            ])->timeout(30)->post($url, [
                'contents' => [
                    ['role' => 'user', 'parts' => [['text' => 'Trả lời bằng 1 từ: 1+1=?']]]
                ],
                'generationConfig' => [
                    'maxOutputTokens' => 20,
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $text = $this->extractTextFromResponse($data);
                return [
                    'model' => $model,
                    'success' => true,
                    'http_status' => $response->status(),
                    'reply' => trim($text),
                    'raw_parts_count' => count($data['candidates'][0]['content']['parts'] ?? []),
                ];
            } else {
                return [
                    'model' => $model,
                    'success' => false,
                    'http_status' => $response->status(),
                    'error' => $response->json(),
                ];
            }
        } catch (\Exception $e) {
            return [
                'model' => $model,
                'success' => false,
                'exception' => $e->getMessage(),
            ];
        }
    }

    /**
     * Gửi tin nhắn tới Gemini API và nhận phản hồi
     */
    public function send(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $apiKey = config('services.gemini.api_key');

        Log::info('[GeminiChat] === BẮT ĐẦU REQUEST ===');
        Log::info('[GeminiChat] API key length: ' . strlen($apiKey ?? ''));
        Log::info('[GeminiChat] API key preview: ' . ($apiKey ? substr($apiKey, 0, 15) . '...' : 'EMPTY'));
        Log::info('[GeminiChat] User message: ' . $request->input('message'));

        if (empty($apiKey)) {
            Log::error('[GeminiChat] API key TRỐNG!');
            return response()->json([
                'success' => false,
                'message' => 'Gemini API key chưa được cấu hình. Vui lòng liên hệ quản trị viên.',
            ], 500);
        }

        try {
            $userMessage = $request->input('message');

            // System prompt
            $systemPrompt = "Bạn là trợ lý ảo của hệ thống Thư Viện Online (Libhub). "
                . "Bạn hỗ trợ người dùng về: tìm kiếm sách, hướng dẫn mượn/trả sách, "
                . "thông tin về chính sách thuê sách, thanh toán, tra cứu danh mục sách, "
                . "và các câu hỏi liên quan đến thư viện. "
                . "Trả lời bằng tiếng Việt, ngắn gọn, thân thiện và hữu ích. "
                . "Nếu câu hỏi không liên quan đến thư viện hoặc sách, "
                . "hãy lịch sự từ chối và hướng người dùng quay lại chủ đề thư viện.";

            // Lấy lịch sử chat từ session
            $history = $request->session()->get('gemini_chat_history', []);
            Log::info('[GeminiChat] History count: ' . count($history));

            // Xây dựng contents
            $contents = [];
            if (empty($history)) {
                $contents[] = [
                    'role' => 'user',
                    'parts' => [['text' => $systemPrompt . "\n\nNgười dùng: " . $userMessage]]
                ];
            } else {
                $contents = $history;
                $contents[] = [
                    'role' => 'user',
                    'parts' => [['text' => $userMessage]]
                ];
            }

            // Thử lần lượt các model từ mới đến cũ
            $modelsToTry = ['gemini-2.5-flash', 'gemini-2.0-flash', 'gemini-2.0-flash-lite'];
            $response = null;
            $model = null;

            $payload = [
                'contents' => $contents,
                'generationConfig' => [
                    'temperature' => 0.7,
                    'topK' => 40,
                    'topP' => 0.95,
                    'maxOutputTokens' => 1024,
                ],
                'safetySettings' => [
                    ['category' => 'HARM_CATEGORY_HARASSMENT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
                    ['category' => 'HARM_CATEGORY_HATE_SPEECH', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
                    ['category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
                    ['category' => 'HARM_CATEGORY_DANGEROUS_CONTENT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
                ],
            ];

            foreach ($modelsToTry as $tryModel) {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/{$tryModel}:generateContent?key={$apiKey}";
                Log::info("[GeminiChat] Thử model: {$tryModel}");

                $response = Http::withOptions([
                    'verify' => false,
                ])->timeout(30)->post($url, $payload);

                Log::info("[GeminiChat] {$tryModel} -> HTTP " . $response->status());

                if ($response->successful()) {
                    $model = $tryModel;
                    Log::info("[GeminiChat] {$tryModel} THÀNH CÔNG!");
                    break;
                } else {
                    Log::warning("[GeminiChat] {$tryModel} FAIL: " . substr($response->body(), 0, 300));
                }
            }

            if ($response->successful()) {
                $data = $response->json();
                $reply = $this->extractTextFromResponse($data);

                Log::info("[GeminiChat] THÀNH CÔNG! Model: {$model}, Reply length: " . strlen($reply));

                // Cập nhật lịch sử
                if (empty($history)) {
                    $history = [
                        ['role' => 'user', 'parts' => [['text' => $systemPrompt . "\n\nNgười dùng: " . $userMessage]]],
                        ['role' => 'model', 'parts' => [['text' => $reply]]],
                    ];
                } else {
                    $history[] = ['role' => 'user', 'parts' => [['text' => $userMessage]]];
                    $history[] = ['role' => 'model', 'parts' => [['text' => $reply]]];
                }

                if (count($history) > 20) {
                    $history = array_slice($history, -20);
                }

                $request->session()->put('gemini_chat_history', $history);

                return response()->json([
                    'success' => true,
                    'message' => $reply,
                ]);
            } else {
                $errorBody = $response->json();
                $errorMessage = $errorBody['error']['message'] ?? 'Unknown error';
                $errorStatus = $errorBody['error']['status'] ?? '';

                Log::error("[GeminiChat] TẤT CẢ MODEL ĐỀU FAIL!");
                Log::error("[GeminiChat] Last HTTP status: " . $response->status());
                Log::error("[GeminiChat] Error message: {$errorMessage}");
                Log::error("[GeminiChat] Error status: {$errorStatus}");
                Log::error("[GeminiChat] Full error body: " . $response->body());

                // Thông báo rõ ràng
                $userMsg = 'Không thể kết nối với trợ lý ảo. Vui lòng thử lại sau.';
                if (str_contains($errorMessage, 'API key expired') || str_contains($errorMessage, 'API_KEY_INVALID')) {
                    $userMsg = 'API key đã hết hạn hoặc không hợp lệ. Vui lòng liên hệ quản trị viên.';
                } elseif (str_contains($errorMessage, 'quota')) {
                    $userMsg = 'Đã vượt quá giới hạn sử dụng API. Vui lòng thử lại sau.';
                } elseif (str_contains($errorMessage, 'not found') || str_contains($errorMessage, 'does not exist')) {
                    $userMsg = 'Model AI không khả dụng. Vui lòng liên hệ quản trị viên.';
                }

                return response()->json([
                    'success' => false,
                    'message' => $userMsg,
                    'debug' => config('app.debug') ? [
                        'gemini_error' => $errorMessage,
                        'gemini_status' => $errorStatus,
                        'http_code' => $response->status(),
                    ] : null,
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error("[GeminiChat] EXCEPTION: " . $e->getMessage());
            Log::error("[GeminiChat] Trace: " . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi kết nối. Vui lòng thử lại sau.',
                'debug' => config('app.debug') ? [
                    'exception' => $e->getMessage(),
                    'file' => $e->getFile() . ':' . $e->getLine(),
                ] : null,
            ], 500);
        }
    }

    /**
     * Trích xuất text từ response Gemini (hỗ trợ cả model 2.0 và 2.5 với thinking)
     */
    private function extractTextFromResponse(array $data): string
    {
        $parts = $data['candidates'][0]['content']['parts'] ?? [];

        // Gemini 2.5 models may return thinking thoughts + text in separate parts
        // Find the last part that has 'text' key (not 'thought' key)
        $textContent = '';
        foreach ($parts as $part) {
            if (isset($part['text']) && !isset($part['thought'])) {
                $textContent = $part['text'];
            }
        }

        // If no non-thought text found, try any part with 'text' key
        if (empty($textContent)) {
            foreach ($parts as $part) {
                if (isset($part['text'])) {
                    $textContent = $part['text'];
                    break;
                }
            }
        }

        return $textContent ?: 'Xin lỗi, tôi không thể trả lời lúc này.';
    }

    /**
     * Xóa lịch sử chat
     */
    public function clearHistory(Request $request)
    {
        $request->session()->forget('gemini_chat_history');

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa lịch sử chat.',
        ]);
    }
}
