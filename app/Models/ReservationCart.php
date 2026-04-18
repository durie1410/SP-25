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
        $book = Book::find($bookId);
        return $this->items()->create([
            'book_id' => $bookId,
            'quantity' => max(1, $quantity),
            'daily_fee' => $book?->daily_fee ?? 5000,
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

    public function getTotalPriceAttribute(): float
    {
        return (float) $this->items->sum(function ($item) {
            return (float) ($item->total_price ?? 0);
        });
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

    public function updateDates(int $itemId, string $pickupDate, string $returnDate, ?string $pickupTime = null): array
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
            'pickup_time' => $pickupTime,
            'return_date' => $returnDate,
            'days' => $days,
        ]);

        // Tính trực tiếp không dùng accessor - lấy daily_fee từ cart item hoặc từ sách
        $dailyFeeFromBook = $item->book?->daily_fee ?? 5000;
        $itemPrice = $days * ($item->daily_fee ?? $dailyFeeFromBook) * ($item->quantity ?? 1);
        $cartTotal = $this->items->sum(function($i) use ($days) {
            $iDays = $i->calculateDaysFromDates();
            $iDailyFeeFromBook = $i->book?->daily_fee ?? 5000;
            return $iDays * ($i->daily_fee ?? $iDailyFeeFromBook) * ($i->quantity ?? 1);
        });

        \Log::info('DEBUG updateDates response', [
            'item_id' => $item->id,
            'days' => $days,
            'item_price' => $itemPrice,
            'total_price' => $cartTotal,
            'pickup_date' => $pickupDate,
            'return_date' => $returnDate,
        ]);

        return [
            'success' => true,
            'days' => $days,
            'item_price' => $itemPrice,
            'total_price' => $cartTotal,
        ];
    }

    public function submitReservations(string $notes = null, array $selectedItemIds = [], ?string $pickupTime = null, ?string $reservationCode = null): array
    {
        $createdReservations = [];
        $submittedItems = 0;
        $submittedCopies = 0;

        return DB::transaction(function () use ($notes, $selectedItemIds, $pickupTime, $reservationCode, &$createdReservations, &$submittedItems, &$submittedCopies) {
            $itemIds = collect($selectedItemIds)
                ->map(fn ($itemId) => (int) $itemId)
                ->filter(fn ($itemId) => $itemId > 0)
                ->unique()
                ->values();

            $itemsQuery = $this->items()->with('book')->orderBy('id');
            if ($itemIds->isNotEmpty()) {
                $itemsQuery->whereIn('id', $itemIds->all());
            }

            $items = $itemsQuery->get();

            foreach ($items as $item) {
                $quantity = max(1, (int) ($item->quantity ?? 1));
                // Luôn tính số ngày từ pickup_date và return_date thực tế
                $days = $item->calculateDaysFromDates();
                // Lấy daily_fee từ cart item, nếu null thì lấy từ sách
                $dailyFee = (float) ($item->daily_fee ?? $item->book?->daily_fee ?? 5000);
                $perCopyFee = $days * $dailyFee;
                // Làm tròn giống PricingService để đảm bảo đúng với giá trong phiếu mượn
                $roundTo = config('pricing.rental.round_to', 1000);
                $perCopyFee = round($perCopyFee / $roundTo) * $roundTo;

                \Log::info('DEBUG submitReservations fee calculation', [
                    'item_id' => $item->id,
                    'quantity' => $quantity,
                    'days' => $days,
                    'daily_fee' => $dailyFee,
                    'per_copy_fee' => $perCopyFee,
                    'pickup_date' => $item->pickup_date,
                    'return_date' => $item->return_date,
                ]);

                for ($copy = 0; $copy < $quantity; $copy++) {
                    $reservation = InventoryReservation::create([
                        'book_id' => $item->book_id,
                        'user_id' => $this->user_id,
                        'reader_id' => $this->reader_id,
                        'reservation_code' => $reservationCode,
                        'pickup_date' => $item->pickup_date,
                        'pickup_time' => $pickupTime,
                        'return_date' => $item->return_date,
                        'total_fee' => $perCopyFee,
                        'status' => 'pending',
                        'notes' => $notes,
                    ]);

                    $createdReservations[] = $reservation;
                    $submittedCopies++;
                }

                $submittedItems++;
            }

            if ($items->isNotEmpty()) {
                $this->items()->whereIn('id', $items->pluck('id')->all())->delete();
            }

            if (!$this->items()->exists()) {
                $this->delete();
            }

            return [
                'created' => $createdReservations,
                'submitted_items' => $submittedItems,
                'submitted_copies' => $submittedCopies,
            ];
        });
    }
}
