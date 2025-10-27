<?php

namespace App\Domain\Booking\Contracts;

use App\Models\Booking;
use App\Models\ServiceDuration;
use Carbon\CarbonImmutable;

/**
 * Интерфейс репозитория для работы с бронированиями
 */
interface BookingRepositoryInterface
{
    /**
     * Создает новое бронирование с блокировкой от race condition
     *
     * @param ServiceDuration $duration Длительность услуги
     * @param CarbonImmutable $startMsk Время начала (MSK)
     * @param string $customerName Имя клиента
     * @param string $customerPhone Телефон клиента
     * @return Booking
     * @throws \Illuminate\Validation\ValidationException Если слот уже занят
     */
    public function createWithLock(
        ServiceDuration $duration,
        CarbonImmutable $startMsk,
        string $customerName,
        string $customerPhone
    ): Booking;
}

