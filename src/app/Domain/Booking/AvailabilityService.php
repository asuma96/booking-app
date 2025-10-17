<?php

namespace App\Domain\Booking;

use App\Models\Booking;
use App\Models\ServiceDuration;
use Carbon\CarbonImmutable;

class AvailabilityService
{
    public const TZ = 'Europe/Moscow';

    public function daySlots(ServiceDuration $duration, CarbonImmutable $dayMsk): array
    {
        if ($dayMsk->isSunday()) return [];

        $row = $duration->service->schedules()->where('weekday', $dayMsk->dayOfWeekIso)->first();
        if (!$row) return [];

        $winStart = $dayMsk->setTimeFromTimeString($row->start_time);
        $winEnd   = $dayMsk->setTimeFromTimeString($row->end_time);

        $durMin = (int)$duration->minutes;

        $busy = Booking::query()
            ->where('service_id', $duration->service_id)
            ->where('status','booked')
            ->where('start_at_utc','<', $winEnd->setTimezone('UTC'))
            ->where('end_at_utc','>',   $winStart->setTimezone('UTC'))
            ->get(['start_at_utc','end_at_utc'])
            ->map(fn($b)=>[
                CarbonImmutable::parse($b->start_at_utc,'UTC')->setTimezone(self::TZ),
                CarbonImmutable::parse($b->end_at_utc,'UTC')->setTimezone(self::TZ),
            ])->all();

        $grid = 30;
        $lastStart = $winEnd->subMinutes($durMin);

        $slots = [];
        for ($t = $winStart; $t->lte($lastStart); $t = $t->addMinutes($grid)) {
            $s = $t;
            $e = $t->addMinutes($durMin);
            $overlap = collect($busy)->first(fn($r)=> $s->lt($r[1]) && $e->gt($r[0]));
            if (!$overlap) $slots[] = $s->format('H:i');
        }
        return $slots;
    }


}
