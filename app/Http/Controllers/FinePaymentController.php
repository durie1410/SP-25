<?php

namespace App\Http\Controllers;

use App\Models\Borrow;
use App\Models\BorrowPayment;
use App\Models\Fine;
use App\Models\Reader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FinePaymentController extends Controller
{
    /**
     * Thanh toán phạt bằng tiền mặt theo phiếu mượn
     */
    public function payCash(Borrow $borrow)
    {
        $pendingFines = $borrow->fines()->where('status', 'pending')->get();
        if ($pendingFines->isEmpty()) {
            return back()->with('error', 'Không có khoản phạt nào cần thanh toán.');
        }

        $totalAmount = $pendingFines->sum('amount');

        try {
            DB::beginTransaction();

            BorrowPayment::create([
                'borrow_id' => $borrow->id,
                'amount' => $totalAmount,
                'payment_type' => 'damage_fee',
                'payment_method' => 'cash',
                'payment_status' => 'success',
                'transaction_code' => 'CASH-' . time(),
                'note' => 'Thanh toán phạt tại quầy (Tiền mặt)',
            ]);

            Fine::whereIn('id', $pendingFines->pluck('id'))->update([
                'status' => 'paid',
                'paid_date' => now(),
            ]);

            DB::commit();
            return back()->with('success', 'Đã xác nhận thanh toán phạt bằng tiền mặt thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error payCash fine: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Thanh toán tất cả khoản phạt của độc giả bằng tiền mặt
     */
    public function payCashByReader(Reader $reader)
    {
        $pendingFines = Fine::where('reader_id', $reader->id)->where('status', 'pending')->get();
        if ($pendingFines->isEmpty()) {
            return back()->with('error', 'Không có khoản phạt nào cần thanh toán.');
        }

        try {
            DB::beginTransaction();

            $finesByBorrow = $pendingFines->groupBy('borrow_id');
            foreach ($finesByBorrow as $borrowId => $borrowFines) {
                BorrowPayment::create([
                    'borrow_id' => $borrowId,
                    'amount' => $borrowFines->sum('amount'),
                    'payment_type' => 'damage_fee',
                    'payment_method' => 'cash',
                    'payment_status' => 'success',
                    'transaction_code' => 'CASH-' . $reader->id . '-' . time(),
                    'note' => 'Thanh toán phạt tại quầy (Tiền mặt) cho độc giả: ' . $reader->ho_ten,
                ]);
            }

            Fine::whereIn('id', $pendingFines->pluck('id'))->update([
                'status' => 'paid',
                'paid_date' => now(),
            ]);

            DB::commit();
            return back()->with('success', 'Đã thanh toán tất cả khoản phạt của độc giả bằng tiền mặt.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error payCashByReader: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Tạo link thanh toán MoMo cho tiền phạt theo phiếu mượn
     */
    public function createMomoPayment(Borrow $borrow)
    {
        $pendingFines = $borrow->fines()->where('status', 'pending')->get();
        if ($pendingFines->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Không có khoản phạt nào cần thanh toán.']);
        }

        $amount = (int) $pendingFines->sum('amount');

        $endpoint    = config('services.momo.endpoint');
        $partnerCode = config('services.momo.partner_code');
        $accessKey   = config('services.momo.access_key');
        $secretKey   = config('services.momo.secret_key');

        $redirectUrl = route('admin.borrows.fine-momo.return');
        $ipnUrl      = route('admin.borrows.fine-momo.ipn');

        $orderId   = 'FINE_' . $borrow->id . '_' . time();
        $requestId = (string) time();
        $orderInfo = 'Thanh_toan_phat_phieu_muon_' . $borrow->id;
        $extraData = base64_encode(json_encode(['borrow_id' => $borrow->id, 'type' => 'fine_borrow']));

        $rawHash = "accessKey=$accessKey&amount=$amount&extraData=$extraData&ipnUrl=$ipnUrl&orderId=$orderId&orderInfo=$orderInfo&partnerCode=$partnerCode&redirectUrl=$redirectUrl&requestId=$requestId&requestType=captureWallet";
        $signature = hash_hmac('sha256', $rawHash, $secretKey);

        try {
            $response = Http::post($endpoint, [
                'partnerCode' => $partnerCode,
                'accessKey'   => $accessKey,
                'requestId'   => $requestId,
                'amount'      => (string) $amount,
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

            if (isset($result['payUrl'])) {
                BorrowPayment::create([
                    'borrow_id' => $borrow->id,
                    'amount' => $amount,
                    'payment_type' => 'damage_fee',
                    'payment_method' => 'momo',
                    'payment_status' => 'pending',
                    'transaction_code' => $orderId,
                    'note' => 'Thanh toán phạt qua MoMo (Đang chờ)',
                ]);

                return response()->json(['success' => true, 'payUrl' => $result['payUrl']]);
            }

            return response()->json(['success' => false, 'message' => 'MoMo Error: ' . ($result['message'] ?? 'Unknown error')]);
        } catch (\Exception $e) {
            Log::error('Momo Fine Payment Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Lỗi kết nối MoMo.']);
        }
    }

    /**
     * Tạo link thanh toán MoMo cho tất cả tiền phạt của độc giả
     */
    public function createMomoPaymentByReader(Reader $reader)
    {
        $pendingFines = Fine::where('reader_id', $reader->id)->where('status', 'pending')->get();
        if ($pendingFines->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Không có khoản phạt nào cần thanh toán.']);
        }

        $amount = (int) $pendingFines->sum('amount');

        $endpoint    = config('services.momo.endpoint');
        $partnerCode = config('services.momo.partner_code');
        $accessKey   = config('services.momo.access_key');
        $secretKey   = config('services.momo.secret_key');

        $redirectUrl = route('admin.borrows.fine-momo.return');
        $ipnUrl      = route('admin.borrows.fine-momo.ipn');

        $orderId   = 'FINE_READER_' . $reader->id . '_' . time();
        $requestId = (string) time();
        $orderInfo = 'Thanh_toan_phat_doc_gia_' . str_replace(' ', '_', $reader->ho_ten);
        $extraData = base64_encode(json_encode(['reader_id' => $reader->id, 'type' => 'fine_reader']));

        $rawHash = "accessKey=$accessKey&amount=$amount&extraData=$extraData&ipnUrl=$ipnUrl&orderId=$orderId&orderInfo=$orderInfo&partnerCode=$partnerCode&redirectUrl=$redirectUrl&requestId=$requestId&requestType=captureWallet";
        $signature = hash_hmac('sha256', $rawHash, $secretKey);

        try {
            $response = Http::post($endpoint, [
                'partnerCode' => $partnerCode,
                'accessKey'   => $accessKey,
                'requestId'   => $requestId,
                'amount'      => (string) $amount,
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

            if (isset($result['payUrl'])) {
                // tạo payment pending theo từng phiếu mượn (để theo dõi trong DB hiện tại)
                $finesByBorrow = $pendingFines->groupBy('borrow_id');
                foreach ($finesByBorrow as $borrowId => $borrowFines) {
                    BorrowPayment::create([
                        'borrow_id' => $borrowId,
                        'amount' => $borrowFines->sum('amount'),
                        'payment_type' => 'damage_fee',
                        'payment_method' => 'momo',
                        'payment_status' => 'pending',
                        'transaction_code' => $orderId,
                        'note' => 'Thanh toán phạt MoMo (Độc giả)',
                    ]);
                }

                return response()->json(['success' => true, 'payUrl' => $result['payUrl']]);
            }

            return response()->json(['success' => false, 'message' => 'MoMo Error: ' . ($result['message'] ?? 'Unknown error')]);
        } catch (\Exception $e) {
            Log::error('Momo Reader Fine Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Lỗi kết nối MoMo.']);
        }
    }

    /**
     * MoMo Return (redirect về site)
     */
    public function momoReturn(Request $request)
    {
        $resultCode = $request->resultCode;
        $orderId = $request->orderId;
        $extraData = json_decode(base64_decode($request->extraData ?? ''), true) ?: [];

        if (($extraData['type'] ?? null) === 'fine_reader') {
            $readerId = $extraData['reader_id'] ?? null;
            if ($resultCode == 0) {
                return redirect()->route('admin.fine-payments.index', ['reader_id' => $readerId])
                    ->with('success', 'Thanh toán MoMo thành công. Đang cập nhật trạng thái...');
            }
            return redirect()->route('admin.fine-payments.index', ['reader_id' => $readerId])
                ->with('error', 'Thanh toán MoMo thất bại hoặc đã bị hủy.');
        }

        // fallback theo borrow
        $borrowId = explode('_', $orderId)[1] ?? null;
        if ($resultCode == 0) {
            return redirect()->route('admin.borrows.show', $borrowId)
                ->with('success', 'Thanh toán MoMo thành công. Đang cập nhật trạng thái...');
        }
        return redirect()->route('admin.borrows.show', $borrowId)
            ->with('error', 'Thanh toán MoMo thất bại hoặc đã bị hủy.');
    }

    /**
     * MoMo IPN (server-to-server)
     */
    public function momoIpn(Request $request)
    {
        Log::info('MOMO FINE IPN received', $request->all());

        $orderId = $request->orderId;
        $resultCode = $request->resultCode;
        $extraData = json_decode(base64_decode($request->extraData ?? ''), true) ?: [];

        if ((int) $resultCode !== 0) {
            return response()->json(['message' => 'Received but failed'], 200);
        }

        DB::beginTransaction();
        try {
            if (($extraData['type'] ?? null) === 'fine_reader') {
                $readerId = $extraData['reader_id'] ?? null;
                if (!$readerId) {
                    DB::rollBack();
                    return response()->json(['message' => 'Missing reader_id'], 200);
                }

                // Update tất cả BorrowPayment cùng transaction_code
                BorrowPayment::where('transaction_code', $orderId)
                    ->update([
                        'payment_status' => 'success',
                        'note' => 'Thanh toán phạt qua MoMo thành công (Độc giả)',
                    ]);

                Fine::where('reader_id', $readerId)
                    ->where('status', 'pending')
                    ->update([
                        'status' => 'paid',
                        'paid_date' => now(),
                    ]);

                DB::commit();
                return response()->json(['message' => 'Success']);
            }

            // fine theo borrow
            $borrowId = $extraData['borrow_id'] ?? null;
            if ($borrowId) {
                BorrowPayment::where('borrow_id', $borrowId)
                    ->where('transaction_code', $orderId)
                    ->update([
                        'payment_status' => 'success',
                        'note' => 'Thanh toán phạt qua MoMo thành công',
                    ]);

                Fine::where('borrow_id', $borrowId)
                    ->where('status', 'pending')
                    ->update([
                        'status' => 'paid',
                        'paid_date' => now(),
                    ]);

                DB::commit();
                return response()->json(['message' => 'Success']);
            }

            DB::rollBack();
            return response()->json(['message' => 'Received but not processed'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('MOMO IPN Error: ' . $e->getMessage());
            return response()->json(['message' => 'Error'], 200);
        }
    }
}
