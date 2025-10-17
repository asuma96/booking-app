<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Service;
use App\Models\ServiceDuration;
use App\Models\ServiceSchedule;
use App\Models\Venue;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class DummyDataSeeder extends Seeder
{
    private const TZ = 'Europe/Moscow';
    private const BUFFER = 30;

    public function run(): void
    {
        $venue = Venue::create(['name' => 'Полигон']);

        $quad  = Service::create(['venue_id'=>$venue->id,'name'=>'Поездка на квадроцикле']);
        $enduro= Service::create(['venue_id'=>$venue->id,'name'=>'Тур на эндуро']);

        $qd30 = ServiceDuration::create(['service_id'=>$quad->id, 'minutes'=>30]);
        $qd60 = ServiceDuration::create(['service_id'=>$quad->id, 'minutes'=>60]);
        $ed60 = ServiceDuration::create(['service_id'=>$enduro->id,'minutes'=>60]);
        $ed120= ServiceDuration::create(['service_id'=>$enduro->id,'minutes'=>120]);

        foreach ([$quad,$enduro] as $s) {
            foreach (range(1,6) as $wd) {
                ServiceSchedule::create([
                    'service_id'=>$s->id,'weekday'=>$wd,'start_time'=>'10:00','end_time'=>'20:00'
                ]);
            }
        }

        $this->book($qd30, '2025-10-16 13:00');
        $this->book($qd30, '2025-10-16 16:00');
        foreach (['10:00','11:00','13:00','18:00'] as $t) $this->book($qd30, "2025-10-17 $t");

        $this->book($qd60, '2025-10-16 10:00');

        foreach (['10:00','11:30','18:30'] as $t) $this->book($ed60, "2025-10-16 $t");

        $this->book($ed120, '2025-10-17 14:00');
    }

    private function book(ServiceDuration $dur, string $mskDateTime): void
    {
        $startMsk = CarbonImmutable::parse($mskDateTime, self::TZ);
        $endMsk   = $startMsk->addMinutes($dur->minutes + self::BUFFER);
        Booking::create([
            'service_id'          => $dur->service_id,
            'service_duration_id' => $dur->id,
            'start_at_utc'        => $startMsk->setTimezone('UTC'),
            'end_at_utc'          => $endMsk->setTimezone('UTC'),
            'customer_name'       => 'Занято',
            'customer_phone'      => '+70000000000',
            'status'              => 'booked',
        ]);
    }
}
