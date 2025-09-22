<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Car Park Configuration
    |--------------------------------------------------------------------------
    */
    'total_spaces' => 10,

    /*
    |--------------------------------------------------------------------------
    | Pricing Rules (in pence)
    |--------------------------------------------------------------------------
    */
    'seasons' => [
        'summer' => [
            'start_month' => 6, // June
            'end_month' => 8,   // August
            'weekday_price' => 2000, // £20.00
            'weekend_price' => 2500, // £25.00
        ],
        'winter' => [
            'start_month' => 11,
            'end_month' => 12,
            'weekday_price' => 1500, // £15.00
            'weekend_price' => 2000, // £20.00
        ],
    ],
    'weekday_price' => 1000,
    'weekend_price' => 1500,
    'currency' => 'GBP',
];
