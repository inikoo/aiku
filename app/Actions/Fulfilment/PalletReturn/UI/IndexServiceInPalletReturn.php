<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 15 Jun 2024 00:09:49 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletReturn\UI;

use App\Actions\OrgAction;
use App\Enums\Catalogue\Service\ServiceStateEnum;
use App\Enums\Fulfilment\FulfilmentTransaction\FulfilmentTransactionTypeEnum;
use App\Http\Resources\Fulfilment\ServicesResource;
use App\InertiaTable\InertiaTable;
use App\Models\Fulfilment\FulfilmentTransaction;
use App\Models\Fulfilment\PalletReturn;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\QueryBuilder\AllowedFilter;

class IndexServiceInPalletReturn extends OrgAction
{
    protected function getElementGroups(PalletReturn $palletReturn): array
    {
        return [

            'state' => [
                'label'    => __('State'),
                'elements' => array_merge_recursive(
                    ServicestateEnum::labels(),
                    ServicestateEnum::count($palletReturn->fulfilment->shop)
                ),

                'engine' => function ($query, $elements) {
                    $query->whereIn('state', $elements);
                }

            ],
        ];
    }

    public function handle(PalletReturn $palletReturn, $prefix = null): LengthAwarePaginator
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

        $queryBuilder = QueryBuilder::for(FulfilmentTransaction::class);
        $queryBuilder->where('fulfilment_transactions.parent_type', class_basename($palletReturn));
        $queryBuilder->where('fulfilment_transactions.parent_id', $palletReturn->id);
        $queryBuilder->where('fulfilment_transactions.type', FulfilmentTransactionTypeEnum::SERVICE->value);
        $queryBuilder->where('fulfilment_transactions.type', FulfilmentTransactionTypeEnum::SERVICE->value);

        $queryBuilder->join('assets', 'fulfilment_transactions.asset_id', '=', 'assets.id');
        $queryBuilder->join('services', 'assets.model_id', '=', 'services.id');
        $queryBuilder->join('currencies', 'services.currency_id', '=', 'currencies.id');




        foreach ($this->getElementGroups($palletReturn) as $key => $elementGroup) {
            $queryBuilder->whereElementGroup(
                key: $key,
                allowedElements: array_keys($elementGroup['elements']),
                engine: $elementGroup['engine'],
                prefix: $prefix
            );
        }

        $queryBuilder
            ->defaultSort('services.id')
            ->select([
                'services.id',
                'services.state',
                'services.created_at',
                'services.price',
                'services.unit',
                'assets.name',
                'assets.code',
                'assets.price',
                'services.description',
                'currencies.code as currency_code',
                'fulfilment_transactions.quantity'
            ]);


        return $queryBuilder->allowedSorts(['id','price','name','state'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(
        PalletReturn $palletReturn,
        ?array $modelOperations = null,
        $prefix = null,
        $canEdit = false
    ): Closure {
        return function (InertiaTable $table) use ($palletReturn, $modelOperations, $prefix, $canEdit) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            $table
                ->withGlobalSearch()
                ->withModelOperations($modelOperations)
                ->withEmptyState(
                    [
                        'icons' => ['fal fa-concierge-bell'],
                        'title' => '',
                        'count' => $palletReturn->stats->number_services,
                    ]
                );

            $table
                ->column(key: 'state', label: '', canBeHidden: false, type: 'icon')
                ->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'quantity', label: __('quantity'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'price', label: __('price'), canBeHidden: false, sortable: true, searchable: true, className: 'text-right font-mono')
                ->column(key: 'workflow', label: __('workflow'), canBeHidden: false, sortable: true, searchable: true, className: 'hello')
                ->column(key: 'total', label: __('total'), canBeHidden: false, sortable: true, searchable: true, className: 'text-right font-mono')
                ->column(key: 'action', label: __('action'), canBeHidden: false, sortable: true, searchable: true, className: 'hello')
                ->defaultSort('code');
        };
    }


    public function jsonResponse(LengthAwarePaginator $services): AnonymousResourceCollection
    {
        return ServicesResource::collection($services);
    }
}
