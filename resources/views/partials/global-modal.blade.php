<style>
    /* Modern Toast Notifications */
    .toast-container-modern {
        position: fixed;
        top: 90px;
        right: 22px;
        z-index: 9999;
        display: flex;
        flex-direction: column;
        gap: 12px;
        width: 360px;
        max-width: calc(100vw - 24px);
        pointer-events: none;
    }

    .toast-modern {
        pointer-events: auto;
        background: rgba(255, 255, 255, 0.92);
        border: 1px solid rgba(226, 232, 240, 0.9);
        border-radius: 14px;
        box-shadow: 0 18px 50px rgba(15, 23, 42, 0.12);
        overflow: hidden;
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        transform: translateY(-6px);
        opacity: 0;
        animation: toastIn 240ms cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
    }

    @keyframes toastIn {
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .toast-modern.out {
        animation: toastOut 180ms cubic-bezier(0.4, 0, 1, 1) forwards;
    }

    @keyframes toastOut {
        to {
            transform: translateY(-8px);
            opacity: 0;
        }
    }

    .toast-modern-inner {
        display: flex;
        gap: 12px;
        padding: 14px 14px 12px;
    }

    .toast-modern-icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 40px;
        color: #fff;
    }

    .toast-modern-title {
        font-weight: 700;
        color: #0f172a;
        font-size: 14px;
        line-height: 1.2;
        margin: 0;
    }

    .toast-modern-message {
        margin: 4px 0 0;
        color: #475569;
        font-size: 13px;
        line-height: 1.45;
    }

    .toast-modern-message ul {
        padding-left: 18px;
        margin: 6px 0 0;
    }

    .toast-modern-message li {
        margin: 2px 0;
    }

    .toast-modern-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-left: auto;
    }

    .toast-modern-close {
        border: none;
        background: transparent;
        color: #64748b;
        width: 32px;
        height: 32px;
        border-radius: 10px;
        cursor: pointer;
        transition: background 150ms ease, color 150ms ease;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
    }

    .toast-modern-close:hover {
        background: rgba(15, 23, 42, 0.06);
        color: #0f172a;
    }

    .toast-modern-bar {
        height: 3px;
        background: rgba(15, 23, 42, 0.06);
    }

    .toast-modern-bar > div {
        height: 100%;
        width: 100%;
        transform-origin: left;
        transform: scaleX(1);
        animation: toastProgress linear forwards;
    }

    @keyframes toastProgress {
        to {
            transform: scaleX(0);
        }
    }

    /* Types */
    .toast-success .toast-modern-icon { background: linear-gradient(135deg, #10b981, #059669); }
    .toast-success .toast-modern-bar > div { background: linear-gradient(90deg, #10b981, #059669); }

    .toast-error .toast-modern-icon { background: linear-gradient(135deg, #ef4444, #dc2626); }
    .toast-error .toast-modern-bar > div { background: linear-gradient(90deg, #ef4444, #dc2626); }

    .toast-warning .toast-modern-icon { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .toast-warning .toast-modern-bar > div { background: linear-gradient(90deg, #f59e0b, #d97706); }

    .toast-info .toast-modern-icon { background: linear-gradient(135deg, #3b82f6, #2563eb); }
    .toast-info .toast-modern-bar > div { background: linear-gradient(90deg, #3b82f6, #2563eb); }
</style>

<div class="toast-container-modern" id="toastContainerModern" aria-live="polite" aria-relevant="additions"></div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const container = document.getElementById('toastContainerModern');

        function escapeHtml(str) {
            if (str === null || str === undefined) return '';
            return String(str)
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function iconFor(type) {
            switch (type) {
                case 'success':
                    return '<i class="fas fa-check" style="font-size: 18px;"></i>';
                case 'error':
                    return '<i class="fas fa-times" style="font-size: 18px;"></i>';
                case 'warning':
                    return '<i class="fas fa-exclamation" style="font-size: 18px;"></i>';
                default:
                    return '<i class="fas fa-info" style="font-size: 18px;"></i>';
            }
        }

        window.showToast = function (title, messageHtml, type = 'info', options = {}) {
            const duration = typeof options.duration === 'number' ? options.duration : 4200;

            const toast = document.createElement('div');
            toast.className = `toast-modern toast-${type}`;

            const safeTitle = escapeHtml(title);

            toast.innerHTML = `
                <div class="toast-modern-inner">
                    <div class="toast-modern-icon">${iconFor(type)}</div>
                    <div style="min-width: 0; flex: 1;">
                        <p class="toast-modern-title">${safeTitle}</p>
                        <div class="toast-modern-message">${messageHtml}</div>
                    </div>
                    <div class="toast-modern-actions">
                        <button type="button" class="toast-modern-close" aria-label="Close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="toast-modern-bar"><div style="animation-duration: ${duration}ms"></div></div>
            `;

            const closeBtn = toast.querySelector('.toast-modern-close');

            const removeToast = () => {
                toast.classList.add('out');
                setTimeout(() => {
                    toast.remove();
                }, 200);
            };

            closeBtn.addEventListener('click', removeToast);

            // Auto close
            const timer = setTimeout(removeToast, duration);

            // Pause on hover
            toast.addEventListener('mouseenter', () => {
                clearTimeout(timer);
                const bar = toast.querySelector('.toast-modern-bar > div');
                if (bar) bar.style.animationPlayState = 'paused';
            });

            toast.addEventListener('mouseleave', () => {
                const bar = toast.querySelector('.toast-modern-bar > div');
                if (bar) bar.style.animationPlayState = 'running';
                // Restart timer with shorter remaining duration is overkill; simple approach: close in 2s after hover
                setTimeout(removeToast, 1800);
            });

            container.appendChild(toast);
        };

        // Flash messages
        @if(session('success'))
            showToast('Thành công', @json(session('success')), 'success');
        @endif

        @if(session('error'))
            showToast('Có lỗi xảy ra', @json(session('error')), 'error');
        @endif

        @if(session('warning'))
            showToast('Cảnh báo', @json(session('warning')), 'warning');
        @endif

        @if(session('info'))
            showToast('Thông báo', @json(session('info')), 'info');
        @endif

        // Validation errors
        @if($errors->any())
            let errorMsg = '<ul>';
            @foreach($errors->all() as $error)
                errorMsg += '<li>{{ $error }}</li>';
            @endforeach
            errorMsg += '</ul>';
            showToast('Vui lòng kiểm tra lại', errorMsg, 'error', { duration: 6500 });
        @endif
    });
</script>
