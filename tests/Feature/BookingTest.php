<?php

declare(strict_types=1);

use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

test('an authenticated user can create a booking', function () {
    $user = User::factory()->create();

    $bookingData = [
        'from_date' => '2025-12-01',
        'to_date' => '2025-12-03',
    ];

    $response = $this->actingAs($user)->postJson(route('api.v1.bookings.store'), $bookingData);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'success',
            'data' => [],
        ]);

    // SQLite, It is not supporting date type column. It is added as datetime column.
    $this->assertDatabaseHas('bookings', [
        'start_date' => Carbon::parse('2025-12-01')->format('Y-m-d H:i:s'),
        'end_date' => Carbon::parse('2025-12-03')->format('Y-m-d H:i:s'),
        'user_id' => $user->id,
    ]);
});

test('a user cannot create a booking when the car park is full', function () {
    $user = User::factory()->create();

    $fromDate = Carbon::parse('2025-12-01');
    $toDate = Carbon::parse('2025-12-03');

    // Fill the car park
    Booking::factory(config('parking.total_spaces'))->create([
        'start_date' => $fromDate,
        'end_date' => $toDate,
    ]);

    $this->actingAs($user)->postJson(route('api.v1.bookings.store'), [
        'from_date' => $fromDate,
        'to_date' => $toDate,
    ])->assertStatus(422);
});

test('a user cannot create a booking when start date and end date is identical', function () {
    $user = User::factory()->create();

    $fromDate = Carbon::parse('2025-12-01');
    $toDate = Carbon::parse('2025-12-01');

    $this->actingAs($user)->postJson(route('api.v1.bookings.store'), [
        'from_date' => $fromDate,
        'to_date' => $toDate,
    ])->assertStatus(422)
        ->assertJsonValidationErrors('to_date');
});

test('a user can amend their own booking', function () {
    $booking = Booking::factory()->create();

    $this->actingAs($booking->user)->putJson(route('api.v1.bookings.update', $booking->id), [
        'from_date' => '2025-12-01',
        'to_date' => '2025-12-04',
        'status' => BookingStatus::ACTIVE,
    ])->assertStatus(200)
        ->assertJsonFragment([
            'start_date' => Carbon::parse('2025-12-01')->format('Y-m-d'),
            'end_date' => Carbon::parse('2025-12-04')->format('Y-m-d'),
        ]);
});

test('a user cannot amend another users booking or non existed booking', function () {
    $bookingOwner = User::factory()->create();
    $booking = Booking::factory()->create([
        'user_id' => $bookingOwner->id,
    ]);

    $anotherUser = User::factory()->create();

    $this->actingAs($anotherUser)->putJson(route('api.v1.bookings.update', $booking->id), [
        'from_date' => '2025-12-01',
        'to_date' => '2025-12-04',
        'status' => BookingStatus::ACTIVE,
    ])->assertStatus(404); //
});

test('an admin can amend any users booking', function () {
    $admin = User::factory()->create([
        'role' => UserRole::ADMIN,
    ]);
    $booking = Booking::factory()->create();

    $this->actingAs($admin)->putJson(route('api.v1.bookings.update', $booking->id), [
        'from_date' => '2025-12-01',
        'to_date' => '2025-12-03',
        'status' => BookingStatus::ACTIVE,
    ])->assertStatus(200);
});

test('a user can cancel/delete their own booking', function () {
    $booking = Booking::factory()->create();

    $this->actingAs($booking->user)->deleteJson(route('api.v1.bookings.destroy', $booking->id))
        ->assertStatus(200);
});

test('a user can not cancel or delete another user booking', function () {
    $booking = Booking::factory()->create();
    $anotherUser = User::factory()->create();

    $this->actingAs($anotherUser)->deleteJson(route('api.v1.bookings.destroy', $booking->id))
        ->assertStatus(404);
});
