<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 24-12-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\SysAdmin\Organisation\UI;

use App\Actions\OrgAction;
use App\Actions\SysAdmin\User\Traits\WithFormattedUserHistories;
use App\Actions\UI\Overview\ShowOverviewHub;
use App\Http\Resources\SysAdmin\HistoriesResource;
use App\InertiaTable\InertiaTable;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Pagination\LengthAwarePaginator;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use OwenIt\Auditing\Models\Audit;
use Spatie\QueryBuilder\AllowedFilter;

class IndexHistoryInOrganisation extends OrgAction
{
    use AsAction;
    use WithAttributes;
    use WithFormattedUserHistories;

    public string $model;

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

    public function asController(Organisation $organisation, ActionRequest $request): LengthAwarePaginator
    {
        $this->organisation = $organisation;
        $this->initialisation($organisation, $request);
        return $this->handle($this->organisation);
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

    public function htmlResponse(LengthAwarePaginator $histories, ActionRequest $request): Response
    {
        return Inertia::render(
            'SysAdmin/Histories',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters(),
                ),
                'title'       => __('Changelog'),
                'pageHead'    => [
                    'icon'      => [
                        'icon'  => ['fal', 'fa-history'],
                        'title' => __('Changelog')
                    ],
                    'title'     => __('Changelog'),
                ],
                'data'        => HistoriesResource::collection($histories),
            ]
        )->table($this->tableStructure());
    }


    public function getBreadcrumbs(string $routeName, array $routeParameters, string $suffix = null): array
    {
        $headCrumb = function (array $routeParameters, ?string $suffix) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => $routeParameters,
                        'label' => __('Changelog'),
                        'icon'  => 'fal fa-history'
                    ],
                    'suffix' => $suffix
                ],
            ];
        };

        return match ($routeName) {
            'grp.org.overview.changelog.index' =>
            array_merge(
                ShowOverviewHub::make()->getBreadcrumbs(
                    $routeName,
                    $routeParameters
                ),
                $headCrumb(
                    [
                        'name' => $routeName,
                        'parameters' => $routeParameters
                    ],
                    $suffix
                ),
            ),
            default => []
        };
    }


}
