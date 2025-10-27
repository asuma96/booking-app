<?php

namespace App\Providers;

use App\Domain\Booking\AvailabilityChecker;
use App\Domain\Booking\BookingRepository;
use App\Domain\Booking\BookingValidator;
use App\Domain\Booking\Contracts\AvailabilityCheckerInterface;
use App\Domain\Booking\Contracts\BookingRepositoryInterface;
use App\Domain\Booking\Contracts\BookingValidatorInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Регистрация сервисов бронирования
        $this->app->singleton(BookingValidatorInterface::class, BookingValidator::class);
        $this->app->singleton(AvailabilityCheckerInterface::class, AvailabilityChecker::class);
        $this->app->singleton(BookingRepositoryInterface::class, BookingRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::setLocale('ru');
        Vite::prefetch(concurrency: 3);
    }
}
