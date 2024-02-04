<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 20 Sept 2022 12:26:11 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public const HOME = '/dashboard';


    public function boot(): void
    {
        $this->configureRateLimiting();

        Route::prefix('webhooks')
            ->domain(config('app.domain'))
            ->middleware('webhooks')
            ->group(base_path('routes/grp/webhooks/webhooks.php'));

        Route::prefix('api')
            ->domain(config('app.domain'))
            ->middleware('api')
            ->group(base_path('routes/grp/api/api.php'));

        Route::domain('app.'.config('app.domain'))
            ->middleware('grp')
            ->name('grp.')
            ->group(base_path('routes/grp/web/app.php'));

        Route::middleware('public')
            ->domain(config('app.domain'))
            ->name('public.')
            ->group(base_path('routes/public/web/app.php'));


    }

    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(600)->by($request->user()?->id ?: $request->ip());
        });
    }
}
