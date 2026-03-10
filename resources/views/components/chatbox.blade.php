{{-- Gemini AI Chatbox Widget --}}
<div id="gemini-chatbox">
    {{-- Nút mở chatbox --}}
    <button id="chatbox-toggle" class="chatbox-toggle-btn" title="Trợ lý ảo Libhub">
        <i class="fas fa-comments" id="chatbox-icon-open"></i>
        <i class="fas fa-times" id="chatbox-icon-close" style="display:none;"></i>
    </button>

    {{-- Giao diện chatbox --}}
    <div id="chatbox-container" class="chatbox-container" style="display:none;">
        <div class="chatbox-header">
            <div class="chatbox-header-info">
                <div class="chatbox-avatar">
                    <i class="fas fa-robot"></i>
                </div>
                <div>
                    <div class="chatbox-title">Trợ lý Libhub</div>
                    <div class="chatbox-status"><span class="status-dot"></span> Trực tuyến</div>
                </div>
            </div>
            <div class="chatbox-header-actions">
                <button id="chatbox-clear" title="Xóa lịch sử chat">
                    <i class="fas fa-trash-alt"></i>
                </button>
                <button id="chatbox-minimize" title="Thu nhỏ">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>

        <div id="chatbox-messages" class="chatbox-messages">
            <div class="chat-message bot-message">
                <div class="message-avatar"><i class="fas fa-robot"></i></div>
                <div class="message-content">
                    <p>Xin chào! 👋 Tôi là trợ lý ảo của <strong>Libhub</strong>. Tôi có thể giúp bạn:</p>
                    <ul>
                        <li>🔍 Tìm kiếm sách</li>
                        <li>📚 Hướng dẫn mượn/trả sách</li>
                        <li>💳 Thông tin thanh toán</li>
                        <li>❓ Giải đáp thắc mắc</li>
                    </ul>
                    <p>Hãy hỏi tôi bất cứ điều gì!</p>
                </div>
            </div>
        </div>

        <div id="chatbox-typing" class="chat-message bot-message typing-indicator" style="display:none;">
            <div class="message-avatar"><i class="fas fa-robot"></i></div>
            <div class="message-content">
                <div class="typing-dots">
                    <span></span><span></span><span></span>
                </div>
            </div>
        </div>

        <div class="chatbox-quick-actions" id="chatbox-quick-actions">
            <button class="quick-action-btn" data-message="Hướng dẫn mượn sách">📚 Mượn sách</button>
            <button class="quick-action-btn" data-message="Cách trả sách">🔄 Trả sách</button>
            <button class="quick-action-btn" data-message="Chính sách thuê sách">📋 Chính sách</button>
            <button class="quick-action-btn" data-message="Hướng dẫn thanh toán">💳 Thanh toán</button>
        </div>

        <form id="chatbox-form" class="chatbox-input-area">
            @csrf
            <input type="text" id="chatbox-input" placeholder="Nhập tin nhắn..." autocomplete="off" maxlength="2000">
            <button type="submit" id="chatbox-send" title="Gửi">
                <i class="fas fa-paper-plane"></i>
            </button>
        </form>
    </div>
</div>

