<?php

namespace App\Http\Controllers;

use App\Models\Fine;
use App\Models\Reader;
use Illuminate\Http\Request;

class FinePaymentsController extends Controller
{
    /**
     * Danh sách các khoản phạt pending theo độc giả
     */
    public function index(Request $request)
    {
        $reader = null;
        if ($request->filled('reader_id')) {
            $reader = Reader::findOrFail($request->reader_id);
        }

        $query = Fine::with(['borrow', 'borrowItem.book', 'reader'])
            ->where('status', 'pending');

        if ($reader) {
            $query->where('reader_id', $reader->id);
        }

        $fines = $query->orderByDesc('created_at')->paginate(15);

        return view('admin.fine-payments.index', compact('fines', 'reader'));
    }
}
