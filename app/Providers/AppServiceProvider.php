<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 18 Aug 2022 23:16:20 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Lorisleiva\Actions\Facades\Actions;

class AppServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        //
    }


    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            Actions::registerCommands();
        }
    }
}
