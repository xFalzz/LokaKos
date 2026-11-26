<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        \App\Models\BoardingHouse::class => \App\Policies\BoardingHousePolicy::class,
        \App\Models\Booking::class => \App\Policies\BookingPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
