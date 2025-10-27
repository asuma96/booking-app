<?php

namespace App\Domain\Booking\Contracts;

use App\Models\ServiceDuration;
use Carbon\CarbonImmutable;

/**
 * Интерфейс для валидации бизнес-правил бронирования
 */
interface BookingValidatorInterface
{
    /**
     * Проверяет, доступна ли услуга для бронирования в указанное время
     *
     * @param ServiceDuration $duration Длительность услуги
     * @param CarbonImmutable $startTime Время начала бронирования (MSK)
     * @return void
     * @throws \Illuminate\Validation\ValidationException Если валидация не прошла
     */
    public function validate(ServiceDuration $duration, CarbonImmutable $startTime): void;
}

