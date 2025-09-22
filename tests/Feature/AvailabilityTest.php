<?php

declare(strict_types=1);

use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('api returns correct availability when car park is empty', function () {
    $fromDate = '2025-10-01';
    $toDate = '2025-10-02';
    $totalSpaces = config('parking.total_spaces');

    $response = $this->getJson("api/v1/availability?from_date=$fromDate&to_date=$toDate");

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data')
        ->assertJson([
            'success' => true,
            'data' => [
                ['date' => $fromDate, 'available_spaces' => $totalSpaces],
                ['date' => $toDate, 'available_spaces' => $totalSpaces],
            ],
        ]);
});

test('api returns correct availability when car park is full', function () {
    $fromDate = '2025-10-05';
    $toDate = '2025-10-06';

    Booking::factory(config('parking.total_spaces'))->create([
        'start_date' => $fromDate,
        'end_date' => $toDate,
    ]);

    $response = $this->getJson("api/v1/availability?from_date=$fromDate&to_date=$toDate");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                ['date' => $fromDate, 'available_spaces' => 0],
                ['date' => $toDate, 'available_spaces' => config('parking.total_spaces')],
            ],
        ]);
});

test('api returns correct availability when car park spaces are booked', function () {
    $fromDate = '2025-10-01';
    $toDate = '2025-10-02';

    $totalSpaces = config('parking.total_spaces');

    Booking::factory()->create([
        'start_date' => $fromDate,
        'end_date' => $toDate,
    ]);

    $response = $this->getJson("api/v1/availability?from_date=$fromDate&to_date=$toDate");

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data')
        ->assertJson([
            'success' => true,
            'data' => [
                ['date' => $fromDate, 'available_spaces' => $totalSpaces - 1],
                ['date' => $toDate, 'available_spaces' => $totalSpaces],
            ],
        ]);
});

test('api returns validation error for identical from_date and to_date', function () {
    $date = '2025-10-01';

    $response = $this->getJson("api/v1/availability?from_date=$date&to_date=$date");

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('to_date');
});
