<?php

namespace App\Console\Commands;

use App\Models\InventoryReceipt;
use App\Models\Supplier;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncSuppliersFromReceipts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'suppliers:sync-from-receipts {--dry-run : Chi mo phong, khong ghi DB}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dong bo nha cung cap tu du lieu supplier cu trong inventory_receipts';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $this->info($dryRun
            ? 'Dang chay DRY-RUN dong bo nha cung cap...'
            : 'Dang dong bo nha cung cap...');

        $receipts = InventoryReceipt::query()
            ->select(['id', 'supplier', 'supplier_id'])
            ->whereNull('supplier_id')
            ->whereNotNull('supplier')
            ->get()
            ->filter(function ($receipt) {
                return $this->normalizeName($receipt->supplier) !== '';
            })
            ->values();

        if ($receipts->isEmpty()) {
            $this->info('Khong co phieu nhap nao can dong bo.');
            return self::SUCCESS;
        }

        $existingSuppliers = Supplier::query()->get(['id', 'name']);
        $supplierMap = [];
        foreach ($existingSuppliers as $supplier) {
            $supplierMap[$this->normalizeKey($supplier->name)] = [
                'id' => (int) $supplier->id,
                'name' => $supplier->name,
            ];
        }

        $invalidNames = ['n/a', 'na', 'none', 'null', '-'];
        $createdCount = 0;
        $linkedCount = 0;
        $skippedCount = 0;

        $groups = $receipts->groupBy(function ($receipt) {
            return $this->normalizeKey($receipt->supplier);
        });

        $applySync = function () use (&$supplierMap, $groups, $dryRun, $invalidNames, &$createdCount, &$linkedCount, &$skippedCount) {
            foreach ($groups as $key => $groupReceipts) {
                $sampleName = $this->normalizeName((string) ($groupReceipts->first()->supplier ?? ''));

                if ($this->shouldSkipSupplierName($sampleName, $key, $invalidNames)) {
                    $skippedCount += $groupReceipts->count();
                    $this->warn("Bo qua '{$sampleName}' ({$groupReceipts->count()} phieu) do khong hop le.");
                    continue;
                }

                $supplierId = $supplierMap[$key]['id'] ?? null;

                if (!$supplierId) {
                    if ($dryRun) {
                        $createdCount++;
                        $this->line("[DRY-RUN] Se tao supplier moi: {$sampleName}");
                    } else {
                        $newSupplier = Supplier::create([
                            'name' => $sampleName,
                        ]);
                        $supplierId = (int) $newSupplier->id;
                        $supplierMap[$key] = [
                            'id' => $supplierId,
                            'name' => $newSupplier->name,
                        ];
                        $createdCount++;
                        $this->line("Da tao supplier: {$newSupplier->name} (#{$newSupplier->id})");
                    }
                }

                $receiptIds = $groupReceipts->pluck('id')->all();

                if ($dryRun) {
                    $linkedCount += count($receiptIds);
                    $this->line('[DRY-RUN] Se gan supplier cho ' . count($receiptIds) . ' phieu.');
                    continue;
                }

                if ($supplierId) {
                    $updated = InventoryReceipt::query()
                        ->whereIn('id', $receiptIds)
                        ->whereNull('supplier_id')
                        ->update(['supplier_id' => $supplierId]);

                    $linkedCount += $updated;
                    $this->line("Da gan supplier_id={$supplierId} cho {$updated} phieu.");
                }
            }
        };

        if ($dryRun) {
            $applySync();
        } else {
            DB::transaction($applySync);
        }

        $this->newLine();
        $this->info('=== KET QUA DONG BO ===');
        $this->info('Tong phieu can xu ly: ' . $receipts->count());
        $this->info('Supplier moi tao: ' . $createdCount);
        $this->info('Phieu da gan supplier_id: ' . $linkedCount);
        $this->info('Phieu bo qua: ' . $skippedCount);

        return self::SUCCESS;
    }

    private function normalizeName(?string $name): string
    {
        $name = preg_replace('/\s+/u', ' ', trim((string) $name));
        return $name ?? '';
    }

    private function normalizeKey(?string $name): string
    {
        return mb_strtolower($this->normalizeName($name), 'UTF-8');
    }

    private function shouldSkipSupplierName(string $sampleName, string $key, array $invalidNames): bool
    {
        if ($sampleName === '' || in_array($key, $invalidNames, true)) {
            return true;
        }

        if (mb_strlen($sampleName, 'UTF-8') < 2) {
            return true;
        }

        if (preg_match('/^\d+$/', $sampleName)) {
            return true;
        }

        if (str_contains($key, 'cập nhật') || str_contains($key, 'cap nhat')) {
            return true;
        }

        return false;
    }
}
