<?php

namespace Tests\Unit\Models;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Service;
use App\Models\ServiceDuration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_booking()
    {
        $service = Service::factory()->create();
        $serviceDuration = ServiceDuration::factory()->create([
            'service_id' => $service->id,
            'minutes' => 60
        ]);

        $booking = Booking::create([
            'service_id' => $service->id,
            'service_duration_id' => $serviceDuration->id,
            'customer_name' => 'John Doe',
            'customer_phone' => '+79991234567',
            'start_at_utc' => now(),
            'end_at_utc' => now()->addHour(),
            'status' => BookingStatus::BOOKED->value
        ]);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'customer_name' => 'John Doe',
            'status' => BookingStatus::BOOKED->value
        ]);
    }

    /** @test */
    public function it_has_correct_status_methods()
    {
        $booking = Booking::factory()->create([
            'status' => BookingStatus::BOOKED->value
        ]);

        $this->assertTrue($booking->isBooked());
        $this->assertFalse($booking->isCancelled());
        $this->assertFalse($booking->isCompleted());

        $booking->status = BookingStatus::CANCELLED->value;
        $this->assertTrue($booking->isCancelled());

        $booking->status = BookingStatus::COMPLETED->value;
        $this->assertTrue($booking->isCompleted());
    }

    /** @test */
    public function it_has_correct_relationships()
    {
        $service = Service::factory()->create();
        $serviceDuration = ServiceDuration::factory()->create([
            'service_id' => $service->id
        ]);

        $booking = Booking::factory()->create([
            'service_id' => $service->id,
            'service_duration_id' => $serviceDuration->id
        ]);

        $this->assertNotNull($booking->service);
        $this->assertNotNull($booking->duration);
        $this->assertEquals($service->id, $booking->service->id);
        $this->assertEquals($serviceDuration->id, $booking->duration->id);
    }
}
