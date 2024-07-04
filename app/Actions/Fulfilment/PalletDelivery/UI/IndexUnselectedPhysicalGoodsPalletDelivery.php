<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 14 Jun 2024 22:48:17 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletDelivery\UI;

use App\Actions\OrgAction;
use App\Enums\Catalogue\Product\ProductStateEnum;
use App\Enums\UI\Fulfilment\PhysicalGoodsTabsEnum;
use App\Http\Resources\Fulfilment\PhysicalGoodsResource;
use App\InertiaTable\InertiaTable;
use App\Models\Catalogue\Product;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexUnselectedPhysicalGoodsPalletDelivery extends OrgAction
{
    protected function getElementGroups(Fulfilment $parent): array
    {
        return [

            'state' => [
                'label'    => __('State'),
                'elements' => array_merge_recursive(
                    ProductStateEnum::labels(),
                    ProductStateEnum::count($parent->shop)
                ),

                'engine' => function ($query, $elements) {
                    $query->whereIn('state', $elements);
                }

            ],
        ];
    }


    public function handle(Fulfilment  $fulfilment, PalletDelivery $palletDelivery, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereAnyWordStartWith('services.name', $value)
                    ->orWhereStartWith('services.code', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(Product::class);
        $queryBuilder->where('products.shop_id', $fulfilment->shop_id);
        $queryBuilder->join('currencies', 'products.currency_id', '=', 'currencies.id');

        $queryBuilder->whereNotIn('products.id', $palletDelivery->physicalGoods()->pluck('outer_id'));

        foreach ($this->getElementGroups($fulfilment) as $key => $elementGroup) {
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
                'products.slug',
                'products.name',
                'products.code',
                'products.state',
                'products.created_at',
                'products.price',
                'products.unit',
                'currencies.code as currency_code',
            ]);

        return $queryBuilder->allowedSorts(['id','price','name','state'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function asController(Organisation $organisation, Fulfilment $fulfilment, PalletDelivery $palletDelivery, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisationFromFulfilment($fulfilment, $request)->withTab(PhysicalGoodsTabsEnum::values());

        return $this->handle($fulfilment, $palletDelivery, PhysicalGoodsTabsEnum::PHYSICAL_GOODS->value);
    }

    public function fromRetina(PalletDelivery $palletDelivery, ActionRequest $request): LengthAwarePaginator
    {
        /** @var FulfilmentCustomer $fulfilmentCustomer */
        $fulfilmentCustomer = $request->user()->customer->fulfilmentCustomer;
        $this->fulfilment   = $fulfilmentCustomer->fulfilment;

        $this->initialisation($request->get('website')->organisation, $request);
        return $this->handle($this->fulfilment, $palletDelivery, PhysicalGoodsTabsEnum::PHYSICAL_GOODS->value);
    }

    public function jsonResponse(LengthAwarePaginator $physicalGoods): AnonymousResourceCollection
    {
        return PhysicalGoodsResource::collection($physicalGoods);
    }
}
