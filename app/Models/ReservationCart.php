<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class ReservationCart extends Model
{
    protected $fillable = ['user_id', 'reader_id', 'pickup_date'];

    protected $dates = ['pickup_date'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reader(): BelongsTo
    {
        return $this->belongsTo(Reader::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReservationCartItem::class, 'cart_id');
    }

    public function getItemCountAttribute(): int
    {
        return (int) $this->items()->sum('quantity');
    }

    public function hasBook(int $bookId): bool
    {
        return $this->items()->where('book_id', $bookId)->exists();
    }

    public function addBook(int $bookId, int $quantity = 1): ?ReservationCartItem
    {
        return $this->items()->create([
            'book_id' => $bookId,
            'quantity' => max(1, $quantity),
        ]);
    }

    public function removeBook(int $itemId): bool
    {
        return (bool) $this->items()->where('id', $itemId)->delete();
    }

    public function clear(): void
    {
        $this->items()->delete();
    }

    public function updateQuantity(int $itemId, int $quantity): array
    {
        $item = $this->items()->where('id', $itemId)->first();

        if (!$item) {
            return ['success' => false, 'message' => 'Sách không có trong giỏ'];
        }

        $quantity = max(1, $quantity);

        $item->update(['quantity' => $quantity]);

        return [
            'success' => true,
            'quantity' => $quantity,
            'item_price' => $item->fresh()->total_price,
            'total_price' => $this->fresh()->total_price,
        ];
    }

    public function updateDates(int $itemId, string $pickupDate, string $returnDate): array
    {
        $item = $this->items()->where('id', $itemId)->first();

        if (!$item) {
            return ['success' => false, 'message' => 'Sách không có trong giỏ'];
        }

        $pickup = new \DateTime($pickupDate);
        $return = new \DateTime($returnDate);
        $today = new \DateTime();
        $today->setTime(0, 0, 0);

        if ($pickup < $today) {
            return ['success' => false, 'message' => 'Ngày lấy không được là ngày quá khứ'];
        }

        if ($return <= $pickup) {
            return ['success' => false, 'message' => 'Ngày trả phải sau ngày lấy'];
        }

        $days = max(1, (int) $pickup->diff($return)->days);

        $item->update([
            'pickup_date' => $pickupDate,
            'return_date' => $returnDate,
            'days' => $days,
        ]);

        return [
            'success' => true,
            'days' => $days,
            'item_price' => $item->fresh()->total_price,
            'total_price' => $this->fresh()->total_price,
        ];
    }

    public function submitReservations(string $notes = null): array
    {
        $createdReservations = [];
        $skippedCount = 0;

        return DB::transaction(function () use ($notes, &$createdReservations, &$skippedCount) {
            $bookIds = $this->items->pluck('book_id')->toArray();

            $existingByBookId = InventoryReservation::where('user_id', $this->user_id)
                ->whereIn('book_id', $bookIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('book_id');

            foreach ($this->items as $item) {
                $existing = $existingByBookId->get($item->book_id);

                if ($existing && in_array($existing->status, ['pending', 'ready'], true)) {
                    $skippedCount++;
                    continue;
                }

                try {
                    if ($existing) {
                        $existing->update([
                            'status' => 'pending',
                            'notes' => $notes,
                            'admin_note' => null,
                            'inventory_id' => null,
                            'borrow_id' => null,
                            'processed_by' => null,
                            'ready_at' => null,
                            'fulfilled_at' => null,
                            'cancelled_at' => null,
                        ]);
                        $createdReservations[] = $existing;
                    } else {
                        $reservation = InventoryReservation::create([
                            'book_id' => $item->book_id,
                            'user_id' => $this->user_id,
                            'reader_id' => $this->reader_id,
                            'status' => 'pending',
                            'notes' => $notes,
                        ]);
                        $createdReservations[] = $reservation;
                    }
                } catch (\Illuminate\Database\QueryException $e) {
                    $skippedCount++;
                }
            }

            $this->clear();

            return [
                'created' => $createdReservations,
                'skipped' => $skippedCount,
            ];
        });
    }
}
