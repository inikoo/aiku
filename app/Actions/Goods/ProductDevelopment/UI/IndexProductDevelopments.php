<?php

/*
 * Author Louis Perez
 * Created on 15-10-2026-18h-05m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Actions\Goods\ProductDevelopment\UI;

use App\Actions\Goods\ProductDevelopment\UI\Traits\WithProductDevelopmentsIndex;
use App\Actions\Goods\ProductDevelopment\UI\Traits\WithProductDevelopmentsQuery;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithProductDevelopmentAuthorisation;
use App\Http\Resources\Goods\ProductDevelopmentsResource;
use App\InertiaTable\InertiaTable;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\User;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedSort;

class IndexProductDevelopments extends OrgAction
{
    use WithProductDevelopmentAuthorisation;
    use WithProductDevelopmentsQuery;
    use WithProductDevelopmentsIndex;

    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisationFromGroup(group(), $request);

        return $this->handle($this->group, $request->user());
    }

    public function handle(Group $group, User $user, ?string $prefix = null): LengthAwarePaginator
    {
        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        return $this->productDevelopmentsQuery($group, $user)
            ->defaultSort('-id')
            ->allowedSorts([
                AllowedSort::field('id', 'product_developments.id'),
                AllowedSort::field('code', 'product_developments.code'),
                AllowedSort::field('name', 'product_developments.name'),
                AllowedSort::field('stage_position', 'product_developments.current_position'),
                AllowedSort::field('number_required_missing', 'product_development_stats.number_required_missing'),
                AllowedSort::field('target_online_date', 'product_developments.target_online_date'),
            ])
            ->withPaginator($prefix, tableName: request()->route()->getName())
            ->withQueryString();
    }

    public function tableStructure(?string $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($prefix) {
            if ($prefix) {
                $table->name($prefix)->pageName($prefix.'Page');
            }

            $table
                ->withEmptyState([
                    'title'       => __('No product developments yet'),
                    'description' => __('Start one for each new or redesigned product family'),
                ])
                ->column(key: 'status', label: '', type: 'icon', canBeHidden: false)
                ->column(key: 'code', label: __('Code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('Family'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'brand', label: __('Brand'))
                ->column(key: 'supplier', label: __('Supplier / agent'))
                ->column(key: 'stage_position', label: __('Stage'), sortable: true)
                ->column(key: 'number_required_missing', label: __('Missing'), sortable: true, align: 'right')
                ->column(key: 'owner', label: __('Owner'))
                ->column(key: 'target_online_date', label: __('Target online'), sortable: true)
                ->column(key: 'days_in_stage', label: __('Days in stage'), align: 'right')
                ->defaultSort('-id');
        };
    }

    public function jsonResponse(LengthAwarePaginator $productDevelopments): AnonymousResourceCollection
    {
        return ProductDevelopmentsResource::collection($productDevelopments);
    }

    public function htmlResponse(LengthAwarePaginator $productDevelopments, ActionRequest $request): Response
    {
        return Inertia::render(
            'Goods/ProductDevelopments',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('Product development'),
                'pageHead'    => [
                    'title'   => __('Product development'),
                    'icon'    => [
                        'title' => __('Product development'),
                        'icon'  => 'fal fa-lightbulb-on',
                    ],
                    'actions' => [
                        $this->createAction(),
                    ],
                ],
                'viewSwitch'  => $this->viewSwitch('list'),
                'filterBar'   => $this->productDevelopmentFilterBar($this->group, $request->user()),
                'data'        => ProductDevelopmentsResource::collection($productDevelopments),
            ]
        )->table($this->tableStructure());
    }
}
