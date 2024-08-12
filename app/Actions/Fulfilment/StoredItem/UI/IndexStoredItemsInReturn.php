<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 26 Jan 2024 18:40:36 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItem\UI;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HasFulfilmentAssetsAuthorisation;
use App\Enums\Fulfilment\PalletReturn\PalletReturnStateEnum;
use App\Enums\Fulfilment\StoredItem\StoredItemInReturnOptionEnum;
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


    protected function getElementGroups(PalletReturn $palletReturn): array
    {
        return [
            'option' => [
                'label'    => __('Option'),
                'elements' => array_merge_recursive(
                    StoredItemInReturnOptionEnum::labels(),
                    StoredItemInReturnOptionEnum::count()
                ),
                'engine' => function ($query, $elements) use ($palletReturn) {
                    if (in_array(StoredItemInReturnOptionEnum::SELECTED->value, $elements)) {
                        $query->whereHas('palletReturns', function ($query) use ($palletReturn) {
                            $query->where('pallet_return_id', $palletReturn->id);
                        });
                    } elseif (in_array(StoredItemInReturnOptionEnum::UNSELECTED->value, $elements)) {
                        $query->whereDoesntHave('palletReturns', function ($query) use ($palletReturn) {
                            $query->where('pallet_return_id', $palletReturn->id);
                        });
                    }
                }
            ],
        ];
    }

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

        $queryBuilder = QueryBuilder::for(StoredItem::class);

        // $queryBuilder->where('stored_items.state', StoredItemStateEnum::ACTIVE->value);

        $queryBuilder->with(['pallets', 'palletReturns']);
        $queryBuilder->where('stored_items.fulfilment_customer_id', $parent->fulfilment_customer_id);

        if ($parent->state === PalletReturnStateEnum::IN_PROCESS) {
            foreach ($this->getElementGroups($parent) as $key => $elementGroup) {
                $queryBuilder->whereElementGroup(
                    key: $key,
                    allowedElements: array_keys($elementGroup['elements']),
                    engine: $elementGroup['engine'],
                    prefix: $prefix
                );
            }
        } else {
            $queryBuilder->whereHas('palletReturns', function ($query) use ($parent) {
                $query->where('pallet_return_id', $parent->id);
            });
        }

        $queryBuilder->defaultSort('stored_items.id');

        return $queryBuilder->allowedSorts(['reference', 'code', 'price', 'name', 'state'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(PalletReturn $palletReturn, $request, $prefix = null, $modelOperations = []): Closure
    {
        return function (InertiaTable $table) use ($prefix, $modelOperations, $request, $palletReturn) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix . 'Page');
            }

            $emptyStateData = [
                /*'icons' => ['fal fa-pallet'],
                'title' => '',
                'count' => match (class_basename($palletReturn)) {
                    'FulfilmentCustomer' => $palletReturn->number_stored_items,
                    default              => $palletReturn->stats->number_stored_items
                }*/
            ];

            if ($palletReturn->state === PalletReturnStateEnum::IN_PROCESS) {
                foreach ($this->getElementGroups($palletReturn) as $key => $elementGroup) {
                    $table->elementGroup(
                        key: $key,
                        label: $elementGroup['label'],
                        elements: $elementGroup['elements']
                    );
                }
            }

            if ($palletReturn instanceof Fulfilment) {
                $emptyStateData['description'] = __("There is no stored items this fulfilment shop");
            }
            if ($palletReturn instanceof FulfilmentCustomer) {
                $emptyStateData['description'] = __("This customer don't have any pallets");
            }

            //            if (!$palletReturn instanceof PalletDelivery and !$palletReturn instanceof PalletReturn) {
            $table->withGlobalSearch();
            //            }

            $table->withEmptyState($emptyStateData)
                ->withModelOperations($modelOperations);


            $table->column(key: 'state', label: ['fal', 'fa-yin-yang'], type: 'icon');

            $table->column(key: 'reference', label: __('reference'), canBeHidden: false, sortable: true, searchable: true);

            if ($palletReturn->state === PalletReturnStateEnum::IN_PROCESS) {
                $table->column(key: 'total_quantity', label: __('total quantity'), canBeHidden: false, sortable: true, searchable: true);
            }

            $table->column(key: 'quantity', label: __('quantity'), canBeHidden: false, sortable: true, searchable: true);

            //            $table->column(key: 'actions', label: ' ', canBeHidden: false, searchable: true);


            $table->defaultSort('reference');
        };
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo('org-supervisor.' . $this->organisation->id);

        return $request->user()->hasAnyPermission(
            [
                'org-supervisor.' . $this->organisation->id,
                'warehouses-view.' . $this->organisation->id
            ]
        );
    }

}
