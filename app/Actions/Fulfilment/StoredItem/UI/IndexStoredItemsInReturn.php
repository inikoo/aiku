<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 26 Jan 2024 18:40:36 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItem\UI;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HasFulfilmentAssetsAuthorisation;
use App\Enums\Fulfilment\StoredItem\StoredItemStateEnum;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Fulfilment\PalletReturn;
use App\Models\Fulfilment\StoredItem;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\InertiaTable\InertiaTable;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;
use App\Services\QueryBuilder;

class IndexStoredItemsInReturn extends OrgAction
{
    use HasFulfilmentAssetsAuthorisation;

    private PalletReturn $palletReturn;

    private bool $selectStoredPallets = false;


    public function handle(Fulfilment|FulfilmentCustomer $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereAnyWordStartWith('stored_items.reference', $value);
            });
        });

        $queryBuilder = QueryBuilder::for(StoredItem::class);

        // $queryBuilder->where('stored_items.state', StoredItemStateEnum::ACTIVE->value);

        $queryBuilder->with('pallets');

        if($parent instanceof FulfilmentCustomer) {
            $queryBuilder->where('stored_items.fulfilment_customer_id', $parent->id);
        }

        if($parent instanceof Fulfilment) {
            $queryBuilder->where('stored_items.fulfilment_id', $parent->id);
        }

        $queryBuilder->defaultSort('stored_items.id');

        return $queryBuilder->allowedSorts(['code','price','name','state'])
            ->allowedFilters([$globalSearch])
            ->withPaginator(null)
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


            $table->column(key: 'state', label: ['fal', 'fa-yin-yang'], type: 'icon');

            $table->column(key: 'reference', label: __('reference'), canBeHidden: false, sortable: true, searchable: true);

            $table->column(key: 'total_quantity', label: __('total quantity'), canBeHidden: false, sortable: true, searchable: true);
            $table->column(key: 'quantity', label: __('quantity'), canBeHidden: false, sortable: true, searchable: true);

            //            $table->column(key: 'actions', label: ' ', canBeHidden: false, searchable: true);


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

}
