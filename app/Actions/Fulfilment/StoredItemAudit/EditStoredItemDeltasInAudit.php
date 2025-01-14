<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 14 Jan 2025 12:52:25 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItemAudit;

use App\Actions\Fulfilment\WithFulfilmentCustomerSubNavigation;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HasFulfilmentAssetsAuthorisation;
use App\Enums\Fulfilment\Pallet\PalletStateEnum;
use App\Enums\Fulfilment\Pallet\PalletStatusEnum;
use App\InertiaTable\InertiaTable;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\Pallet;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;

class EditStoredItemDeltasInAudit extends OrgAction
{
    use HasFulfilmentAssetsAuthorisation;
    use WithFulfilmentCustomerSubNavigation;


    private bool $selectStoredPallets = false;

    private FulfilmentCustomer $parent;

    protected function getElementGroups(FulfilmentCustomer $fulfilmentCustomer, string $prefix): array
    {
        $elements = [];

        if ($prefix == 'all') {
            $elements = [
                'status' => [
                    'label'    => __('Status'),
                    'elements' => array_merge_recursive(
                        PalletStatusEnum::labels($fulfilmentCustomer),
                        PalletStatusEnum::count($fulfilmentCustomer)
                    ),

                    'engine' => function ($query, $elements) {
                        $query->whereIn('pallets.status', $elements);
                    }
                ],


            ];
        }

        return $elements;
    }

    public function handle(FulfilmentCustomer $fulfilmentCustomer, $prefix = null): LengthAwarePaginator
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


        $query->where('fulfilment_customer_id', $fulfilmentCustomer->id);
        $query->where('pallets.status', PalletStatusEnum::STORING);
        $query->where('pallets.state', PalletStateEnum::STORING);

        $query->leftJoin('stored_item_audit_deltas', 'pallets.id', '=', 'stored_item_audit_deltas.pallet_id');

        foreach ($this->getElementGroups($fulfilmentCustomer, $prefix) as $key => $elementGroup) {
            $query->whereElementGroup(
                key: $key,
                allowedElements: array_keys($elementGroup['elements']),
                engine: $elementGroup['engine'],
                prefix: $prefix
            );
        }


        $query->whereNotNull('pallets.slug');


        $query->defaultSort('pallets.id')
            ->select(
                'pallets.id',
                'pallets.slug',
                'pallets.reference',
                'pallets.customer_reference',
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
                'stored_item_audit_deltas.audited_at'
            );


        return $query->allowedSorts(['customer_reference', 'reference', 'fulfilment_customer_name'])
            ->allowedFilters([$globalSearch, 'customer_reference', 'reference'])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(FulfilmentCustomer $fulfilmentCustomer, $prefix = null, $modelOperations = []): Closure
    {
        return function (InertiaTable $table) use ($prefix, $modelOperations, $fulfilmentCustomer) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            foreach ($this->getElementGroups($fulfilmentCustomer, $prefix) as $key => $elementGroup) {
                $table->elementGroup(
                    key: $key,
                    label: $elementGroup['label'],
                    elements: $elementGroup['elements']
                );
            }


            $emptyStateData = [
                'icons'       => ['fal fa-pallet'],
                'title'       => __('No pallets found'),
                'count'       => $fulfilmentCustomer->number_pallets,
                'description' => __("This customer don't have any pallets")
            ];


            $table->withGlobalSearch();


            $table->withEmptyState($emptyStateData)
                ->withModelOperations($modelOperations);


            $table->column(key: 'state', label: ['fal', 'fa-yin-yang'], type: 'icon');
            $table->column(key: 'reference', label: __('pallet reference'), canBeHidden: false, sortable: true, searchable: true);
            $table->column(key: 'customer_reference', label: __("Pallet customer's reference"), canBeHidden: false, sortable: true, searchable: true);
            $table->column(key: 'stored_items', label: __("Customer's SKUs"), canBeHidden: false);

            $table->defaultSort('reference');
        };
    }



}
