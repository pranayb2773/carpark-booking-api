<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

final class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('+1 day', '+1 month');
        $endDate = (clone $startDate)->modify('+'.fake()->numberBetween(1, 10).' days');

        return [
            'user_id' => User::factory(),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'total_price' => fake()->randomNumber(5, false),
            'status' => BookingStatus::ACTIVE,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BookingStatus::CANCELLED,
        ]);
    }

    public function forUser(User $user): static
    {
        return $this->state(function (array $attributes) use ($user) {
            return [
                'user_id' => $user->id,
            ];
        });
    }
}
