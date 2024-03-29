<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 18 Aug 2022 23:16:20 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Facades\Actions;

/**
 * @method forPage(mixed $page, mixed $perPage)
 * @method count()
 */
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
        Validator::extend('iunique', function ($attribute, $value, $parameters, $validator) {
            if (isset($parameters[1])) {
                [$connection]  = $validator->parseTable($parameters[0]);
                $wrapped       = DB::connection($connection)->getQueryGrammar()->wrap($parameters[1]);
                $parameters[1] = DB::raw("lower($wrapped)");
            }

            return $validator->validateUnique($attribute, Str::lower($value), $parameters);
        }, trans('validation.iunique'));


        if ($this->app->runningInConsole()) {
            Actions::registerCommands();
        }


        Relation::morphMap(
            [
                'Admin'                  => 'App\Models\SysAdmin\Admin',
                'JobPosition'            => 'App\Models\HumanResources\JobPosition',
                'Group'                  => 'App\Models\SysAdmin\Group',
                'Organisation'           => 'App\Models\SysAdmin\Organisation',
                'User'                   => 'App\Models\SysAdmin\User',
                'Employee'               => 'App\Models\HumanResources\Employee',
                'Guest'                  => 'App\Models\SysAdmin\Guest',
                'Customer'               => 'App\Models\CRM\Customer',
                'Prospect'               => 'App\Models\Deals\Prospect',
                'Shop'                   => 'App\Models\Market\Shop',
                'Fulfilment'             => 'App\Models\Fulfilment\Fulfilment',
                'ProductCategory'        => 'App\Models\Market\ProductCategory',
                'Product'                => 'App\Models\Market\Product',
                'HistoricProduct'        => 'App\Models\Market\HistoricProduct',
                'Supplier'               => 'App\Models\SupplyChain\Supplier',
                'WebUser'                => 'App\Models\CRM\WebUser',
                'Order'                  => 'App\Models\OMS\Order',
                'Agent'                  => 'App\Models\SupplyChain\Agent',
                'TradeUnit'              => 'App\Models\Goods\TradeUnit',
                'Website'                => 'App\Models\Web\Website',
                'Warehouse'              => 'App\Models\Inventory\Warehouse',
                'WarehouseArea'          => 'App\Models\Inventory\WarehouseArea',
                'Location'               => 'App\Models\Inventory\Location',
                'FulfilmentCustomer'     => 'App\Models\Fulfilment\FulfilmentCustomer',
                'Payment'                => 'App\Models\Accounting\Payment',
                'PaymentAccount'         => 'App\Models\Inventory\PaymentAccount',
                'PaymentServiceProvider' => 'App\Models\Inventory\PaymentServiceProvider',


            ]
        );
    }
}
