<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class CreateCarParkBookingAction
{
    /**
     * Execute the action.
     */
    public function handle(array $data): Booking
    {
        return DB::transaction(function () use ($data): Booking {
            $this->checkAvailabilityForPeriod($data['from_date'], $data['to_date']);

            $price = (new PriceCalculatorAction())->handle($data['from_date'], $data['to_date']);

            $user = Auth::user();

            return Booking::create([
                'start_date' => $data['from_date'],
                'end_date' => $data['to_date'],
                'total_price' => (int) $price * 100,
                'notes' => $data['notes'] ?? null,
                'status' => BookingStatus::ACTIVE,
                'user_id' => $user->isAdmin() && isset($user['user_id']) ? $data['user_id'] : $user->id,
            ]);
        }, 3);
    }

    private function checkAvailabilityForPeriod(string $fromDate, string $toDate, ?int $ignoreBookingId = null): void
    {
        $period = CarbonPeriod::create($fromDate, $toDate);
        $totalSpaces = config('parking.total_spaces');

        foreach ($period as $date) {
            $dateString = $date->format('Y-m-d');

            $bookingsOnDate = Booking::query()->forDateRange($dateString, $dateString)
                ->active()
                ->lockForUpdate()
                ->when($ignoreBookingId !== null, function ($query) use ($ignoreBookingId) {
                    return $query->where('id', '!=', $ignoreBookingId);
                })->count();

            if ($bookingsOnDate >= $totalSpaces) {
                throw ValidationException::withMessages([
                    'availability' => 'No parking spaces available for '.$dateString,
                ]);
            }
        }
    }
}
