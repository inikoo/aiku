<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 26 Jan 2024 18:40:36 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItem\UI;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HasFulfilmentAssetsAuthorisation;
use App\Http\Resources\Fulfilment\PalletsResource;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Fulfilment\PalletReturn;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Organisation;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\InertiaTable\InertiaTable;
use App\Models\Fulfilment\PalletReturnItem;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;
use App\Services\QueryBuilder;

class IndexStoredItemsInReturn extends OrgAction
{
    use HasFulfilmentAssetsAuthorisation;

    private PalletReturn $palletReturn;

    private bool $selectStoredPallets = false;


    public function handle(PalletReturn $palletReturn, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereAnyWordStartWith('stored_items.reference', $value);
            });
        });


        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $query = QueryBuilder::for(PalletReturnItem::class);


        $query->where('pallet_return_items.pallet_return_id', $palletReturn->id);
        $query->join('pallets', 'pallet_return_items.pallet_id', '=', 'pallets.id');
        $query->join('stored_items', 'pallet_return_items.stored_item_id', '=', 'stored_items.id');


        $query->join('locations', 'pallets.location_id', '=', 'locations.id');


        $query->defaultSort('stored_items.id')
            ->select(
                'pallet_return_items.id',
                'stored_items.id as stored_item_id',
                'pallets.id as pallet_id',
                'pallets.location_id',
                'stored_items.slug',
                'stored_items.reference',
                'stored_items.notes',
                'stored_items.state',
                'stored_items.status',
                'stored_items.type',
                'stored_items.received_at',
                'stored_items.fulfilment_customer_id',
                'stored_items.pallet_return_id',
                'locations.slug as location_slug',
                'locations.slug as location_code'
            );


        return $query->allowedSorts(['reference', 'id'])
            ->allowedFilters([$globalSearch, 'id', 'reference'])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(PalletReturn $palletReturn, $prefix = null, $request, $modelOperations = []): Closure
    {
        return function (InertiaTable $table) use ($prefix, $modelOperations, $request, $palletReturn) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            $emptyStateData = [
                'icons' => ['fal fa-pallet'],
                'title' => '',
                'count' => match (class_basename($palletReturn)) {
                    'FulfilmentCustomer' => $palletReturn->number_stored_items,
                    default              => $palletReturn->stats->number_stored_items
                }
            ];


            if ($palletReturn instanceof Fulfilment) {
                $emptyStateData['description'] = __("There is no stored items this fulfilment shop");
            }
            if ($palletReturn instanceof FulfilmentCustomer) {
                $emptyStateData['description'] = __("This customer don't have any pallets");
            }

            if (!$palletReturn instanceof PalletDelivery and !$palletReturn instanceof PalletReturn) {
                $table->withGlobalSearch();
            }

            $table->withEmptyState($emptyStateData)
                ->withModelOperations($modelOperations);


            $table->column(key: 'type_icon', label: ['fal', 'fa-yin-yang'], type: 'icon');


            /* $table->column(key: 'state', label: ['fal', 'fa-yin-yang'], type: 'icon'); */


            $table->column(key: 'reference', label: __('reference'), canBeHidden: false, sortable: true, searchable: true);

            $table->column(key: 'actions', label: ' ', canBeHidden: false, searchable: true);


            $table->defaultSort('reference');
        };
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo('org-supervisor.'.$this->organisation->id);

        return $request->user()->hasAnyPermission(
            [
                'org-supervisor.'.$this->organisation->id,
                'warehouses-view.'.$this->organisation->id
            ]
        );
    }

    // public function jsonResponse(LengthAwarePaginator $pallets): AnonymousResourceCollection
    // {
    //     return PalletsResource::collection($pallets);
    // }

    // public function asController(Organisation $organisation, Warehouse $warehouse, PalletReturn $palletReturn, ActionRequest $request): LengthAwarePaginator
    // {
    //     $this->initialisationFromWarehouse($warehouse, $request);

    //     return $this->handle($palletReturn);
    // }
}
