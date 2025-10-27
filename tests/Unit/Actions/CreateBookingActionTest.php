<?php

namespace Tests\Unit\Actions;

use App\Actions\CreateBookingAction;
use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\ServiceDuration;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CreateBookingActionTest extends TestCase
{
    use RefreshDatabase;

    private CreateBookingAction $createBookingAction;
    private ServiceDuration $serviceDuration;

    protected function setUp(): void
    {
        parent::setUp();

        // Создаем mock ServiceDuration
        $this->serviceDuration = ServiceDuration::factory()->create([
            'minutes' => 60,
            'service_id' => 1
        ]);

        $this->createBookingAction = new CreateBookingAction();
    }

    /** @test */
    public function it_creates_a_booking_successfully()
    {
        $startTime = CarbonImmutable::now()->addHours(1);
        
        $booking = $this->createBookingAction->handle(
            $this->serviceDuration,
            $startTime,
            'John Doe',
            '+79991234567'
        );

        $this->assertInstanceOf(Booking::class, $booking);
        $this->assertEquals(BookingStatus::BOOKED->value, $booking->status);
        $this->assertEquals($this->serviceDuration->service_id, $booking->service_id);
    }

    /** @test */
    public function it_throws_exception_for_past_booking_time()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Нельзя бронировать время в прошлом.');

        $pastTime = CarbonImmutable::now()->subHour();
        
        $this->createBookingAction->handle(
            $this->serviceDuration,
            $pastTime,
            'John Doe',
            '+79991234567'
        );
    }

    /** @test */
    public function it_prevents_overlapping_bookings()
    {
        // Создаем первое бронирование
        $startTime = CarbonImmutable::now()->addHours(1);
        $this->createBookingAction->handle(
            $this->serviceDuration,
            $startTime,
            'First Customer',
            '+79991111111'
        );

        // Пытаемся создать перекрывающееся бронирование
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Слот только что заняли.');

        $overlappingTime = $startTime->addMinutes(30);
        $this->createBookingAction->handle(
            $this->serviceDuration,
            $overlappingTime,
            'Second Customer',
            '+79992222222'
        );
    }

    /** @test */
    public function it_handles_buffer_time_between_bookings()
    {
        // Создаем первое бронирование
        $startTime = CarbonImmutable::now()->addHours(1);
        $this->createBookingAction->handle(
            $this->serviceDuration,
            $startTime,
            'First Customer',
            '+79991111111'
        );

        // Создаем бронирование после буферного времени (30 минут)
        $subsequentBookingTime = $startTime->addMinutes(
            $this->serviceDuration->minutes + 30 + 1
        );

        $subsequentBooking = $this->createBookingAction->handle(
            $this->serviceDuration,
            $subsequentBookingTime,
            'Second Customer',
            '+79992222222'
        );

        $this->assertInstanceOf(Booking::class, $subsequentBooking);
        $this->assertEquals(BookingStatus::BOOKED->value, $subsequentBooking->status);
    }
}
