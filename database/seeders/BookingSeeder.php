<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Database\Seeder;

final class BookingSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::query()->where('role', UserRole::CUSTOMER)->get();

        foreach ($customers as $customer) {
            // Create 2-5 random booking for each customer.
            Booking::factory(rand(2, 5))->forUser($customer)->create();
        }

        // Create some cancelled bookings
        Booking::factory(10)->cancelled()->create();
    }
}
