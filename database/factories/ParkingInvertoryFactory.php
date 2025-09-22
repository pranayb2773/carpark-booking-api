<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ParkingInvertory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

final class ParkingInvertoryFactory extends Factory
{
    protected $model = ParkingInvertory::class;

    public function definition(): array
    {
        return [
            'date' => Carbon::now(),
            'reserved' => $this->faker->randomNumber(),
        ];
    }
}
