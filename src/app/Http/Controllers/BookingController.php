<?php

namespace App\Http\Controllers;

use App\Actions\CreateBookingAction;
use App\Http\Requests\BookingRequest;
use App\Models\ServiceDuration;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    public function store(BookingRequest $req, CreateBookingAction $action)
    {
        $data = $req->validated();

        $duration = ServiceDuration::findOrFail((int)$data['service_duration_id']);

        $startMsk = CarbonImmutable::parse($data['date'].' '.$data['time'], 'Europe/Moscow');

        $schedule = $duration->service->schedules()
            ->where('weekday', $startMsk->dayOfWeekIso)
            ->first();

        if (!$schedule || $startMsk->isSunday()) {
            throw ValidationException::withMessages(['time'=>'В этот день запись недоступна']);
        }

        $winStart = $startMsk->setTimeFromTimeString($schedule->start_time);
        $winEnd   = $startMsk->setTimeFromTimeString($schedule->end_time);
        $endMsk = $startMsk->addMinutes((int)$duration->minutes + 30);

        if ($startMsk->lt($winStart) || $endMsk->gt($winEnd)) {
            throw ValidationException::withMessages(['time'=>'Время должно укладываться в рабочее окно']);
        }


        if ($startMsk->isSunday() || $startMsk->hour < 10 || $startMsk->hour >= 20) {
            throw ValidationException::withMessages([
                'time'=>'Можно бронировать Пн–Сб, с 10:00 до 20:00 МСК',
            ]);
        }

        $phone = preg_replace('/(?!^\+)\D+/', '', (string)$data['customer_phone']);

        $action->handle($duration, $startMsk, (string)$data['customer_name'], $phone);

        return redirect()
            ->route('services.show', [
                'service'     => $duration->service_id,
                'duration_id' => $duration->id,
                'date'        => $startMsk->toDateString(),
            ])
            ->with('booked', true);
    }

}
