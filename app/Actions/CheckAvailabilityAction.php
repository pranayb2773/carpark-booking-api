<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Booking;
use Carbon\CarbonPeriod;

final readonly class CheckAvailabilityAction
{
    public function handle(string $fromDate, string $toDate): array
    {
        $period = CarbonPeriod::create($fromDate, $toDate);
        $availabilities = [];
        $totalSpaces = config('parking.total_spaces');

        foreach ($period as $date) {
            $dateString = $date->format('Y-m-d');

            $bookingsOnDate = Booking::query()->active()->forDateRange($dateString, $dateString)->count();

            $availableSpaces = $totalSpaces - $bookingsOnDate;

            $availabilities[] = [
                'date' => $dateString,
                'available_spaces' => max(0, $availableSpaces),
            ];
        }

        return $availabilities;
    }
}
