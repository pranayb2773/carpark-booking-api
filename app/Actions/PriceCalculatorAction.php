<?php

declare(strict_types=1);

namespace App\Actions;

use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;

final readonly class PriceCalculatorAction
{
    public function handle(string $fromDate, string $toDate): float
    {
        $period = CarbonPeriod::create($fromDate, Carbon::parse($toDate)->subDay());
        $totalPrice = 0;

        $summerStart = config('parking.seasons.summer.start_month');
        $summerEnd = config('parking.seasons.summer.end_month');

        $winterStart = config('parking.seasons.winter.start_month');
        $winterEnd = config('parking.seasons.winter.end_month');

        foreach ($period as $date) {
            $isSummer = $date->month >= $summerStart && $date->month <= $summerEnd;
            $isWinter = $date->month >= $winterStart && $date->month <= $winterEnd;
            $isWeekend = $date->isWeekend();

            if ($isSummer) {
                $totalPrice += $isWeekend
                    ? config('parking.seasons.summer.weekend_price')
                    : config('parking.seasons.summer.weekday_price');
            } elseif ($isWinter) {
                $totalPrice += $isWeekend
                    ? config('parking.seasons.winter.weekend_price')
                    : config('parking.seasons.winter.weekday_price');
            } else {
                $totalPrice += $isWeekend
                    ? (int) config('parking.weekend_price')
                    : (int) config('parking.weekday_price');
            }
        }

        return $this->convertPrice($totalPrice);
    }

    private function convertPrice(int $price): float
    {
        return $price / 100;
    }
}