<style>
    /* ===== Chatbox Toggle Button ===== */
    .chatbox-toggle-btn {
        position: fixed;
        bottom: 24px;
        right: 24px;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
        color: #fff;
        border: none;
        cursor: pointer;
        box-shadow: 0 4px 20px rgba(37, 99, 235, 0.4);
        z-index: 10001;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        transition: all 0.3s ease;
    }

    .chatbox-toggle-btn:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 25px rgba(37, 99, 235, 0.5);
    }

    /* ===== Chatbox Container ===== */
    .chatbox-container {
        position: fixed;
        bottom: 96px;
        right: 24px;
        width: 380px;
        max-height: 560px;
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        z-index: 10000;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        animation: chatboxSlideUp 0.3s ease-out;
    }

    @keyframes chatboxSlideUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* ===== Header ===== */
    .chatbox-header {
        background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
        color: #fff;
        padding: 14px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-shrink: 0;
    }

    .chatbox-header-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .chatbox-avatar {
        width: 38px;
        height: 38px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
    }

    .chatbox-title {
        font-weight: 600;
        font-size: 0.95rem;
    }

    .chatbox-status {
        font-size: 0.75rem;
        opacity: 0.9;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .status-dot {
        width: 8px;
        height: 8px;
        background: #34d399;
        border-radius: 50%;
        display: inline-block;
    }

    .chatbox-header-actions {
        display: flex;
        gap: 6px;
    }

    .chatbox-header-actions button {
        background: rgba(255, 255, 255, 0.15);
        border: none;
        color: #fff;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
        transition: background 0.2s;
    }

    .chatbox-header-actions button:hover {
        background: rgba(255, 255, 255, 0.3);
    }

    /* ===== Messages Area ===== */
    .chatbox-messages {
        flex: 1;
        overflow-y: auto;
        padding: 16px;
        min-height: 280px;
        max-height: 340px;
        display: flex;
        flex-direction: column;
        gap: 12px;
        background: #f8fafc;
    }

    .chatbox-messages::-webkit-scrollbar {
        width: 4px;
    }

    .chatbox-messages::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }

    /* ===== Chat Messages ===== */
    .chat-message {
        display: flex;
        gap: 8px;
        max-width: 90%;
        animation: messageIn 0.3s ease-out;
    }

    @keyframes messageIn {
        from {
            opacity: 0;
            transform: translateY(8px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .bot-message {
        align-self: flex-start;
    }

    .user-message {
        align-self: flex-end;
        flex-direction: row-reverse;
    }

    .message-avatar {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 0.8rem;
    }

    .bot-message .message-avatar {
        background: linear-gradient(135deg, #2563eb, #7c3aed);
        color: #fff;
    }

    .user-message .message-avatar {
        background: #e2e8f0;
        color: #475569;
    }

    .message-content {
        padding: 10px 14px;
        border-radius: 14px;
        font-size: 0.88rem;
        line-height: 1.5;
        word-break: break-word;
    }

    .bot-message .message-content {
        background: #fff;
        color: #1e293b;
        border: 1px solid #e2e8f0;
        border-top-left-radius: 4px;
    }

    .user-message .message-content {
        background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
        color: #fff;
        border-top-right-radius: 4px;
    }

    .message-content p {
        margin: 0 0 6px 0;
    }

    .message-content p:last-child {
        margin-bottom: 0;
    }

    .message-content ul {
        margin: 4px 0;
        padding-left: 18px;
    }

    .message-content ul li {
        margin-bottom: 2px;
        font-size: 0.85rem;
    }

    /* ===== Typing Indicator ===== */
    .typing-indicator {
        margin-top: 4px;
    }

    .typing-dots {
        display: flex;
        gap: 4px;
        padding: 4px 0;
    }

    .typing-dots span {
        width: 8px;
        height: 8px;
        background: #94a3b8;
        border-radius: 50%;
        animation: typingBounce 1.4s infinite ease-in-out both;
    }

    .typing-dots span:nth-child(1) {
        animation-delay: 0s;
    }

    .typing-dots span:nth-child(2) {
        animation-delay: 0.16s;
    }

    .typing-dots span:nth-child(3) {
        animation-delay: 0.32s;
    }

    @keyframes typingBounce {
        0%, 80%, 100% {
            transform: scale(0.6);
            opacity: 0.4;
        }
        40% {
            transform: scale(1);
            opacity: 1;
        }
    }

    /* ===== Quick Actions ===== */
    .chatbox-quick-actions {
        padding: 8px 16px;
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
    }

    .quick-action-btn {
        padding: 6px 12px;
        border: 1px solid #e2e8f0;
        background: #fff;
        border-radius: 20px;
        font-size: 0.78rem;
        cursor: pointer;
        transition: all 0.2s;
        color: #475569;
        white-space: nowrap;
    }

    .quick-action-btn:hover {
        background: #2563eb;
        color: #fff;
        border-color: #2563eb;
    }

    /* ===== Input Area ===== */
    .chatbox-input-area {
        display: flex;
        padding: 12px;
        gap: 8px;
        background: #fff;
        border-top: 1px solid #e2e8f0;
        flex-shrink: 0;
    }

    #chatbox-input {
        flex: 1;
        border: 1px solid #e2e8f0;
        border-radius: 24px;
        padding: 10px 16px;
        font-size: 0.88rem;
        outline: none;
        transition: border-color 0.2s;
        font-family: inherit;
    }

    #chatbox-input:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.1);
    }

    #chatbox-send {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
        color: #fff;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
        transition: all 0.2s;
        flex-shrink: 0;
    }

    #chatbox-send:hover {
        transform: scale(1.05);
        box-shadow: 0 2px 10px rgba(37, 99, 235, 0.3);
    }

    #chatbox-send:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
    }

    /* ===== Responsive ===== */
    @media (max-width: 480px) {
        .chatbox-container {
            width: calc(100vw - 20px);
            right: 10px;
            bottom: 86px;
            max-height: 70vh;
        }

        .chatbox-toggle-btn {
            bottom: 16px;
            right: 16px;
            width: 54px;
            height: 54px;
            font-size: 1.3rem;
        }
    }

    /* ===== Error message style ===== */
    .message-error .message-content {
        background: #fef2f2 !important;
        color: #dc2626 !important;
        border-color: #fecaca !important;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Phát hiện base URL tự động từ trình duyệt (hoạt động cả Apache lẫn artisan serve)
        function getBaseUrl() {
            // Nếu có thẻ <base>, dùng nó
            const baseTag = document.querySelector('base');
            if (baseTag) return baseTag.href.replace(/\/$/, '');

            // Phát hiện từ URL hiện tại: tìm /public/ trong path
            const loc = window.location;
            const path = loc.pathname;
            const publicIdx = path.indexOf('/public');
            if (publicIdx !== -1) {
                return loc.origin + path.substring(0, publicIdx) + '/public';
            }
            // Nếu không có /public/ thì dùng origin (artisan serve)
            return loc.origin;
        }
        const baseUrl = getBaseUrl();
        const geminiSendUrl = baseUrl + '/gemini-chat/send';
        const geminiClearUrl = baseUrl + '/gemini-chat/clear';

        console.log('[Chatbox] Base URL:', baseUrl);
        console.log('[Chatbox] Send URL:', geminiSendUrl);
        console.log('[Chatbox] Clear URL:', geminiClearUrl);

        const toggleBtn = document.getElementById('chatbox-toggle');
        const container = document.getElementById('chatbox-container');
        const iconOpen = document.getElementById('chatbox-icon-open');
        const iconClose = document.getElementById('chatbox-icon-close');
        const form = document.getElementById('chatbox-form');
        const input = document.getElementById('chatbox-input');
        const messagesDiv = document.getElementById('chatbox-messages');
        const typingDiv = document.getElementById('chatbox-typing');
        const clearBtn = document.getElementById('chatbox-clear');
        const minimizeBtn = document.getElementById('chatbox-minimize');
        const quickActions = document.getElementById('chatbox-quick-actions');
        const sendBtn = document.getElementById('chatbox-send');
        let isOpen = false;
        let isSending = false;

        // Toggle chatbox
        toggleBtn.addEventListener('click', function () {
            isOpen = !isOpen;
            container.style.display = isOpen ? 'flex' : 'none';
            iconOpen.style.display = isOpen ? 'none' : 'inline';
            iconClose.style.display = isOpen ? 'inline' : 'none';
            if (isOpen) {
                input.focus();
                scrollToBottom();
            }
        });

        // Minimize
        minimizeBtn.addEventListener('click', function () {
            isOpen = false;
            container.style.display = 'none';
            iconOpen.style.display = 'inline';
            iconClose.style.display = 'none';
        });

        // Quick actions
        quickActions.addEventListener('click', function (e) {
            const btn = e.target.closest('.quick-action-btn');
            if (btn && !isSending) {
                const message = btn.getAttribute('data-message');
                input.value = message;
                sendMessage(message);
            }
        });

        // Form submit
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const message = input.value.trim();
            if (message && !isSending) {
                sendMessage(message);
            }
        });

        // Clear chat
        clearBtn.addEventListener('click', function () {
            if (confirm('Bạn muốn xóa toàn bộ lịch sử chat?')) {
                // Gọi API xóa lịch sử
                fetch(geminiClearUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                }).catch(() => {});

                // Reset giao diện
                messagesDiv.innerHTML = `
                    <div class="chat-message bot-message">
                        <div class="message-avatar"><i class="fas fa-robot"></i></div>
                        <div class="message-content">
                            <p>Xin chào! 👋 Tôi là trợ lý ảo của <strong>Libhub</strong>. Hãy hỏi tôi bất cứ điều gì về thư viện!</p>
                        </div>
                    </div>
                `;
                quickActions.style.display = 'flex';
            }
        });

        function sendMessage(message) {
            isSending = true;
            sendBtn.disabled = true;
            input.value = '';
            input.disabled = true;

            // Ẩn quick actions sau tin nhắn đầu tiên
            quickActions.style.display = 'none';

            // Hiển thị tin nhắn của người dùng
            appendMessage(message, 'user');

            // Hiển thị typing indicator
            typingDiv.style.display = 'flex';
            scrollToBottom();

            // Gọi API
            fetch(geminiSendUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ message: message }),
            })
            .then(response => response.json())
            .then(data => {
                typingDiv.style.display = 'none';
                if (data.success) {
                    appendMessage(data.message, 'bot');
                } else {
                    appendMessage(data.message || 'Đã xảy ra lỗi. Vui lòng thử lại.', 'bot', true);
                }
            })
            .catch(error => {
                typingDiv.style.display = 'none';
                appendMessage('Không thể kết nối. Vui lòng kiểm tra mạng và thử lại.', 'bot', true);
                console.error('Chatbox error:', error);
            })
            .finally(() => {
                isSending = false;
                sendBtn.disabled = false;
                input.disabled = false;
                input.focus();
                scrollToBottom();
            });
        }

        function appendMessage(text, sender, isError = false) {
            const msgDiv = document.createElement('div');
            msgDiv.className = `chat-message ${sender === 'user' ? 'user-message' : 'bot-message'}${isError ? ' message-error' : ''}`;

            const avatarIcon = sender === 'user' ? 'fa-user' : 'fa-robot';

            // Format text: convert markdown-like text to HTML
            let formattedText = formatText(text);

            msgDiv.innerHTML = `
                <div class="message-avatar"><i class="fas ${avatarIcon}"></i></div>
                <div class="message-content">${formattedText}</div>
            `;

            messagesDiv.appendChild(msgDiv);
            scrollToBottom();
        }

        function formatText(text) {
            // Convert **bold** to <strong>
            text = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
            // Convert *italic* to <em>
            text = text.replace(/\*(.*?)\*/g, '<em>$1</em>');
            // Convert newlines to <br>
            text = text.replace(/\n/g, '<br>');
            // Wrap in paragraph if no HTML tags
            if (!text.includes('<p>') && !text.includes('<ul>') && !text.includes('<ol>')) {
                text = '<p>' + text + '</p>';
            }
            return text;
        }

        function scrollToBottom() {
            setTimeout(() => {
                messagesDiv.scrollTop = messagesDiv.scrollHeight;
            }, 50);
        }

        // Enter to send, Shift+Enter for newline
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                form.dispatchEvent(new Event('submit'));
            }
        });
    });
</script>
