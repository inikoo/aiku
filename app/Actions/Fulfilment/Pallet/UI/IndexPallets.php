<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 May 2023 21:14:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Pallet\UI;

use App\Actions\InertiaAction;
use App\Actions\UI\Fulfilment\ShowFulfilmentsDashboard;
use App\Actions\OrgAction;
use App\Enums\UI\FulfilmentTabsEnum;
use App\Enums\UI\TabsAbbreviationEnum;
use App\Http\Resources\Fulfilment\PalletResource;
use App\Models\CRM\Customer;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\StoredItem;
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
    /** @noinspection PhpUndefinedMethodInspection */
    public function handle(Organisation|Customer|Location|null $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('customer_reference', 'ILIKE', "%$value%");
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        return QueryBuilder::for(Pallet::class)
            ->defaultSort('slug')
            ->with('customer')
            ->when($parent, function ($query) use ($parent) {
                if(class_basename($parent) == "Customer") {
                    $query->where('customer_id', $parent->id);
                }
                if(class_basename($parent) == "Location") {
                    $query->where('location_id', $parent->id);
                }
                if($parent == null) {
                    $query->where('location_id', null);
                }
            })
            ->allowedSorts(['customer_reference'])
            ->allowedFilters([$globalSearch, 'customer_reference'])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure($prefix = null): Closure
    {
        return function (InertiaTable $table) use ($prefix) {
            $table
                ->name($prefix)
                ->pageName($prefix.'Page')

                ->withGlobalSearch()
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
        $this->canEdit = $request->user()->hasPermissionTo('fulfilment.edit');

        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('hr.view')
            );
    }


    public function jsonResponse(LengthAwarePaginator $pallets): AnonymousResourceCollection
    {
        return PalletResource::collection($pallets);
    }


    public function htmlResponse(LengthAwarePaginator $pallets): Response
    {
        return Inertia::render(
            'Fulfilment/Pallets',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('pallets'),
                'pageHead'    => [
                    'title'   => __('pallets'),
                    'actions' => [
                        'buttons' => [
                            'route' => [
                                'name'       => 'grp.org.fulfilment.shops.show.pallets.create',
                                'parameters' => array_values(request()->route()->originalParameters())
                            ],
                            'label' => __('pallets')
                        ]
                    ],
                ],
                'data' => PalletResource::collection($pallets),
            ]
        )->table($this->tableStructure($pallets));
    }

    public function asController(Organisation $organisation, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($organisation, $request);

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

    public function getBreadcrumbs(): array
    {
        return array_merge(
            (new ShowFulfilmentsDashboard())->getBreadcrumbs(),
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name' => 'grp.fulfilment.stored-items.index'
                        ],
                        'label' => __('stored items'),
                        'icon'  => 'fal fa-bars',
                    ],

                ]
            ]
        );
    }
}
