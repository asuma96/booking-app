<?php

namespace App\Domain\Booking\Contracts;

use App\Models\ServiceDuration;
use Carbon\CarbonImmutable;

/**
 * Интерфейс для проверки доступности слотов
 */
interface AvailabilityCheckerInterface
{
    /**
     * Получить доступные слоты для бронирования на указанный день
     *
     * @param ServiceDuration $duration Длительность услуги
     * @param CarbonImmutable $dayMsk День в часовом поясе МСК
     * @return array Массив доступных слотов в формате 'H:i'
     */
    public function getDaySlots(ServiceDuration $duration, CarbonImmutable $dayMsk): array;

    /**
     * Проверяет, свободен ли указанный временной слот
     *
     * @param int $serviceId ID услуги
     * @param CarbonImmutable $start Начало слота (UTC)
     * @param CarbonImmutable $end Конец слота (UTC)
     * @return bool True если слот свободен
     */
    public function isSlotAvailable(int $serviceId, CarbonImmutable $start, CarbonImmutable $end): bool;
}

