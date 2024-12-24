<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 24-12-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\SysAdmin\Group\UI;

use App\Actions\GrpAction;
use App\Actions\SysAdmin\User\Traits\WithFormattedUserHistories;
use App\InertiaTable\InertiaTable;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Pagination\LengthAwarePaginator;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use OwenIt\Auditing\Models\Audit;
use Spatie\QueryBuilder\AllowedFilter;
use App\Actions\UI\Dashboards\ShowGroupDashboard;
use Inertia\Inertia;
use Inertia\Response;

class IndexHistoryInGroup extends GrpAction
{
    use AsAction;
    use WithAttributes;
    use WithFormattedUserHistories;

    public function handle($model, $prefix = null): LengthAwarePaginator|array|bool
    {
        $this->model = class_basename($model);

        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereAnyWordStartWith('user_type', $value)
                    ->orWhereWith('user_type', $value)
                    ->orWhereWith('url', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(Audit::class);

        $queryBuilder->orderBy('id', 'DESC');
        $queryBuilder->where('auditable_type', $this->model);
        if (isset($model->id)) {
            $queryBuilder->where('auditable_id', $model->id);
        }

        return $queryBuilder
            ->defaultSort('audits.created_at')
            ->allowedSorts(['ip_address','auditable_id', 'auditable_type', 'user_type', 'url','created_at'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation(app('group'), $request);
        return $this->handle($this->group);
    }

    public function tableStructure($prefix = null, ?array $exportLinks = null): Closure
    {
        return function (InertiaTable $table) use ($exportLinks, $prefix) {

            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            $table
                ->withGlobalSearch()
                ->withExportLinks($exportLinks)
                ->column(key: 'expand', label: '', type: 'icon')
                ->column(key: 'datetime', label: __('Date'), canBeHidden: false, sortable: true)
                ->column(key: 'user_name', label: __('User'), canBeHidden: false, sortable: true)
                ->column(key: 'old_values', label: __('Old Value'), canBeHidden: false, sortable: true)
                ->column(key: 'new_values', label: __('New Value'), canBeHidden: false, sortable: true)
                ->column(key: 'event', label: __('Action'), canBeHidden: false, sortable: true)
                ->defaultSort('ip_address');
        };
    }

    public function htmlResponse(LengthAwarePaginator $audits, ActionRequest $request): Response
    {
        return Inertia::render(
            'Devel/Dummy',
            [
                // 'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('Changelog'),
                'pageHead'    => [
                    'icon'      => [
                        'icon'  => ['fal', 'fa-history'],
                        'title' => __('Changelog')
                    ],
                    'title'     => __('Changelog'),
                ],
                'data' => $audits
            ]
        );
    }

    public function getBreadcrumbs(): array
    {
        return
            array_merge(
                ShowGroupDashboard::make()->getBreadcrumbs(),
                // [
                //     [
                //         'type'   => 'simple',
                //         'simple' => [
                //             'route' => [
                //                 'name' => 'grp.overview.hub'
                //             ],
                //             'label'  => __('Overview'),
                //         ]
                //     ]
                // ]
            );
    }
}
