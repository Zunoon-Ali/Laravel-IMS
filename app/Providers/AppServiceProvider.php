<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Illuminate\Support\Facades\Event;
use App\Events\PersonalPaymentRecorded;
use App\Listeners\LogPersonalPaymentSuccess;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Repositories\Contracts\PersonalStockRepositoryInterface::class,
            \App\Repositories\Eloquent\PersonalStockRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\PersonalPaymentRepositoryInterface::class,
            \App\Repositories\Eloquent\PersonalPaymentRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\PersonalReturnRepositoryInterface::class,
            \App\Repositories\Eloquent\PersonalReturnRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\PersonalSupplierRepositoryInterface::class,
            \App\Repositories\Eloquent\PersonalSupplierRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\PersonalCustomerRepositoryInterface::class,
            \App\Repositories\Eloquent\PersonalCustomerRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\PersonalPaymentSentRepositoryInterface::class,
            \App\Repositories\Eloquent\PersonalPaymentSentRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(
            PersonalPaymentRecorded::class,
            LogPersonalPaymentSuccess::class
        );
    }
}
