<?php

namespace App\Actions;

use App\Models\Booking;
use App\Models\DayLock;
use App\Models\ServiceDuration;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class CreateBookingAction
{

    public function handle(ServiceDuration $duration, CarbonImmutable $startMsk, string $name, string $phone): Booking
    {
        return DB::transaction(function () use ($duration, $startMsk, $name, $phone) {

            $lock = DayLock::query()->firstOrCreate([
                'service_id' => $duration->service_id,
                'date'       => $startMsk->toDateString(),
            ]);
            DB::table('day_locks')->where('id', $lock->id)->lockForUpdate();

            $endMsk = $startMsk->addMinutes((int)$duration->minutes + 30);

            $exists = Booking::query()
                ->where('service_id', $duration->service_id)
                ->where('status', 'booked')
                ->where('start_at_utc', '<', $endMsk->setTimezone('UTC'))
                ->where('end_at_utc',   '>', $startMsk->setTimezone('UTC'))
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'time' => 'Слот только что заняли. Обновите страницу.',
                ]);
            }

            return Booking::create([
                'service_id'          => $duration->service_id,
                'service_duration_id' => $duration->id,
                'customer_name'       => $name,
                'customer_phone'      => $phone,
                'start_at_utc'        => $startMsk->setTimezone('UTC'),
                'end_at_utc'          => $endMsk->setTimezone('UTC'),
                'status'              => 'booked',
            ]);
        });
    }}
