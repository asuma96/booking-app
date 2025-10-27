<?php

namespace App\Http\Controllers;

use App\Domain\Booking\Contracts\AvailabilityCheckerInterface;
use App\Models\Service;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\CarbonImmutable;

/**
 * Контроллер для отображения услуг и доступных слотов
 */
class ServiceController extends Controller
{
    /**
     * Отображает список всех услуг
     */
    public function index()
    {
        return Inertia::render('Services/Index', [
            'services' => Service::query()->get(['id', 'name'])
        ]);
    }

    /**
     * Отображает детали услуги, календарь и доступные слоты
     */
    public function show(Request $req, Service $service, AvailabilityCheckerInterface $availabilityChecker)
    {
        // Выбираем длительность услуги
        $duration = $service->durations()
            ->when($req->filled('duration_id'), fn($q) => $q->where('id', (int)$req->query('duration_id')))
            ->orderBy('minutes')
            ->firstOrFail();

        $weekStart = $req->query('date')
            ? CarbonImmutable::parse($req->query('date'), 'Europe/Moscow')->startOfWeek()
            : CarbonImmutable::now('Europe/Moscow')->startOfWeek();

        $days = collect(range(0, 6))->map(function($i) use ($weekStart) {
            $d = $weekStart->addDays($i);
            return ['iso' => $d->toDateString(), 'label' => $d->isoFormat('dd, D MMM')];
        });

        $selected = $req->query('date')
            ? CarbonImmutable::parse($req->query('date'), 'Europe/Moscow')
            : $weekStart;

        return Inertia::render('Services/Show', [
            'service'     => ['id' => $service->id, 'name' => $service->name],
            'durations'   => $service->durations()->orderBy('minutes')->get(['id', 'minutes']),
            'durationId'  => $duration->id,
            'weekStart'   => $weekStart->toDateString(),
            'days'        => $days,
            'selectedDay' => $selected->toDateString(),
            'slots'       => $availabilityChecker->getDaySlots($duration, $selected),
            'booked'      => (bool)session()->pull('booked', false),
        ]);
    }
}
