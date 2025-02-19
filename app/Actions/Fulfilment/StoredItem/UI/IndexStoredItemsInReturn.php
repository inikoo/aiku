<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 26 Jan 2024 18:40:36 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItem\UI;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithFulfilmentAuthorisation;
use App\Enums\Fulfilment\PalletReturn\PalletReturnStateEnum;
use App\Http\Resources\Fulfilment\PalletReturnStoredItemsResource;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\PalletReturn;
use App\Models\Fulfilment\StoredItem;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\InertiaTable\InertiaTable;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Organisation;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;
use App\Services\QueryBuilder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class IndexStoredItemsInReturn extends OrgAction
{
    use WithFulfilmentAuthorisation;


    private PalletReturn $palletReturn;

    private bool $selectStoredPallets = false;


    public function handle(PalletReturn $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereAnyWordStartWith('stored_items.reference', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(StoredItem::class)
            ->leftJoin('pallet_return_items', function ($join) use ($parent) {
                $join->on('stored_items.id', '=', 'pallet_return_items.stored_item_id')
                    ->where('pallet_return_items.pallet_return_id', '=', $parent->id);
            })
            ->leftJoin('pallet_returns', function ($join) use ($parent) {
                $join->on('pallet_returns.id', '=', 'pallet_return_items.pallet_return_id')
                    ->where('pallet_returns.id', '=', $parent->id);
            })
            ->leftJoin('pallet_stored_items', function ($join) {
                $join->on('stored_items.id', '=', 'pallet_stored_items.stored_item_id');
            })
            ->leftJoin('pallets', function ($join) {
                $join->on('pallets.id', '=', 'pallet_stored_items.pallet_id');
            })
            ->where('stored_items.fulfilment_customer_id', $parent->fulfilment_customer_id);

        if ($parent->state === PalletReturnStateEnum::IN_PROCESS) {
            $queryBuilder->where('stored_items.total_quantity', '>', 0);
        } else {
            $queryBuilder->where('pallet_returns.id', $parent->id);
        }

        $queryBuilder->distinct('stored_items.id')
            ->defaultSort('stored_items.id')
            ->select([
                'stored_items.id',
                'stored_items.reference',
                'stored_items.slug',
                'stored_items.name',
                'stored_items.total_quantity',
                'pallet_returns.id as pallet_return_id',
                'pallet_returns.state as pallet_return_state',
                \DB::raw(
                    '(SELECT COALESCE(SUM(quantity_ordered), 0) 
                FROM pallet_return_items pri 
                WHERE pri.stored_item_id = stored_items.id 
                AND pri.pallet_return_id = '.$parent->id.') AS total_quantity_ordered'
                ),
            ])
            ->groupBy([
                'stored_items.id',
                'stored_items.reference',
                'stored_items.slug',
                'stored_items.name',
                'stored_items.total_quantity',
                'pallet_returns.id'
            ]);

        return $queryBuilder->allowedSorts(['reference', 'code', 'price', 'name', 'state'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix, tableName: request()->route()->getName())
            ->withQueryString();
    }

    public function tableStructure(PalletReturn $palletReturn, $request, $prefix = null, $modelOperations = []): Closure
    {
        return function (InertiaTable $table) use ($prefix, $modelOperations, $request, $palletReturn) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            $emptyStateData = [

            ];


            if ($palletReturn instanceof Fulfilment) {
                $emptyStateData['description'] = __("There is no stored items this fulfilment shop");
            }
            if ($palletReturn instanceof FulfilmentCustomer) {
                $emptyStateData['description'] = __("This customer don't have any pallets");
            }

            $table->withGlobalSearch();


            $table->withEmptyState($emptyStateData)
                ->withModelOperations($modelOperations);


            $table->column(key: 'state', label: ['fal', 'fa-yin-yang'], type: 'icon');

            $table->column(key: 'reference', label: __('reference'), canBeHidden: false, sortable: true, searchable: true);
            $table->column(key: 'total_quantity', label: __('Current stock'), canBeHidden: false, sortable: true, searchable: true);
            $table->column(key: 'pallet_stored_items', label: __('Pallets [location]'), canBeHidden: false, sortable: true, searchable: true);

            $table->column(key: 'total_quantity_ordered', label: __('requested quantity'), canBeHidden: false, sortable: true, searchable: true);
            if ($palletReturn->state === PalletReturnStateEnum::PICKING) {
                $table->column(key: 'actions', label: __('action'), canBeHidden: false, sortable: true, searchable: true);
            }


            $table->defaultSort('reference');
        };
    }



    public function asController(Organisation $organisation, Warehouse $warehouse, PalletReturn $palletReturn, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisationFromWarehouse($warehouse, $request);

        return $this->handle($palletReturn);
    }

    public function jsonResponse(LengthAwarePaginator $storedItems): AnonymousResourceCollection
    {
        return PalletReturnStoredItemsResource::collection($storedItems);
    }

}
