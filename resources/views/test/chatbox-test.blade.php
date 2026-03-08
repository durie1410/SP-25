<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Test Gemini Chatbox</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f0f2f5;
            min-height: 100vh;
        }

        .test-header {
            background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
            color: #fff;
            padding: 24px 0;
            margin-bottom: 30px;
        }

        .test-header h1 {
            font-size: 1.6rem;
            font-weight: 700;
            margin: 0;
        }

        .test-header p {
            opacity: 0.85;
            margin: 4px 0 0;
            font-size: 0.9rem;
        }

        .test-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            padding: 24px;
            margin-bottom: 24px;
        }

        .test-card h5 {
            font-weight: 700;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status-ok {
            background: #dcfce7;
            color: #16a34a;
        }

        .status-fail {
            background: #fef2f2;
            color: #dc2626;
        }

        .status-pending {
            background: #fef9c3;
            color: #ca8a04;
        }

        .test-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .test-item:last-child {
            border-bottom: none;
        }

        .test-item-label {
            font-weight: 500;
            color: #475569;
        }

        .test-log {
            background: #0f172a;
            color: #e2e8f0;
            border-radius: 8px;
            padding: 16px;
            font-family: 'Courier New', monospace;
            font-size: 0.82rem;
            max-height: 300px;
            overflow-y: auto;
            margin-top: 12px;
        }

        .test-log .log-ok {
            color: #34d399;
        }

        .test-log .log-fail {
            color: #f87171;
        }

        .test-log .log-info {
            color: #60a5fa;
        }

        .test-log .log-warn {
            color: #fbbf24;
        }

        .btn-test {
            background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
            color: #fff;
            border: none;
            padding: 10px 24px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-test:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
            color: #fff;
        }

        .btn-test:disabled {
            opacity: 0.5;
            transform: none;
            cursor: not-allowed;
        }

        .inline-chat-test {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
            margin-top: 12px;
        }

        .inline-chat-messages {
            min-height: 200px;
            max-height: 300px;
            overflow-y: auto;
            padding: 16px;
            background: #f8fafc;
        }

        .inline-chat-input {
            display: flex;
            gap: 8px;
            padding: 12px;
            background: #fff;
            border-top: 1px solid #e2e8f0;
        }

        .inline-chat-input input {
            flex: 1;
            border: 1px solid #e2e8f0;
            border-radius: 24px;
            padding: 10px 16px;
            font-size: 0.88rem;
            outline: none;
        }

        .inline-chat-input input:focus {
            border-color: #2563eb;
        }

        .inline-chat-input button {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: linear-gradient(135deg, #2563eb, #7c3aed);
            color: #fff;
            border: none;
            cursor: pointer;
        }

        .test-msg {
            margin-bottom: 10px;
            display: flex;
            gap: 8px;
        }

        .test-msg.user-msg {
            flex-direction: row-reverse;
        }

        .test-msg .msg-bubble {
            max-width: 80%;
            padding: 10px 14px;
            border-radius: 14px;
            font-size: 0.88rem;
            line-height: 1.5;
        }

        .test-msg.bot-msg .msg-bubble {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-top-left-radius: 4px;
        }

        .test-msg.user-msg .msg-bubble {
            background: linear-gradient(135deg, #2563eb, #7c3aed);
            color: #fff;
            border-top-right-radius: 4px;
        }

        .test-msg .msg-avatar {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            flex-shrink: 0;
        }

        .bot-msg .msg-avatar {
            background: linear-gradient(135deg, #2563eb, #7c3aed);
            color: #fff;
        }

        .user-msg .msg-avatar {
            background: #e2e8f0;
            color: #475569;
        }

        .spinner-sm {
            width: 16px;
            height: 16px;
            border: 2px solid #e2e8f0;
            border-top: 2px solid #2563eb;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            display: inline-block;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>

<body>
    <div class="test-header">
        <div class="container">
            <h1><i class="fas fa-flask"></i> Test Gemini AI Chatbox</h1>
            <p>Trang kiểm tra giao diện và backend của chatbox Gemini AI</p>
        </div>
    </div>

    <div class="container pb-5">
        <div class="row">
            {{-- Cột trái: Backend Tests --}}
            <div class="col-lg-6">
                <div class="test-card">
                    <h5><i class="fas fa-server text-primary"></i> Backend Tests</h5>

                    <div class="test-item">
                        <span class="test-item-label">GEMINI_API_KEY trong .env</span>
                        <span id="test-env" class="status-badge status-pending">
                            <i class="fas fa-hourglass-half"></i> Chưa kiểm tra
                        </span>
                    </div>

                    <div class="test-item">
                        <span class="test-item-label">Config services.gemini</span>
                        <span id="test-config" class="status-badge status-pending">
                            <i class="fas fa-hourglass-half"></i> Chưa kiểm tra
                        </span>
                    </div>

                    <div class="test-item">
                        <span class="test-item-label">Route gemini.chat.send</span>
                        <span id="test-route-send" class="status-badge status-pending">
                            <i class="fas fa-hourglass-half"></i> Chưa kiểm tra
                        </span>
                    </div>

                    <div class="test-item">
                        <span class="test-item-label">Route gemini.chat.clear</span>
                        <span id="test-route-clear" class="status-badge status-pending">
                            <i class="fas fa-hourglass-half"></i> Chưa kiểm tra
                        </span>
                    </div>

                    <div class="test-item">
                        <span class="test-item-label">Gọi Gemini API thực tế</span>
                        <span id="test-api" class="status-badge status-pending">
                            <i class="fas fa-hourglass-half"></i> Chưa kiểm tra
                        </span>
                    </div>

                    <div class="mt-3 d-flex gap-2">
                        <button class="btn-test" id="btn-run-all" onclick="runAllTests()">
                            <i class="fas fa-play"></i> Chạy tất cả test
                        </button>
                        <button class="btn btn-outline-secondary" onclick="clearLog()">
                            <i class="fas fa-eraser"></i> Xóa log
                        </button>
                    </div>

                    <div class="test-log" id="test-log">
                        <div class="log-info">[INFO] Sẵn sàng chạy test. Nhấn "Chạy tất cả test" để bắt đầu.</div>
                    </div>
                </div>

                {{-- API Response Detail --}}
                <div class="test-card">
                    <h5><i class="fas fa-code text-primary"></i> Chi tiết API Response</h5>
                    <div id="api-response-detail" class="test-log" style="min-height: 100px;">
                        <div class="log-info">[INFO] Chưa có response nào...</div>
                    </div>
                </div>
            </div>

            {{-- Cột phải: UI Test --}}
            <div class="col-lg-6">
                <div class="test-card">
                    <h5><i class="fas fa-comments text-primary"></i> Giao diện Chat (Inline Test)</h5>
                    <p class="text-muted" style="font-size: 0.85rem;">
                        Test trực tiếp chatbox ngay trong trang này - không cần mở widget.
                    </p>

                    <div class="inline-chat-test">
                        <div class="inline-chat-messages" id="inline-messages">
                            <div class="test-msg bot-msg">
                                <div class="msg-avatar"><i class="fas fa-robot"></i></div>
                                <div class="msg-bubble">
                                    Xin chào! 👋 Tôi là trợ lý ảo Libhub. Hãy thử gửi tin nhắn để test!
                                </div>
                            </div>
                        </div>
                        <div class="inline-chat-input">
                            <input type="text" id="inline-input" placeholder="Nhập tin nhắn test..."
                                onkeydown="if(event.key==='Enter')sendInlineMsg()">
                            <button onclick="sendInlineMsg()" id="inline-send">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Quick Test Messages --}}
                <div class="test-card">
                    <h5><i class="fas fa-bolt text-warning"></i> Gửi nhanh tin nhắn test</h5>
                    <div class="d-flex flex-wrap gap-2">
                        <button class="btn btn-outline-primary btn-sm" onclick="quickSend('Xin chào')">
                            Xin chào
                        </button>
                        <button class="btn btn-outline-primary btn-sm" onclick="quickSend('Hướng dẫn mượn sách')">
                            Mượn sách
                        </button>
                        <button class="btn btn-outline-primary btn-sm" onclick="quickSend('Cách trả sách')">
                            Trả sách
                        </button>
                        <button class="btn btn-outline-primary btn-sm" onclick="quickSend('Chính sách thuê sách là gì?')">
                            Chính sách
                        </button>
                        <button class="btn btn-outline-primary btn-sm" onclick="quickSend('Tôi muốn tìm sách về lập trình Python')">
                            Tìm sách
                        </button>
                        <button class="btn btn-outline-danger btn-sm" onclick="testClearHistory()">
                            <i class="fas fa-trash"></i> Xóa history
                        </button>
                    </div>
                </div>

                {{-- Widget Test --}}
                <div class="test-card">
                    <h5><i class="fas fa-puzzle-piece text-success"></i> Test Widget Chatbox</h5>
                    <p class="text-muted" style="font-size:0.85rem;">
                        Widget chatbox nổi ở góc dưới bên phải. Nhấn nút tím để mở.
                    </p>
                    <span class="status-badge status-ok">
                        <i class="fas fa-check"></i> Widget đã được load
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Include chatbox widget thật --}}
    @include('components.chatbox')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        // Phát hiện base URL tự động từ trình duyệt
        function getBaseUrl() {
            const loc = window.location;
            const path = loc.pathname;
            const publicIdx = path.indexOf('/public');
            if (publicIdx !== -1) {
                return loc.origin + path.substring(0, publicIdx) + '/public';
            }
            return loc.origin;
        }
        const baseUrl = getBaseUrl();
        const sendUrl = baseUrl + '/gemini-chat/send';
        const clearUrl = baseUrl + '/gemini-chat/clear';
        const configUrl = baseUrl + '/test/chatbox/config';

        console.log('[Test] Base URL:', baseUrl);
        console.log('[Test] Send URL:', sendUrl);
        console.log('[Test] Clear URL:', clearUrl);

        // ============================================================
        // LOG UTILITIES
        // ============================================================
        function addLog(msg, type = 'info') {
            const logDiv = document.getElementById('test-log');
            const time = new Date().toLocaleTimeString('vi-VN');
            const cssClass = 'log-' + type;
            const prefix = {
                'ok': '[✓ OK]',
                'fail': '[✗ FAIL]',
                'info': '[INFO]',
                'warn': '[WARN]'
            }[type] || '[LOG]';
            logDiv.innerHTML += `<div class="${cssClass}">${time} ${prefix} ${msg}</div>`;
            logDiv.scrollTop = logDiv.scrollHeight;
        }

        function clearLog() {
            document.getElementById('test-log').innerHTML =
                '<div class="log-info">[INFO] Log đã được xóa.</div>';
        }

        function setStatus(id, ok, text) {
            const el = document.getElementById(id);
            if (ok) {
                el.className = 'status-badge status-ok';
                el.innerHTML = `<i class="fas fa-check-circle"></i> ${text}`;
            } else {
                el.className = 'status-badge status-fail';
                el.innerHTML = `<i class="fas fa-times-circle"></i> ${text}`;
            }
        }

        function setLoading(id) {
            const el = document.getElementById(id);
            el.className = 'status-badge status-pending';
            el.innerHTML = `<span class="spinner-sm"></span> Đang kiểm tra...`;
        }

        // ============================================================
        // BACKEND TESTS
        // ============================================================
        async function runAllTests() {
            const btn = document.getElementById('btn-run-all');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-sm"></span> Đang chạy...';

            addLog('Bắt đầu chạy test...', 'info');

            // Test 1: Check env config
            await testEnvConfig();

            // Test 2: Check route send
            await testRouteSend();

            // Test 3: Check route clear
            await testRouteClear();

            // Test 4: Test actual API call
            await testGeminiApi();

            addLog('Tất cả test đã hoàn thành!', 'info');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-play"></i> Chạy lại test';
        }

        async function testEnvConfig() {
            setLoading('test-env');
            setLoading('test-config');
            addLog('Kiểm tra GEMINI_API_KEY trong .env...', 'info');

            try {
                const res = await fetch(configUrl, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();

                if (data.env_exists) {
                    setStatus('test-env', true, 'Đã cấu hình');
                    addLog(`GEMINI_API_KEY tồn tại (${data.key_length} ký tự)`, 'ok');
                } else {
                    setStatus('test-env', false, 'Chưa cấu hình');
                    addLog('GEMINI_API_KEY chưa được thiết lập trong .env!', 'fail');
                }

                if (data.config_exists) {
                    setStatus('test-config', true, 'OK');
                    addLog('config/services.php > gemini.api_key: OK', 'ok');
                } else {
                    setStatus('test-config', false, 'Lỗi');
                    addLog('config/services.php thiếu cấu hình gemini!', 'fail');
                }
            } catch (e) {
                setStatus('test-env', false, 'Lỗi kiểm tra');
                setStatus('test-config', false, 'Lỗi kiểm tra');
                addLog('Không thể kiểm tra config: ' + e.message, 'fail');
            }
        }

        async function testRouteSend() {
            setLoading('test-route-send');
            addLog('Kiểm tra route POST /gemini-chat/send...', 'info');

            try {
                // Gửi request thiếu message để test route tồn tại (expect validation error, 422)
                const res = await fetch(sendUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({}),
                });

                if (res.status === 422) {
                    // Validation error = route hoạt động
                    setStatus('test-route-send', true, 'Hoạt động');
                    addLog('Route gemini.chat.send: OK (422 validation = route tồn tại)', 'ok');
                } else if (res.status === 200) {
                    setStatus('test-route-send', true, 'Hoạt động');
                    addLog('Route gemini.chat.send: OK (200)', 'ok');
                } else {
                    setStatus('test-route-send', false, `HTTP ${res.status}`);
                    addLog(`Route gemini.chat.send: HTTP ${res.status}`, 'fail');
                }
            } catch (e) {
                setStatus('test-route-send', false, 'Lỗi');
                addLog('Route send không thể truy cập: ' + e.message, 'fail');
            }
        }

        async function testRouteClear() {
            setLoading('test-route-clear');
            addLog('Kiểm tra route POST /gemini-chat/clear...', 'info');

            try {
                const res = await fetch(clearUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                });

                const data = await res.json();
                if (data.success) {
                    setStatus('test-route-clear', true, 'Hoạt động');
                    addLog('Route gemini.chat.clear: OK', 'ok');
                } else {
                    setStatus('test-route-clear', false, 'Lỗi');
                    addLog('Route clear trả về success=false', 'fail');
                }
            } catch (e) {
                setStatus('test-route-clear', false, 'Lỗi');
                addLog('Route clear không thể truy cập: ' + e.message, 'fail');
            }
        }

        async function testGeminiApi() {
            setLoading('test-api');
            addLog('Gọi Gemini API với tin nhắn test...', 'info');

            const responseDiv = document.getElementById('api-response-detail');
            responseDiv.innerHTML = '<div class="log-info">Đang gọi API...</div>';

            try {
                const startTime = Date.now();
                const res = await fetch(sendUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ message: 'Xin chào, bạn là ai?' }),
                });

                const elapsed = Date.now() - startTime;
                const data = await res.json();

                responseDiv.innerHTML = `
                    <div class="log-info">[STATUS] HTTP ${res.status}</div>
                    <div class="log-info">[TIME] ${elapsed}ms</div>
                    <div class="log-info">[RESPONSE]</div>
                    <div class="${data.success ? 'log-ok' : 'log-fail'}">${JSON.stringify(data, null, 2)}</div>
                `;

                if (data.success) {
                    setStatus('test-api', true, `OK (${elapsed}ms)`);
                    addLog(`Gemini API OK! Phản hồi trong ${elapsed}ms`, 'ok');
                    addLog(`Nội dung: "${data.message.substring(0, 80)}..."`, 'info');
                } else {
                    setStatus('test-api', false, 'Lỗi API');
                    addLog(`Gemini API lỗi: ${data.message}`, 'fail');
                }
            } catch (e) {
                setStatus('test-api', false, 'Lỗi kết nối');
                addLog('Không thể gọi Gemini API: ' + e.message, 'fail');
                responseDiv.innerHTML = `<div class="log-fail">[ERROR] ${e.message}</div>`;
            }
        }

        // ============================================================
        // INLINE CHAT TEST
        // ============================================================
        let inlineSending = false;

        function sendInlineMsg() {
            const input = document.getElementById('inline-input');
            const msg = input.value.trim();
            if (!msg || inlineSending) return;
            input.value = '';
            doInlineSend(msg);
        }

        function quickSend(msg) {
            if (inlineSending) return;
            doInlineSend(msg);
        }

        async function doInlineSend(message) {
            inlineSending = true;
            const msgDiv = document.getElementById('inline-messages');
            const sendBtn = document.getElementById('inline-send');
            sendBtn.disabled = true;

            // Show user message
            msgDiv.innerHTML += `
                <div class="test-msg user-msg">
                    <div class="msg-avatar"><i class="fas fa-user"></i></div>
                    <div class="msg-bubble">${escapeHtml(message)}</div>
                </div>
            `;

            // Show typing
            const typingId = 'typing-' + Date.now();
            msgDiv.innerHTML += `
                <div class="test-msg bot-msg" id="${typingId}">
                    <div class="msg-avatar"><i class="fas fa-robot"></i></div>
                    <div class="msg-bubble"><span class="spinner-sm"></span> Đang trả lời...</div>
                </div>
            `;
            msgDiv.scrollTop = msgDiv.scrollHeight;

            try {
                const res = await fetch(sendUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ message }),
                });

                const data = await res.json();
                const typingEl = document.getElementById(typingId);
                if (typingEl) typingEl.remove();

                let reply = data.success
                    ? formatMsg(data.message)
                    : `<span style="color:#dc2626">${escapeHtml(data.message || 'Lỗi')}</span>`;

                msgDiv.innerHTML += `
                    <div class="test-msg bot-msg">
                        <div class="msg-avatar"><i class="fas fa-robot"></i></div>
                        <div class="msg-bubble">${reply}</div>
                    </div>
                `;
            } catch (e) {
                const typingEl = document.getElementById(typingId);
                if (typingEl) typingEl.remove();

                msgDiv.innerHTML += `
                    <div class="test-msg bot-msg">
                        <div class="msg-avatar"><i class="fas fa-robot"></i></div>
                        <div class="msg-bubble" style="color:#dc2626">Lỗi kết nối: ${escapeHtml(e.message)}</div>
                    </div>
                `;
            }

            msgDiv.scrollTop = msgDiv.scrollHeight;
            inlineSending = false;
            sendBtn.disabled = false;
            document.getElementById('inline-input').focus();
        }

        async function testClearHistory() {
            try {
                await fetch(clearUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                });
                const msgDiv = document.getElementById('inline-messages');
                msgDiv.innerHTML = `
                    <div class="test-msg bot-msg">
                        <div class="msg-avatar"><i class="fas fa-robot"></i></div>
                        <div class="msg-bubble">Đã xóa lịch sử chat! Bắt đầu cuộc trò chuyện mới. 🔄</div>
                    </div>
                `;
                addLog('Đã xóa lịch sử chat thành công!', 'ok');
            } catch (e) {
                addLog('Lỗi xóa history: ' + e.message, 'fail');
            }
        }

        function formatMsg(text) {
            text = escapeHtml(text);
            text = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
            text = text.replace(/\*(.*?)\*/g, '<em>$1</em>');
            text = text.replace(/\n/g, '<br>');
            return text;
        }

        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }
    </script>
</body>

</html>
