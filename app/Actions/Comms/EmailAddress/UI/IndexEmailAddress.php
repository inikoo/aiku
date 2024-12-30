<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 30-12-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\Comms\EmailAddress\UI;

use App\Actions\GrpAction;
use App\Actions\SysAdmin\Group\UI\ShowOverviewHub;
use App\Http\Resources\Mail\EmailAddressResource;
use App\InertiaTable\InertiaTable;
use App\Models\Comms\EmailAddress;
use App\Models\SysAdmin\Group;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexEmailAddress extends GrpAction
{
    public function handle($prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereAnyWordStartWith('email_addresses.email', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }


        $queryBuilder = QueryBuilder::for(EmailAddress::class);
        $queryBuilder->where('email_addresses.group_id', $this->group->id);

        $queryBuilder
            ->defaultSort('email_addresses.email')
            ->select([
                'email_addresses.id',
                'email_addresses.email',
                'email_addresses.number_marketing_dispatches as marketing',
                'email_addresses.number_transactional_dispatches as transactional'
            ]);

        return $queryBuilder
            ->allowedSorts(['email', 'marketing', 'transactional'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("group-overview");
    }

    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation(app('group'), $request);

        return $this->handle();
    }

    public function tableStructure(
        Group $parent,
        ?array $modelOperations = null,
        $prefix = null,
        $canEdit = false
    ): Closure {
        return function (InertiaTable $table) use ($parent, $modelOperations, $prefix, $canEdit) {
            if ($prefix) {
                $table->name($prefix)->pageName($prefix.'Page');
            }


            $table
                ->withGlobalSearch()
                ->withModelOperations($modelOperations)
                ->withEmptyState(
                    match (class_basename($parent)) {
                        'Group' => [
                            'title' => __("No email address found"),
                            'count' => $parent->commsStats->number_email_addresses,
                        ],
                        default => null
                    }
                );

            $table
                ->column(key: 'email', label: __('Email'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'marketing', label: __('Marketing'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'transactional', label: __('Transactional'), canBeHidden: false, sortable: true, searchable: true);
        };
    }

    public function htmlResponse(LengthAwarePaginator $emailAddress, ActionRequest $request): Response
    {
        $title      = __('Email Addresses');
        $icon       = [
            'icon'  => ['fal', 'fa-envelope'],
            'title' => __('Email Addresses')
        ];
        $afterTitle = null;
        $iconRight  = null;

        return Inertia::render(
            'Comms/EmailAddresses',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('email addresses'),
                'pageHead'    => [
                    'title'      => $title,
                    'icon'       => $icon,
                    'afterTitle' => $afterTitle,
                    'iconRight'  => $iconRight,
                ],

                'data' => EmailAddressResource::collection($emailAddress),

            ]
        )->table($this->tableStructure($this->group));
    }


    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        $headCrumb = function (array $routeParameters = []) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => $routeParameters,
                        'label' => __('Email Addresses'),
                        'icon'  => 'fal fa-bars'
                    ],
                ],
            ];
        };

        return match ($routeName) {
            'grp.overview.comms-marketing.email-addresses.index' =>
            array_merge(
                ShowOverviewHub::make()->getBreadcrumbs(),
                $headCrumb(
                    [
                        'name'       => $routeName,
                        'parameters' => $routeParameters
                    ]
                )
            ),
            default => []
        };
    }
}
