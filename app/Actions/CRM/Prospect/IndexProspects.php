<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 21 Jun 2023 08:45:00 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Prospect;

use App\Actions\InertiaAction;
use App\Actions\Market\Shop\UI\ShowShop;
use App\Actions\UI\Dashboard\Dashboard;
use App\Http\Resources\Lead\ProspectResource;
use App\InertiaTable\InertiaTable;
use App\Models\CRM\Prospect;
use App\Models\Market\Shop;
use App\Models\Tenancy\Tenant;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexProspects extends InertiaAction
{
    public function handle(Shop|Tenant $parent, $prefix=null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('prospects.name', '~*', "\y$value\y")
                    ->orWhere('prospects.email', '=', $value)
                    ->orWhere('prospects.phone', '=', $value)
                    ->orWhere('prospects.website', '=', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        /** @noinspection PhpUndefinedMethodInspection */
        return QueryBuilder::for(Prospect::class)
            ->defaultSort('prospects.name')
            ->select([
                'prospects.name',
                'prospects.slug',
                'prospects.email',
                'prospects.phone',
                'prospects.website',
                'prospects.id as shop_id',
                'shops.code as shop_code',
                'shops.slug as shop_slug',
            ])
            ->leftJoin('shops', 'shops.id', 'shop_id')
            ->when($parent, function ($query) use ($parent) {
                if (class_basename($parent) == 'Shop') {
                    $query->where('prospects.shop_id', $parent->id);
                }
            })
            ->allowedSorts(['name', 'email', 'phone', 'website'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure($parent, $prefix=null): Closure
    {
        return function (InertiaTable $table) use ($parent, $prefix) {

            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            $table->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'email', label: __('email'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'phone', label: __('phone'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'website', label: __('website'), canBeHidden: false, sortable: true, searchable: true);
        };
    }
    public function authorize(ActionRequest $request): bool
    {
        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('crm.customers.view')
            );
    }


    public function jsonResponse(LengthAwarePaginator $prospects): AnonymousResourceCollection
    {
        return ProspectResource::collection($prospects);
    }


    public function htmlResponse(LengthAwarePaginator $prospects, ActionRequest $request): Response
    {
        $parent = $request->route()->parameters() == [] ? app('currentTenant') : last($request->route()->parameters());

        return Inertia::render(
            'CRM/Prospects',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters(),
                ),
                'title'       => __('prospects'),
                'pageHead'    => [
                    'title'   => __('prospects'),
                ],
                'prospects' => ProspectResource::collection($prospects),


            ]
        )->table($this->tableStructure($parent));
    }


    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        $this->routeName = $request->route()->getName();
        $this->initialisation($request);
        return $this->handle(app('currentTenant'));
    }

    public function inShop(Shop $shop, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);

        return $this->handle($shop);
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        $headCrumb = function (array $routeParameters = []) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => $routeParameters,
                        'label' => __('prospects'),
                        'icon'  => 'fal fa-bars'
                    ],
                ],
            ];
        };

        return match ($routeName) {
            'prospects.index' =>
            array_merge(
                Dashboard::make()->getBreadcrumbs(),
                $headCrumb(
                    [
                        'name' => 'prospects.index',
                    ]
                ),
            ),
            'shops.show.prospects.index' =>
            array_merge(
                (new ShowShop())->getBreadcrumbs($routeParameters),
                $headCrumb(
                    [
                        'name'      => 'shops.show.prospects.index',
                        'parameters'=>
                            [
                                $routeParameters['shop']->slug
                            ]
                    ]
                )
            ),
            default => []
        };
    }
}
