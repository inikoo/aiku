<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 18 Aug 2022 23:16:20 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Lorisleiva\Actions\Facades\Actions;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
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
                'Admin'           => 'App\Models\SysAdmin\Admin',
                'User'            => 'App\Models\Auth\User',
                'CentralUser'     => 'App\Models\Central\CentralUser',
                'Employee'        => 'App\Models\HumanResources\Employee',
                'Guest'           => 'App\Models\Auth\Guest',
                'Customer'        => 'App\Models\Sales\Customer',
                'Prospect'        => 'App\Models\Deals\Prospect',
                'Shop'            => 'App\Models\Marketing\Shop',
                'Tenant'          => 'App\Models\Tenancy\Tenant',
                'SysUser'         => 'App\Models\SysAdmin\SysUser',
                'ProductCategory' => 'App\Models\Marketing\ProductCategory',
                'Product'         => 'App\Models\Marketing\Product',
                'HistoricProduct' => 'App\Models\Marketing\HistoricProduct',
                'Supplier'        => 'App\Models\Procurement\Supplier',
                'WebUser'         => 'App\Models\Web\WebUser',
                'CentralDomain'   => 'App\Models\Central\CentralDomain',
                'Order'           => 'App\Models\Sales\Order',
                'Agent'           => 'App\Models\Procurement\Agent',
                'Location'        => 'App\Models\Inventory\Location',
                'TradeUnit'       => 'App\Models\Marketing\TradeUnit'
            ]
        );
    }
}
