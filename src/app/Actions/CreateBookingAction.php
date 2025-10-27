<?php

namespace App\Actions;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\DayLock;
use App\Models\ServiceDuration;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class CreateBookingAction
{
    private const BUFFER_MINUTES = 30;

    public function handle(
        ServiceDuration $duration,
        CarbonImmutable $startMsk,
        string $name,
        string $phone
    ): Booking {
        return DB::transaction(function () use ($duration, $startMsk, $name, $phone) {
            $this->validateBookingTime($duration, $startMsk);

            $lock = $this->acquireDayLock($duration, $startMsk);

            $this->checkSlotAvailability($duration, $startMsk);

            return $this->createBooking(
                $duration,
                $startMsk,
                $name,
                $phone
            );
        });
    }

    private function validateBookingTime(ServiceDuration $duration, CarbonImmutable $startMsk): void
    {
        // Проверка, что бронирование не в прошлом
        if ($startMsk->isPast()) {
            throw ValidationException::withMessages([
                'time' => 'Нельзя бронировать время в прошлом.'
            ]);
        }

    }

    private function acquireDayLock(ServiceDuration $duration, CarbonImmutable $startMsk): DayLock
    {
        return DayLock::query()
            ->lockForUpdate()
            ->firstOrCreate([
                'service_id' => $duration->service_id,
                'date'       => $startMsk->toDateString(),
            ]);
    }

    private function checkSlotAvailability(ServiceDuration $duration, CarbonImmutable $startMsk): void
    {
        $endMsk = $startMsk->addMinutes($duration->minutes + self::BUFFER_MINUTES);

        $exists = Booking::query()
            ->where('service_id', $duration->service_id)
            ->where('status', BookingStatus::BOOKED->value)
            ->where('start_at_utc', '<', $endMsk->setTimezone('UTC'))
            ->where('end_at_utc', '>', $startMsk->setTimezone('UTC'))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'time' => 'Слот только что заняли. Обновите страницу.',
            ]);
        }
    }

    private function createBooking(
        ServiceDuration $duration,
        CarbonImmutable $startMsk,
        string $name,
        string $phone
    ): Booking {
        $endMsk = $startMsk->addMinutes($duration->minutes + self::BUFFER_MINUTES);

        return Booking::create([
            'service_id'          => $duration->service_id,
            'service_duration_id' => $duration->id,
            'customer_name'       => $name,
            'customer_phone'      => $phone,
            'start_at_utc'        => $startMsk->setTimezone('UTC'),
            'end_at_utc'          => $endMsk->setTimezone('UTC'),
            'status'              => BookingStatus::BOOKED->value,
        ]);
    }
}
