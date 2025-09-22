<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Number;

uses(RefreshDatabase::class);

test('api returns correct price for a given date range', function () {
    // I have taken weekday.
    $fromDate = '2025-10-01';
    $toDate = '2025-10-02';

    $expectedPrice = Number::currency(config('parking.weekday_price') / 100, config('parking.currency'));

    $this->getJson(route('api.v1.price', [
        'from_date' => $fromDate,
        'to_date' => $toDate,
    ]))->assertStatus(200)
        ->assertJson([
            'success' => true,
            'total_price' => $expectedPrice,
        ]);
});
