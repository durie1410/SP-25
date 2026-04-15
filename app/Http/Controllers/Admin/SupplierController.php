<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::query()->withCount('receipts');

        if ($request->filled('keyword')) {
            $keyword = trim((string) $request->keyword);
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('phone', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%")
                    ->orWhere('address', 'like', "%{$keyword}%");
            });
        }

        $suppliers = $query->orderByDesc('created_at')->paginate(15);

        return view('admin.suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('admin.suppliers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255|unique:suppliers,email',
            'address' => 'nullable|string|max:500',
        ]);

        Supplier::create($data);

        return redirect()->route('admin.suppliers.index')
            ->with('success', 'Thêm nhà cung cấp thành công.');
    }

    public function show($id)
    {
        $supplier = Supplier::withCount('receipts')->findOrFail($id);

        return view('admin.suppliers.show', compact('supplier'));
    }

    public function edit($id)
    {
        $supplier = Supplier::findOrFail($id);

        return view('admin.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255|unique:suppliers,email,' . $supplier->id,
            'address' => 'nullable|string|max:500',
        ]);

        $supplier->update($data);

        return redirect()->route('admin.suppliers.index')
            ->with('success', 'Cập nhật nhà cung cấp thành công.');
    }

    public function destroy($id)
    {
        $supplier = Supplier::withCount('receipts')->findOrFail($id);

        if ($supplier->receipts_count > 0) {
            return redirect()->back()->with('error', 'Không thể xóa vì nhà cung cấp này đã có phiếu nhập.');
        }

        $supplier->delete();

        return redirect()->route('admin.suppliers.index')
            ->with('success', 'Xóa nhà cung cấp thành công.');
    }
}
