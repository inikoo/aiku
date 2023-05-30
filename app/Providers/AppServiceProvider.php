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
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Inertia\Response;
use Lorisleiva\Actions\Facades\Actions;
use Inertia\Response as InertiaResponse;
use App\InertiaTable\InertiaTable;

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
        if ($this->app->runningInConsole()) {
            Actions::registerCommands();
        }

        Str::macro('possessive', function (string $string): string {
            return $string.'\''.(
                Str::endsWith($string, ['s', 'S']) ? '' : 's'
            );
        });

        InertiaResponse::macro('getQueryBuilderProps', function (): array {
            return $this->props['queryBuilderProps'] ?? [];
        });

        InertiaResponse::macro('table', function (callable $withTableBuilder = null): Response {
            $tableBuilder = new InertiaTable(request());

            if ($withTableBuilder) {
                $withTableBuilder($tableBuilder);
            }

            return $tableBuilder->applyTo($this);
        });

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

        if (!Collection::hasMacro('paginate')) {

            Collection::macro('paginate',
                function ($perPage = 15, $page = null, $options = []) {
                    $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
                    return (new LengthAwarePaginator(
                        $this->forPage($page, $perPage)->values()->all(), $this->count(), $perPage, $page, $options))
                        ->withPath('');
                });
        }

        Relation::morphMap(
            [
                'Admin'           => 'App\Models\SysAdmin\Admin',
                'User'            => 'App\Models\Auth\User',
                'GroupUser'       => 'App\Models\Auth\GroupUser',
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
                'TradeUnit'       => 'App\Models\Goods\TradeUnit'
            ]
        );
    }
}
