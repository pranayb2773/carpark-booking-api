<?php

declare(strict_types=1);

namespace App\Enums;

enum BookingStatus: int
{
    case CANCELLED = 0;
    case ACTIVE = 1;

    public function label(): string
    {
        return match ($this) {
            BookingStatus::CANCELLED => __('Cancelled'),
            BookingStatus::ACTIVE => __('Active'),
        };
    }
}
