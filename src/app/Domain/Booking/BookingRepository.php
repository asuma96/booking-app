<?php

namespace App\Domain\Booking;

use App\Domain\Booking\Contracts\AvailabilityCheckerInterface;
use App\Domain\Booking\Contracts\BookingRepositoryInterface;
use App\Models\Booking;
use App\Models\DayLock;
use App\Models\ServiceDuration;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 *
 * Обработка race conditions:
 * При создании бронирования:
 * 1. Начинается транзакция БД
 * 2. Создается/выбирается запись блокировки для конкретного дня и услуги (day_locks)
 * 3. На эту запись устанавливается FOR UPDATE lock, блокирующий другие транзакции
 * 4. Проверяется доступность слота
 * 5. Если слот свободен - создается бронирование
 * 6. Commit транзакции освобождает lock
 *
 * Это гарантирует, что два одновременных запроса на один и тот же день/услугу
 * будут обработаны последовательно, и только первый получит слот.
 */
class BookingRepository implements BookingRepositoryInterface
{
    private const BUFFER_MINUTES = 30;

    public function __construct(
        private readonly AvailabilityCheckerInterface $availabilityChecker
    ) {}

    public function createWithLock(
        ServiceDuration $duration,
        CarbonImmutable $startMsk,
        string $customerName,
        string $customerPhone
    ): Booking {
        return DB::transaction(function () use ($duration, $startMsk, $customerName, $customerPhone) {
            // Шаг 1: Получить или создать запись блокировки для этого дня
            $lock = DayLock::firstOrCreate([
                'service_id' => $duration->service_id,
                'date' => $startMsk->toDateString(),
            ]);

            // Шаг 2: Заблокировать эту запись для обновления (pessimistic lock)
            // Это предотвращает одновременное создание бронирований на один день
            DB::table('day_locks')
                ->where('id', $lock->id)
                ->lockForUpdate()
                ->first();

            // Шаг 3: Рассчитать время окончания с буфером
            $endMsk = $startMsk->addMinutes($duration->minutes + self::BUFFER_MINUTES);
            $startUtc = $startMsk->setTimezone('UTC');
            $endUtc = $endMsk->setTimezone('UTC');

            // Шаг 4: Проверить доступность слота
            if (!$this->availabilityChecker->isSlotAvailable($duration->service_id, $startUtc, $endUtc)) {
                throw ValidationException::withMessages([
                    'time' => 'Слот только что заняли. Обновите страницу.',
                ]);
            }

            // Шаг 5: Создать бронирование
            return Booking::create([
                'service_id' => $duration->service_id,
                'service_duration_id' => $duration->id,
                'customer_name' => $customerName,
                'customer_phone' => $customerPhone,
                'start_at_utc' => $startUtc,
                'end_at_utc' => $endUtc,
                'status' => 'booked',
            ]);
        });
    }
}

