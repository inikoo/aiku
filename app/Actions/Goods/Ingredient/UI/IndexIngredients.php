<?php

/*
 * author Arya Permana - Kirin
 * created on 04-12-2024-14h-07m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Goods\Ingredient\UI;

use App\Actions\Goods\HasGoodsAuthorisation;
use App\Actions\GrpAction;
use App\Actions\UI\Goods\ShowGoodsDashboard;
use App\Http\Resources\Goods\IngredientsResource;
use App\InertiaTable\InertiaTable;
use App\Models\Goods\Ingredient;
use App\Models\SysAdmin\Group;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexIngredients extends GrpAction
{
    use HasGoodsAuthorisation;

    public function asController(ActionRequest $request): LengthAwarePaginator
    {

        $this->initialisation(group(), $request);

        return $this->handle($this->group);
    }

    public function handle(Group $group, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereStartWith('ingredients.name', $value);
            });
        });
        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(Ingredient::class);
        $queryBuilder->where('ingredients.group_id', $group->id);
        /*
        foreach ($this->elementGroups as $key => $elementGroup) {
            $queryBuilder->whereElementGroup(
                key: $key,
                allowedElements: array_keys($elementGroup['elements']),
                engine: $elementGroup['engine'],
                prefix: $prefix
            );
        }
        */

        return $queryBuilder
            ->defaultSort('ingredients.name')
            ->select([
                'ingredients.slug',
                'ingredients.name',
                'ingredients.number_trade_units',
                'ingredients.number_stocks',
                'ingredients.number_master_products',
            ])
            ->allowedSorts(['name', 'number_trade_units', 'number_stocks', 'number_master_products'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(Group $parent, $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($parent, $prefix) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }
            $table
                ->withGlobalSearch()
                ->withEmptyState(
                    [
                        'title'       => __('no ingredients'),
                        'description' => $this->canEdit ? __('Get started by creating a new stock family.') : null,
                    ]
                )
                ->column(key: 'name', label: __('Name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'number_trade_units', label: __('Trade Units'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'number_stocks', label: __('Stocks'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'number_master_products', label: __('Master Products'), canBeHidden: false, sortable: true, searchable: true)
                ->defaultSort('code');
        };
    }

    public function jsonResponse(LengthAwarePaginator $ingredients): AnonymousResourceCollection
    {
        return IngredientsResource::collection($ingredients);
    }

    public function htmlResponse(LengthAwarePaginator $ingredients, ActionRequest $request): Response
    {

        $parent = $this->group;
        return Inertia::render(
            'Goods/Ingredients',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __("Ingredients"),
                'pageHead'    => [
                    'title'   => __("Ingredients"),
                    'icon'    => [
                        'title' => __("Ingredients"),
                        'icon'  => 'fal fa-boxes-alt'
                    ],
                    // 'actions' => [
                    //     $this->canEdit && $request->route()->getName() == 'grp.goods.stock-families.index' ? [
                    //         'type'    => 'button',
                    //         'style'   => 'create',
                    //         'tooltip' => __('new SKU family'),
                    //         'label'   => __('SKU family'),
                    //         'route'   => [
                    //             'name'       => 'grp.goods.stock-families.create',
                    //             'parameters' => array_values($request->route()->originalParameters())
                    //         ]
                    //     ] : false,
                    // ]
                ],
                'data' => IngredientsResource::collection($ingredients),
            ]
        )->table($this->tableStructure($parent));
    }

    public function getBreadcrumbs($suffix = null): array
    {
        return array_merge(
            ShowGoodsDashboard::make()->getBreadcrumbs(),
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name' => 'grp.goods.ingredients.index'
                        ],
                        'label' => __("Ingredients"),
                        'icon'  => 'fal fa-bars',
                    ],
                    'suffix' => $suffix

                ]
            ]
        );
    }
}
