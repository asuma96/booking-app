<?php

namespace App\Domain\Booking;

use App\Domain\Booking\Contracts\BookingValidatorInterface;
use App\Models\ServiceDuration;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

/**
 * Валидатор бизнес-правил для бронирования
 */
class BookingValidator implements BookingValidatorInterface
{
    private const TZ = 'Europe/Moscow';
    private const WORK_START_HOUR = 10;
    private const WORK_END_HOUR = 20;
    private const BUFFER_MINUTES = 30;

    public function validate(ServiceDuration $duration, CarbonImmutable $startMsk): void
    {
        // Проверка: Воскресенье запрещено
        if ($startMsk->isSunday()) {
            throw ValidationException::withMessages([
                'time' => 'В воскресенье бронирование недоступно',
            ]);
        }

        // Проверка: Наличие расписания для услуги в этот день
        $schedule = $duration->service->schedules()
            ->where('weekday', $startMsk->dayOfWeekIso)
            ->first();

        if (!$schedule) {
            throw ValidationException::withMessages([
                'time' => 'В этот день запись недоступна',
            ]);
        }

        // Рассчитываем время окончания с учетом буфера
        $endMsk = $startMsk->addMinutes($duration->minutes + self::BUFFER_MINUTES);

        // Проверка: Начало не раньше часа начала работы
        if ($startMsk->hour < self::WORK_START_HOUR) {
            throw ValidationException::withMessages([
                'time' => sprintf('Бронирование доступно с %d:00 МСК', self::WORK_START_HOUR),
            ]);
        }

        // Проверка: Конец (включая буфер) должен быть до часа окончания работы
        if ($endMsk->hour > self::WORK_END_HOUR || ($endMsk->hour === self::WORK_END_HOUR && $endMsk->minute > 0)) {
            throw ValidationException::withMessages([
                'time' => sprintf('Бронирование должно завершиться до %d:00 МСК (включая 30 мин буфер)', self::WORK_END_HOUR),
            ]);
        }

        // Проверка: Укладывается ли бронирование в окно расписания услуги
        $winStart = $startMsk->setTimeFromTimeString($schedule->start_time);
        $winEnd = $startMsk->setTimeFromTimeString($schedule->end_time);

        if ($startMsk->lt($winStart) || $endMsk->gt($winEnd)) {
            throw ValidationException::withMessages([
                'time' => 'Время должно укладываться в рабочее окно услуги',
            ]);
        }
    }
}

