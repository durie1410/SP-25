@extends('account._layout')

@section('title', 'Ví của tôi')
@section('breadcrumb', 'Ví của tôi')

@section('content')
<div class="account-section">
    <div class="wallet-header">
        <h2 class="section-title">Ví của tôi</h2>
        <div class="wallet-balance-card">
            <div class="wallet-icon">💰</div>
            <div class="wallet-info">
                <p class="wallet-label">Số dư hiện tại</p>
                <p class="wallet-amount">{{ number_format($wallet->balance ?? 0, 0, ',', '.') }} VNĐ</p>
            </div>
        </div>
    </div>

    <div class="wallet-actions">
        @if($wallet)
            <a href="{{ route('account.wallet.transactions') }}" class="btn-secondary">Xem lịch sử giao dịch</a>
            <form action="{{ route('account.wallet.destroy') }}" method="POST" class="wallet-delete-form" onsubmit="return confirm('Bạn có chắc muốn xóa ví? Toàn bộ lịch sử giao dịch trong ví sẽ bị xóa.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-danger">Xóa ví</button>
            </form>
        @endif
    </div>

    <div class="wallet-transactions-section">
        <h3 class="subsection-title">Giao dịch gần đây</h3>
        
        @if(!$wallet)
            <div class="empty-state">
                <div class="empty-icon">🗑️</div>
                <h3>Ví đã được xóa</h3>
                <p>Bạn chưa có ví hoạt động. Khi phát sinh giao dịch hoàn tiền hoặc thanh toán, hệ thống có thể tạo lại ví mới.</p>
            </div>
        @elseif($transactions->count() > 0)
            <div class="transactions-list">
                @foreach($transactions as $transaction)
                    <div class="transaction-item">
                        <div class="transaction-icon">
                            @if($transaction->type === 'refund')
                                💸
                            @elseif($transaction->type === 'deposit')
                                💰
                            @elseif($transaction->type === 'withdraw')
                                💳
                            @elseif($transaction->type === 'payment')
                                🛒
                            @else
                                📝
                            @endif
                        </div>
                        <div class="transaction-details">
                            <div class="transaction-header">
                                <h4 class="transaction-title">
                                    @if($transaction->type === 'refund')
                                        Hoàn tiền cọc
                                    @elseif($transaction->type === 'deposit')
                                        Nạp tiền vào ví
                                    @elseif($transaction->type === 'withdraw')
                                        Rút tiền
                                    @elseif($transaction->type === 'payment')
                                        Thanh toán từ ví
                                    @else
                                        Giao dịch khác
                                    @endif
                                </h4>
                                <span class="transaction-amount {{ $transaction->isCredit() ? 'credit' : 'debit' }}">
                                    {{ $transaction->isCredit() ? '+' : '-' }}{{ number_format((float) ($transaction->amount ?? 0), 0, ',', '.') }} VNĐ
                                </span>
                            </div>
                            @if($transaction->description)
                                <p class="transaction-description">{{ $transaction->description }}</p>
                            @endif
                            <p class="transaction-date">{{ $transaction->created_at->format('d/m/Y H:i') }}</p>
                            <p class="transaction-balance">Số dư sau: {{ number_format((float) ($transaction->balance_after ?? 0), 0, ',', '.') }} VNĐ</p>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($transactions->hasPages())
                <div class="pagination-wrapper">
                    {{ $transactions->links() }}
                </div>
            @endif
        @else
            <div class="empty-state">
                <div class="empty-icon">💼</div>
                <h3>Chưa có giao dịch</h3>
                <p>Bạn chưa có giao dịch nào trong ví. Tiền hoàn cọc sẽ được chuyển vào ví khi đơn mượn hoàn tất.</p>
            </div>
        @endif
    </div>
</div>

<style>
.wallet-header {
    margin-bottom: 30px;
}

.wallet-balance-card {
    display: flex;
    align-items: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    margin-top: 20px;
}

.wallet-icon {
    font-size: 48px;
    margin-right: 20px;
}

.wallet-info {
    flex: 1;
}

.wallet-label {
    color: rgba(255, 255, 255, 0.9);
    font-size: 14px;
    margin-bottom: 8px;
}

.wallet-amount {
    color: #fff;
    font-size: 32px;
    font-weight: 700;
    margin: 0;
}

.wallet-actions {
    margin-bottom: 30px;
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.wallet-delete-form {
    margin: 0;
}

.btn-secondary {
    display: inline-block;
    padding: 12px 24px;
    background-color: #6c757d;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    transition: background-color 0.3s;
}

.btn-secondary:hover {
    background-color: #5a6268;
}

.btn-danger {
    display: inline-block;
    padding: 12px 24px;
    background-color: #dc3545;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: background-color 0.3s;
}

.btn-danger:hover {
    background-color: #b52a37;
}

.subsection-title {
    font-size: 20px;
    font-weight: 600;
    margin-bottom: 20px;
    color: #333;
}

.transactions-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.transaction-item {
    display: flex;
    align-items: flex-start;
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    transition: box-shadow 0.3s;
}

.transaction-item:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.transaction-icon {
    font-size: 32px;
    margin-right: 15px;
}

.transaction-details {
    flex: 1;
}

.transaction-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.transaction-title {
    font-size: 16px;
    font-weight: 600;
    color: #333;
    margin: 0;
}

.transaction-amount {
    font-size: 18px;
    font-weight: 700;
}

.transaction-amount.credit {
    color: #28a745;
}

.transaction-amount.debit {
    color: #dc3545;
}

.transaction-description {
    color: #666;
    font-size: 14px;
    margin: 5px 0;
}

.transaction-date {
    color: #999;
    font-size: 12px;
    margin: 5px 0;
}

.transaction-balance {
    color: #666;
    font-size: 13px;
    margin: 5px 0 0 0;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: #f8f9fa;
    border-radius: 8px;
}

.empty-icon {
    font-size: 64px;
    margin-bottom: 20px;
}

.empty-state h3 {
    color: #333;
    margin-bottom: 10px;
}

.empty-state p {
    color: #666;
    max-width: 500px;
    margin: 0 auto;
}

.pagination-wrapper {
    margin-top: 30px;
    display: flex;
    justify-content: center;
}
</style>
@endsection



