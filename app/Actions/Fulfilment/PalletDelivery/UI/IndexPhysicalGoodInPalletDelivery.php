<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 14 Jun 2024 22:48:02 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletDelivery\UI;

use App\Actions\OrgAction;
use App\Enums\Catalogue\Asset\AssetStateEnum;
use App\Enums\Fulfilment\FulfilmentTransaction\FulfilmentTransactionTypeEnum;
use App\Http\Resources\Fulfilment\FulfilmentTransactionResource;
use App\InertiaTable\InertiaTable;
use App\Models\Fulfilment\FulfilmentTransaction;
use App\Models\Fulfilment\PalletDelivery;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\QueryBuilder\AllowedFilter;

class IndexPhysicalGoodInPalletDelivery extends OrgAction
{
    protected function getElementGroups(PalletDelivery $palletDelivery): array
    {
        return [

            'state' => [
                'label'    => __('State'),
                'elements' => array_merge_recursive(
                    AssetStateEnum::labels(),
                    AssetStateEnum::count($palletDelivery->fulfilment->shop)
                ),

                'engine' => function ($query, $elements) {
                    $query->whereIn('state', $elements);
                }

            ],
        ];
    }

    public function handle(PalletDelivery $palletDelivery, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereAnyWordStartWith('products.name', $value)
                    ->orWhereStartWith('products.code', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(FulfilmentTransaction::class);
        $queryBuilder->where('fulfilment_transactions.parent_type', class_basename($palletDelivery));
        $queryBuilder->where('fulfilment_transactions.parent_id', $palletDelivery->id);

        $queryBuilder->where('fulfilment_transactions.type', FulfilmentTransactionTypeEnum::PRODUCT->value);
        $queryBuilder->join('assets', 'fulfilment_transactions.asset_id', '=', 'assets.id');
        $queryBuilder->join('products', 'assets.model_id', '=', 'products.id');
        $queryBuilder->join('currencies', 'products.currency_id', '=', 'currencies.id');

        foreach ($this->getElementGroups($palletDelivery) as $key => $elementGroup) {
            $queryBuilder->whereElementGroup(
                key: $key,
                allowedElements: array_keys($elementGroup['elements']),
                engine: $elementGroup['engine'],
                prefix: $prefix
            );
        }

        $queryBuilder
            ->defaultSort('products.id')
            ->select([
                'products.id',
                'products.name',
                'products.code',
                'pallet_delivery_physical_goods.pallet_delivery_id',
                'assets.price',
                'products.description',
                'currencies.code as currency_code',
                'fulfilment_transactions.quantity',
                'fulfilment_transactions.parent_id  pallet_delivery_id',
                'fulfilment_transactions.historic_asset_id',
            ]);

        return $queryBuilder->allowedSorts(['id','price','name','state'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(
        PalletDelivery $palletDelivery,
        ?array $modelOperations = null,
        $prefix = null,
        $canEdit = false
    ): Closure {
        return function (InertiaTable $table) use ($palletDelivery, $modelOperations, $prefix, $canEdit) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            $table
                ->withGlobalSearch()
                ->withModelOperations($modelOperations)
                ->withEmptyState(
                    match (class_basename($palletDelivery)) {
                        'Fulfilment' => [
                            'title' => __("No physical goods found"),
                            'count' => $palletDelivery->fulfilment->shop->stats->number_assets_type_product,
                        ],
                        'PalletDelivery' => [
                            'icons' => ['fal fa-cube'],
                            'title' => __('No physical goods selected'),
                            'count' => $palletDelivery->stats->number_physical_goods,
                        ],
                        default => null
                    }
                );

            $table
                ->column(key: 'state', label: '', canBeHidden: false, type: 'icon')
                ->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'quantity', label: __('quantity'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'price', label: __('price'), canBeHidden: false, sortable: true, searchable: true, className: 'text-right font-mono')
                ->column(key: 'actions', label: __('action'), canBeHidden: false, sortable: true, searchable: true, className: 'hello')
                ->defaultSort('code');
        };
    }


    public function jsonResponse(LengthAwarePaginator $physicalGoods): AnonymousResourceCollection
    {
        return FulfilmentTransactionResource::collection($physicalGoods);
    }
}
