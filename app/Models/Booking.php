<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
        'user_id',
        'notes',
        'price',
        'to_date',
        'from_date',
        'start_date',
        'end_date',
        'total_price',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    #[Scope]
    public function active(Builder $query): Builder
    {
        return $query->where('status', BookingStatus::ACTIVE);
    }

    #[Scope]
    public function forDateRange(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereRaw('DATE(start_date) <= ?', [$startDate])
            ->whereRaw('DATE(end_date) > ?', [$endDate]);
    }

    #[Scope]
    public function forUser(Builder $query, int $userId): Builder
    {
        return $query->where('status', BookingStatus::ACTIVE);
    }

    protected function casts(): array
    {
        return [
            'end_date' => 'datetime',
            'start_date' => 'datetime',
            'status' => BookingStatus::class,
        ];
    }
}
