<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 14 Jun 2024 22:48:02 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletDelivery\UI;

use App\Actions\OrgAction;
use App\Enums\Catalogue\Asset\AssetStateEnum;
use App\Http\Resources\Fulfilment\PhysicalGoodsResource;
use App\InertiaTable\InertiaTable;
use App\Models\Fulfilment\PalletDelivery;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\QueryBuilder\AllowedFilter;

class IndexPhysicalGoodInPalletDelivery extends OrgAction
{
    protected function getElementGroups(PalletDelivery $parent): array
    {
        return [

            'state' => [
                'label'    => __('State'),
                'elements' => array_merge_recursive(
                    AssetStateEnum::labels(),
                    AssetStateEnum::count($parent->fulfilment->shop)
                ),

                'engine' => function ($query, $elements) {
                    $query->whereIn('state', $elements);
                }

            ],
        ];
    }

    public function handle(PalletDelivery $parent, $prefix = null): LengthAwarePaginator
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

        $queryBuilder = QueryBuilder::for($parent->physicalGoods());
        $queryBuilder->join('assets', 'products.asset_id', '=', 'assets.id');
        $queryBuilder->join('currencies', 'assets.currency_id', '=', 'currencies.id');

        foreach ($this->getElementGroups($parent) as $key => $elementGroup) {
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
                'assets.price',
                'products.description',
                'currencies.code as currency_code',
                'pallet_delivery_physical_goods.quantity',
                'pallet_delivery_physical_goods.pallet_delivery_id'
            ]);

        return $queryBuilder->allowedSorts(['id','price','name','state'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(
        PalletDelivery $parent,
        ?array $modelOperations = null,
        $prefix = null,
        $canEdit = false
    ): Closure {
        return function (InertiaTable $table) use ($parent, $modelOperations, $prefix, $canEdit) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            $table
                ->withGlobalSearch()
                ->withModelOperations($modelOperations)
                ->withEmptyState(
                    match (class_basename($parent)) {
                        'Fulfilment' => [
                            'title' => __("No physical goods found"),
                            'count' => $parent->fulfilment->shop->stats->number_assets_type_product,
                        ],
                        'PalletDelivery' => [
                            'icons' => ['fal fa-cube'],
                            'title' => __('No physical goods selected'),
                            'count' => $parent->stats->number_physical_goods,
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
        return PhysicalGoodsResource::collection($physicalGoods);
    }
}
