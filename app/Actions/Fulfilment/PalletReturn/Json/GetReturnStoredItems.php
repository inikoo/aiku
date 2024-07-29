<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 11 Jul 2024 13:54:36 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletReturn\Json;

use App\Actions\OrgAction;
use App\Http\Resources\Fulfilment\ReturnStoredItemsResource;
use App\Models\CRM\WebUser;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\PalletStoredItem;
use App\Services\QueryBuilder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class GetReturnStoredItems extends OrgAction
{
    public function handle(FulfilmentCustomer $fulfilmentCustomer): LengthAwarePaginator
    {

        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereAnyWordStartWith('stored_items.reference', $value);
            });
        });



        $queryBuilder = QueryBuilder::for(PalletStoredItem::class);
        $queryBuilder->join('stored_items', 'pallet_stored_items.stored_item_id', '=', 'stored_items.id');
        $queryBuilder->join('pallets', 'pallet_stored_items.pallet_id', '=', 'pallets.id');
        $queryBuilder->join('locations', 'pallets.location_id', '=', 'locations.id');
        $queryBuilder->where('stored_items.fulfilment_customer_id', $fulfilmentCustomer->id);

        $queryBuilder
            ->defaultSort('pallet_stored_items.id')
            ->select([
                'pallet_stored_items.id',
                'pallets.id as pallet_id',
                'pallets.slug as pallet_slug',
                'pallets.reference as pallet_reference',
                'stored_items.id as stored_item_id',
                'stored_items.reference as stored_item_reference',
                'stored_items.slug as stored_item_slug',
                'stored_items.state as stored_item_state',
                'pallet_stored_items.quantity',
                'pallet_stored_items.damaged_quantity',
                'locations.code as location_code'
            ]);


        return $queryBuilder->allowedSorts(['code','price','name','state'])
            ->allowedFilters([$globalSearch])
            ->withPaginator(null)
            ->withQueryString();
    }

    //todo review this
    public function authorize(ActionRequest $request): bool
    {
        if ($request->user() instanceof WebUser) {
            return true;
        }

        $this->canEdit   = $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");
        $this->canDelete = $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");

        return $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.view");
    }

    public function asController(FulfilmentCustomer $fulfilmentCustomer, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisationFromFulfilment($fulfilmentCustomer->fulfilment, $request);

        return $this->handle($fulfilmentCustomer);
    }


    public function jsonResponse(LengthAwarePaginator $storedItems): AnonymousResourceCollection
    {
        return ReturnStoredItemsResource::collection($storedItems);
    }
}
