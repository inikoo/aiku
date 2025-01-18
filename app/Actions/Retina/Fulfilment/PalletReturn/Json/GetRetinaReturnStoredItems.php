<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 11 Jul 2024 13:54:36 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Retina\Fulfilment\PalletReturn\Json;

use App\Actions\RetinaAction;
use App\Enums\Fulfilment\StoredItem\StoredItemStateEnum;
use App\Http\Resources\Fulfilment\ReturnStoredItemsResource;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\PalletReturn;
use App\Models\Fulfilment\StoredItem;
use App\Services\QueryBuilder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class GetRetinaReturnStoredItems extends RetinaAction
{
    public function handle(FulfilmentCustomer|Fulfilment $parent, PalletReturn $scope): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereAnyWordStartWith('stored_items.reference', $value);
            });
        });

        $queryBuilder = QueryBuilder::for(StoredItem::class);

        $queryBuilder->join('pallet_stored_items', 'pallet_stored_items.stored_item_id', '=', 'stored_items.id');
        $queryBuilder->join('pallets', 'pallet_stored_items.pallet_id', '=', 'pallets.id');
        $queryBuilder->join('locations', 'pallets.location_id', '=', 'locations.id');

        $queryBuilder->where('stored_items.state', StoredItemStateEnum::ACTIVE->value);

        if ($parent instanceof FulfilmentCustomer) {
            $queryBuilder->where('stored_items.fulfilment_customer_id', $parent->id);
        }

        if ($parent instanceof Fulfilment) {
            $queryBuilder->where('stored_items.fulfilment_id', $parent->id);
        }

        $queryBuilder
            ->defaultSort('stored_items.id')
            ->select([
                'stored_items.id',
                'pallets.id as pallet_id',
                'pallets.slug as pallet_slug',
                'pallets.reference as pallet_reference',
                'stored_items.id as stored_item_id',
                'stored_items.reference as stored_item_reference',
                'stored_items.slug as stored_item_slug',
                'stored_items.state as stored_item_state',
                'locations.code as location_code'
            ]);


        return $queryBuilder->allowedSorts(['code','price','name','state'])
            ->allowedFilters([$globalSearch])
            ->withPaginator(null)
            ->withQueryString();
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->customer->id == $request->route()->parameter('fulfilmentCustomer')->customer_id) {
            return true;
        }

        return false;
    }

    public function asController(FulfilmentCustomer $fulfilmentCustomer, PalletReturn $palletReturn, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);

        return $this->handle($fulfilmentCustomer, $palletReturn);
    }

    public function jsonResponse(LengthAwarePaginator $storedItems): AnonymousResourceCollection
    {
        return ReturnStoredItemsResource::collection($storedItems);
    }
}
