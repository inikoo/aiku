<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 26 Jan 2024 18:40:36 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Pallet\UI;

use App\Actions\Fulfilment\Fulfilment\UI\ShowFulfilment;
use App\Actions\OrgAction;
use App\Enums\UI\FulfilmentTabsEnum;
use App\Http\Resources\Fulfilment\PalletResource;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\Pallet;
use App\Models\Inventory\Location;
use App\Models\SysAdmin\Organisation;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use App\InertiaTable\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexPallets extends OrgAction
{
    public function handle(Organisation|FulfilmentCustomer|Location|Fulfilment $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereStartWith('customer_reference', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $query=QueryBuilder::for(Pallet::class);

        switch (class_basename($parent)) {
            case "FulfilmentCustomer":
                $query->where('fulfilment_customer_id', $parent->id);
                break;
            case "Location":
                $query->where('location_id', $parent->id);
                break;
            case "Organisation":
                $query->where('organisation_id', $parent->id);
                break;
            case "Fulfilment":
                $query->where('fulfilment_id', $parent->id);
                break;

        }



        /** @noinspection PhpUndefinedMethodInspection */
        return $query->defaultSort('slug')
            ->allowedSorts(['customer_reference'])
            ->allowedFilters([$globalSearch, 'customer_reference'])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure($prefix = null): Closure
    {
        return function (InertiaTable $table) use ($prefix) {

            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            $table->withGlobalSearch()
                ->withEmptyState(
                    [
                        'title' => __("No pallets found"),
                        'count' => 0
                    ]
                )
                ->column(key: 'customer_reference', label: __('reference'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'customer_name', label: __('Customer Name'), canBeHidden: false, searchable: true)
                ->column(key: 'location', label: __('Location'), canBeHidden: false, searchable: true)
                ->column(key: 'state', label: __('State'), canBeHidden: false, searchable: true)
                ->column(key: 'status', label: __('Status'), canBeHidden: false, searchable: true)
                ->column(key: 'notes', label: __('Notes'), canBeHidden: false, searchable: true)
                ->defaultSort('slug');
        };
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo("fulfilment.{$this->fulfilment->id}.stored-items.edit");

        return $request->user()->hasPermissionTo("fulfilment.{$this->fulfilment->id}.stored-items.view");

    }


    public function jsonResponse(LengthAwarePaginator $pallets): AnonymousResourceCollection
    {
        return PalletResource::collection($pallets);
    }


    public function htmlResponse(LengthAwarePaginator $pallets, ActionRequest $request): Response
    {
        return Inertia::render(
            'Org/Fulfilment/Pallets',
            [
                'breadcrumbs' => $this->getBreadcrumbs($request->route()->originalParameters()),
                'title'       => __('pallets'),
                'pageHead'    => [
                    'title'   => __('pallets'),
                ],
                'data' => PalletResource::collection($pallets),
            ]
        )->table($this->tableStructure($pallets));
    }

    public function asController(Organisation $organisation, Fulfilment $fulfilment, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisationFromFulfilment($fulfilment, $request);
        return $this->handle($organisation, FulfilmentTabsEnum::PALLETS->value);
    }

    public function inLocation(Organisation $organisation, Location $location, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($organisation, $request);
        return $this->handle($location, FulfilmentTabsEnum::PALLETS->value);
    }

    public function inUnlocated(Organisation $organisation, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($organisation, $request);

        return $this->handle(null, FulfilmentTabsEnum::PALLETS->value);
    }

    public function getBreadcrumbs(array $routeParameters): array
    {
        return array_merge(
            ShowFulfilment::make()->getBreadcrumbs($routeParameters),
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name'       => 'grp.org.fulfilment.shops.show.pallets.index',
                            'parameters' => [
                                'organisation' => $routeParameters['organisation'],
                                'fulfilment'   => $routeParameters['fulfilment'],
                            ]
                        ],
                        'label' => __('pallets'),
                        'icon'  => 'fal fa-bars',
                    ],

                ]
            ]
        );
    }
}
