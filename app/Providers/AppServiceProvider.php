<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        Relation::morphMap([
            'Member'        => \App\Models\Member::class,
            'Locker'        => \App\Models\Locker::class,
            'FoodItem'      => \App\Models\FoodItem::class,
            'LiquorServing' => \App\Models\LiquorServing::class,
        ]);
    }
}
