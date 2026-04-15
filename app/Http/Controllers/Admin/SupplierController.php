<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryReceipt;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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

        if ($request->filled('status')) {
            $query->where('status', $request->status);
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
            'status' => 'required|in:active,inactive',
        ]);

        $data['name'] = $this->normalizeName($data['name']);

        if ($this->isDuplicateName($data['name'])) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['name' => 'Tên nhà cung cấp đã tồn tại. Vui lòng nhập tên khác.']);
        }

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
            'status' => 'required|in:active,inactive',
        ]);

        $data['name'] = $this->normalizeName($data['name']);

        if ($this->isDuplicateName($data['name'], $supplier->id)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['name' => 'Tên nhà cung cấp đã tồn tại. Vui lòng nhập tên khác.']);
        }

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

    public function legacyMap()
    {
        $legacyRows = InventoryReceipt::query()
            ->select('supplier', DB::raw('COUNT(*) as receipts_count'))
            ->whereNull('supplier_id')
            ->whereNotNull('supplier')
            ->whereRaw("TRIM(supplier) <> ''")
            ->groupBy('supplier')
            ->orderByDesc('receipts_count')
            ->orderBy('supplier')
            ->get();

        $suppliers = Supplier::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.suppliers.legacy-map', compact('legacyRows', 'suppliers'));
    }

    public function applyLegacyMap(Request $request)
    {
        $data = $request->validate([
            'mappings' => 'required|array',
            'mappings.*.legacy_name' => 'required|string',
            'mappings.*.supplier_id' => [
                'nullable',
                Rule::exists('suppliers', 'id')->where(function ($query) {
                    $query->where('status', 'active');
                }),
            ],
        ]);

        $mappings = collect($data['mappings'] ?? [])
            ->filter(function ($row) {
                return !empty($row['legacy_name']) && !empty($row['supplier_id']);
            })
            ->values();

        if ($mappings->isEmpty()) {
            return redirect()->back()->with('error', 'Bạn chưa chọn nhà cung cấp để map.');
        }

        $totalUpdated = 0;
        $groupUpdated = 0;

        DB::transaction(function () use ($mappings, &$totalUpdated, &$groupUpdated) {
            foreach ($mappings as $mapRow) {
                $legacyName = trim((string) $mapRow['legacy_name']);
                $supplierId = (int) $mapRow['supplier_id'];

                $updated = InventoryReceipt::query()
                    ->whereNull('supplier_id')
                    ->where('supplier', $legacyName)
                    ->update([
                        'supplier_id' => $supplierId,
                    ]);

                if ($updated > 0) {
                    $groupUpdated++;
                    $totalUpdated += $updated;
                }
            }
        });

        return redirect()->back()->with('success', "Đã map {$groupUpdated} nhóm tên cũ, cập nhật {$totalUpdated} phiếu nhập.");
    }

    public function toggleStatus($id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->status = $supplier->status === 'active' ? 'inactive' : 'active';
        $supplier->save();

        $label = $supplier->status === 'active' ? 'hoạt động' : 'ngừng hợp tác';

        return redirect()->back()->with('success', "Đã chuyển trạng thái nhà cung cấp sang {$label}.");
    }

    private function normalizeName(string $name): string
    {
        return preg_replace('/\s+/u', ' ', trim($name)) ?? trim($name);
    }

    private function isDuplicateName(string $name, ?int $ignoreId = null): bool
    {
        $query = Supplier::query()
            ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($name, 'UTF-8')]);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }
}
