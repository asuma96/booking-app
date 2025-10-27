<?php

namespace App\Domain\Booking;

use App\Domain\Booking\Contracts\AvailabilityCheckerInterface;
use App\Models\Booking;
use App\Models\ServiceDuration;
use Carbon\CarbonImmutable;

/**
 * Сервис для проверки доступности слотов
 */
class AvailabilityChecker implements AvailabilityCheckerInterface
{
    private const TZ = 'Europe/Moscow';
    private const SLOT_GRID_MINUTES = 30;
    private const BUFFER_MINUTES = 30;

    public function getDaySlots(ServiceDuration $duration, CarbonImmutable $dayMsk): array
    {
        if ($dayMsk->isSunday()) {
            return [];
        }

        $schedule = $duration->service->schedules()
            ->where('weekday', $dayMsk->dayOfWeekIso)
            ->first();

        if (!$schedule) {
            return [];
        }

        $winStart = $dayMsk->setTimeFromTimeString($schedule->start_time);
        $winEnd = $dayMsk->setTimeFromTimeString($schedule->end_time);

        $durationWithBuffer = $duration->minutes + self::BUFFER_MINUTES;

        // Получаем все занятые слоты
        $busySlots = $this->getBusySlots($duration->service_id, $winStart, $winEnd);

        // Генерируем доступные слоты
        $slots = [];
        $lastStart = $winEnd->subMinutes($durationWithBuffer);

        for ($t = $winStart; $t->lte($lastStart); $t = $t->addMinutes(self::SLOT_GRID_MINUTES)) {
            $slotEnd = $t->addMinutes($durationWithBuffer);

            // Проверяем пересечение с занятыми слотами
            $hasOverlap = collect($busySlots)->contains(function ($busy) use ($t, $slotEnd) {
                return $t->lt($busy[1]) && $slotEnd->gt($busy[0]);
            });

            if (!$hasOverlap) {
                $slots[] = $t->format('H:i');
            }
        }

        return $slots;
    }

    public function isSlotAvailable(int $serviceId, CarbonImmutable $start, CarbonImmutable $end): bool
    {
        return !Booking::query()
            ->where('service_id', $serviceId)
            ->where('status', 'booked')
            ->where('start_at_utc', '<', $end)
            ->where('end_at_utc', '>', $start)
            ->exists();
    }

    /**
     * Получает занятые слоты для указанного временного окна
     *
     * @param int $serviceId
     * @param CarbonImmutable $winStart
     * @param CarbonImmutable $winEnd
     * @return array Массив массивов [start, end] в MSK
     */
    private function getBusySlots(int $serviceId, CarbonImmutable $winStart, CarbonImmutable $winEnd): array
    {
        return Booking::query()
            ->where('service_id', $serviceId)
            ->where('status', 'booked')
            ->where('start_at_utc', '<', $winEnd->setTimezone('UTC'))
            ->where('end_at_utc', '>', $winStart->setTimezone('UTC'))
            ->get(['start_at_utc', 'end_at_utc'])
            ->map(fn($b) => [
                CarbonImmutable::parse($b->start_at_utc, 'UTC')->setTimezone(self::TZ),
                CarbonImmutable::parse($b->end_at_utc, 'UTC')->setTimezone(self::TZ),
            ])
            ->all();
    }
}

