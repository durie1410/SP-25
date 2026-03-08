<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class WalletController extends Controller
{
    /**
     * Hiển thị trang ví của khách hàng
     */
    public function index()
    {
        $user = auth()->user();
        $user->load('reader');
        
        // Chỉ lấy ví hiện có, không tự tạo mới để tôn trọng thao tác xóa ví của người dùng
        $wallet = Wallet::where('user_id', $user->id)->first();

        if ($wallet) {
            $wallet->refresh();

            // Lấy các giao dịch gần đây (10 giao dịch)
            $transactions = $wallet->transactions()
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        } else {
            $transactions = new LengthAwarePaginator([], 0, 10, 1, [
                'path' => request()->url(),
                'query' => request()->query(),
            ]);
        }
        
        return view('account.wallet', compact('wallet', 'transactions'));
    }

    /**
     * Hiển thị lịch sử giao dịch đầy đủ
     */
    public function transactions(Request $request)
    {
        $user = auth()->user();
        $user->load('reader');
        
        $wallet = Wallet::where('user_id', $user->id)->first();

        if (!$wallet) {
            return redirect()->route('account.wallet')->with('info', 'Ví của bạn đã được xóa hoặc chưa được tạo.');
        }
        
        // Refresh để đảm bảo lấy số dư mới nhất từ database
        $wallet->refresh();
        
        $query = $wallet->transactions()->orderBy('created_at', 'desc');
        
        // Lọc theo loại giao dịch
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }
        
        // Lọc theo ngày
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $transactions = $query->paginate(20);
        
        return view('account.wallet-transactions', compact('wallet', 'transactions'));
    }

    /**
     * Xóa ví của người dùng hiện tại
     */
    public function destroy(Request $request)
    {
        $userId = auth()->id();

        $wallet = Wallet::where('user_id', $userId)->first();

        if (!$wallet) {
            return redirect()->route('account.wallet')->with('info', 'Ví của bạn không tồn tại hoặc đã được xóa trước đó.');
        }

        DB::transaction(function () use ($wallet) {
            $wallet->delete();
        });

        return redirect()->route('account.wallet')->with('success', 'Đã xóa ví của bạn thành công.');
    }
}


