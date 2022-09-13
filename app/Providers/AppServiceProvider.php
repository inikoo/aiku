<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 18 Aug 2022 23:16:20 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Providers;

use App\Managers\Organisation\OrganisationManager;
use App\Managers\Organisation\SourceOrganisationManager;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Lorisleiva\Actions\Facades\Actions;

class AppServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->app->bind(SourceOrganisationManager::class, function () {
            return new OrganisationManager();
        });
        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }


    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            Actions::registerCommands();
        }

        Request::macro('validatedShiftToArray', function ($map = []): array {
            /** @noinspection PhpUndefinedMethodInspection */
            $validated = $this->validated();
            foreach ($map as $field => $destination) {
                if (array_key_exists($field, $validated)) {
                    Arr::set($validated, "$destination.$field", $validated[$field]);
                    Arr::forget($validated, $field);
                }
            }

            return $validated;
        });

        Relation::morphMap(
            [
                'User'         => 'App\Models\SysAdmin\User',
                'Employee'     => 'App\Models\HumanResources\Employee',
                'Guest'        => 'App\Models\SysAdmin\Guest',
                'Customer'     => 'App\Models\CRM\Customer',
                'Shop'         => 'App\Models\Marketing\Shop',
                'Organisation' => 'App\Models\Organisations\Organisation',

            ]
        );
    }
}
