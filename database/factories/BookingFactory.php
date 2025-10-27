<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Service;
use App\Models\ServiceDuration;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        $service = Service::factory()->create();
        $serviceDuration = ServiceDuration::factory()->create([
            'service_id' => $service->id
        ]);

        $startAt = now()->addDays(rand(1, 30))->setTime(
            rand(9, 18), 
            rand(0, 59)
        );

        return [
            'service_id' => $service->id,
            'service_duration_id' => $serviceDuration->id,
            'customer_name' => $this->faker->name(),
            'customer_phone' => $this->faker->phoneNumber(),
            'start_at_utc' => $startAt,
            'end_at_utc' => $startAt->addMinutes($serviceDuration->minutes),
            'status' => $this->faker->randomElement(BookingStatus::values())
        ];
    }

    public function booked(): static
    {
        return $this->state(fn () => [
            'status' => BookingStatus::BOOKED->value
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => [
            'status' => BookingStatus::CANCELLED->value
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => BookingStatus::COMPLETED->value
        ]);
    }
}
