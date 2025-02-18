<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 26 Jan 2024 18:40:36 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Pallet\UI;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HasFulfilmentAssetsAuthorisation;
use App\Enums\Fulfilment\PalletDelivery\PalletDeliveryStateEnum;
use App\Enums\Fulfilment\PalletReturn\PalletReturnStateEnum;
use App\Http\Resources\Fulfilment\PalletsResource;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Fulfilment\PalletReturn;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Organisation;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\InertiaTable\InertiaTable;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;
use App\Services\QueryBuilder;

class IndexPalletsInDelivery extends OrgAction
{
    use HasFulfilmentAssetsAuthorisation;

    private PalletDelivery $palletDelivery;


    public function handle(PalletDelivery $palletDelivery, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereAnyWordStartWith('pallets.customer_reference', $value)
                    ->orWhereWith('pallets.reference', $value);
            });
        });


        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $query = QueryBuilder::for(Pallet::class);

        $query->where('pallet_delivery_id', $palletDelivery->id);

        $query->leftJoin('locations', 'locations.id', 'pallets.location_id');

        $query->defaultSort('pallets.id')
            ->select(
                'pallets.id',
                'pallets.slug',
                'pallets.reference',
                'pallets.customer_reference',
                'pallets.notes',
                'pallets.state',
                'pallets.status',
                'pallets.rental_id',
                'pallets.type',
                'pallets.received_at',
                'pallets.location_id',
                'pallets.fulfilment_customer_id',
                'pallets.warehouse_id',
                'pallets.pallet_delivery_id',
                'pallets.pallet_return_id',
                'locations.slug as location_slug',
                'locations.slug as location_code',
            );


        return $query->allowedSorts(['customer_reference', 'reference', 'fulfilment_customer_name','type'])
            ->allowedFilters([$globalSearch, 'customer_reference', 'reference'])
            ->withPaginator($prefix, tableName: request()->route()->getName())
            ->withQueryString();
    }

    public function jsonResponse(LengthAwarePaginator $pallets): AnonymousResourceCollection
    {
        return PalletsResource::collection($pallets);
    }

    public function tableStructure(PalletDelivery $palletDelivery, $prefix = null, $modelOperations = []): Closure
    {
        return function (InertiaTable $table) use ($prefix, $modelOperations, $palletDelivery) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }


            $emptyStateData = [
                'icons' => ['fal fa-pallet'],
                'title' => __('No pallet in this delivery'),
                'count' => $palletDelivery->stats->number_pallets
            ];


            $table->withGlobalSearch();

            $table->withEmptyState($emptyStateData)
                ->withModelOperations($modelOperations);

            if ($palletDelivery->state == PalletDeliveryStateEnum::IN_PROCESS) {
                $table->column(key: 'type', label: __('type'), canBeHidden: false, sortable: true, searchable: true);
            } else {
                $table->column(key: 'type_icon', label: ['fal', 'fa-yin-yang'], type: 'avatar');
            }

            /*  if (!($palletDelivery instanceof PalletDelivery and $palletDelivery->state == PalletDeliveryStateEnum::IN_PROCESS)) {
                 $table->column(key: 'state', label: ['fal', 'fa-yin-yang'], type: 'icon');
             } */

            if ($palletDelivery instanceof Organisation || $palletDelivery instanceof Fulfilment) {
                $table->column(key: 'fulfilment_customer_name', label: __('Customer'), canBeHidden: false, sortable: true, searchable: true);
            }

            if (!($palletDelivery instanceof PalletDelivery and $palletDelivery->state == PalletDeliveryStateEnum::IN_PROCESS || $palletDelivery->state == PalletDeliveryStateEnum::SUBMITTED)) {
                $table->column(key: 'reference', label: __('reference'), canBeHidden: false, sortable: true, searchable: true);
            }


            $customersReferenceLabel = __("Pallet reference (customer's), notes");
            if (
                ($palletDelivery instanceof PalletDelivery and $palletDelivery->state == PalletDeliveryStateEnum::IN_PROCESS) or ($palletDelivery instanceof PalletReturn and $palletDelivery->state == PalletReturnStateEnum::IN_PROCESS)
            ) {
                $customersReferenceLabel = __('Customer Reference');
            }


            $table->column(key: 'customer_reference', label: $customersReferenceLabel, canBeHidden: false, sortable: true, searchable: true);


            if (
                ($palletDelivery instanceof PalletDelivery and $palletDelivery->state == PalletDeliveryStateEnum::IN_PROCESS) or ($palletDelivery instanceof PalletReturn and $palletDelivery->state == PalletReturnStateEnum::IN_PROCESS)
            ) {
                $table->column(key: 'notes', label: __('Notes'), canBeHidden: false, searchable: true);
            }



            if ($palletDelivery->state == PalletDeliveryStateEnum::BOOKING_IN or $palletDelivery->state == PalletDeliveryStateEnum::BOOKED_IN) {
                $table->column(key: 'location', label: __('Location'), canBeHidden: false, searchable: true);
                $table->column(key: 'rental', label: __('Rental'), canBeHidden: false, searchable: true);

            }


            if ($palletDelivery->fulfilmentCustomer->items_storage) {
                $table->column(key: 'stored_items', label: 'Stored Items', canBeHidden: false, searchable: true);
            }


            if (
                !(
                    ($palletDelivery instanceof PalletDelivery and in_array($palletDelivery->state, [PalletDeliveryStateEnum::BOOKED_IN, PalletDeliveryStateEnum::RECEIVED])) or
                    ($palletDelivery instanceof PalletReturn and ($palletDelivery->state == PalletReturnStateEnum::DISPATCHED or $palletDelivery->state == PalletReturnStateEnum::CANCEL))
                )
            ) {

                $table->column(key: 'actions', label: ' ', canBeHidden: false, searchable: true);
            }


            $table->defaultSort('reference');
        };
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->authTo('org-supervisor.'.$this->organisation->id);

        return $request->user()->authTo(
            [
                'org-supervisor.'.$this->organisation->id,
                'warehouses-view.'.$this->organisation->id]
        );
    }

    public function asController(Organisation $organisation, Warehouse $warehouse, PalletDelivery $palletDelivery, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisationFromWarehouse($warehouse, $request);

        return $this->handle($palletDelivery);
    }
}
