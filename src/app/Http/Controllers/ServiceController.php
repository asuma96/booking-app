<?php

namespace App\Http\Controllers;

use App\Domain\Booking\AvailabilityService;
use App\Models\Service;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\CarbonImmutable;

class ServiceController extends Controller
{
    public function index()
    {
        return Inertia::render('Services/Index', [
            'services' => \App\Models\Service::query()->get(['id','name'])
        ]);
    }

    public function show(Request $req, Service $service, AvailabilityService $avail)
    {
        $duration = $service->durations()
            ->when($req->filled('duration_id'), fn($q)=>$q->where('id', (int)$req->query('duration_id')))
            ->orderBy('minutes')
            ->firstOrFail();

        $weekStart = $req->query('date')
            ? CarbonImmutable::parse($req->query('date'), 'Europe/Moscow')->startOfWeek()
            : CarbonImmutable::now('Europe/Moscow')->startOfWeek();

        $days = collect(range(0,6))->map(function($i) use ($weekStart) {
            $d = $weekStart->addDays($i);
            return ['iso'=>$d->toDateString(), 'label'=>$d->isoFormat('dd, D MMM')];
        });

        $selected = $req->query('date')
            ? CarbonImmutable::parse($req->query('date'), 'Europe/Moscow')
            : $weekStart;

        return Inertia::render('Services/Show', [
            'service'     => ['id'=>$service->id,'name'=>$service->name],
            'durations'   => $service->durations()->orderBy('minutes')->get(['id','minutes']),
            'durationId'  => $duration->id,
            'weekStart'   => $weekStart->toDateString(),
            'days'        => $days,
            'selectedDay' => $selected->toDateString(),
            'slots'       => $avail->daySlots($duration, $selected),
            'booked'      => (bool)session()->pull('booked', false),
        ]);
    }
}
