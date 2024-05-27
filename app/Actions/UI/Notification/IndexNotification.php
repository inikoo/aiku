<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 08 Apr 2024 15:23:05 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Notification;

use App\Actions\UI\Grp\Dashboard\ShowDashboard;
use App\Actions\UI\WithInertia;
use App\Http\Resources\SysAdmin\NotificationResource;
use App\InertiaTable\InertiaTable;
// use App\Models\CRM\WebUser;
use App\Models\SysAdmin\User;
use Inertia\Response;
use Inertia\Inertia;
use Closure;
use App\Services\QueryBuilder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Spatie\QueryBuilder\AllowedFilter;

class IndexNotification
{
    use AsAction;
    use WithInertia;
    // private User $parent;

    public function handle(User $user, $prefix = null): LengthAwarePaginator
    {
        // dd($user);
        $globalSearch = AllowedFilter::callback('filter', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                if($value == 'unread') {
                    $query->whereNull('notifications.read_at');
                }

                if($value == 'read') {
                    $query->whereNotNull('notifications.read_at');
                }
            });
        });


        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $query = QueryBuilder::for($user->notifications());

        return $query->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        return $this->handle($request->user(), 'notifications');
    }

    public function jsonResponse(LengthAwarePaginator $notifications): AnonymousResourceCollection
    {
        return NotificationResource::collection($notifications);
    }

    public function htmlResponse(LengthAwarePaginator $notifications, ActionRequest $request): Response
    {
        return Inertia::render(
            'Notifications',
            [
                 'breadcrumbs' => $this->getBreadcrumbs($request->route()->getName()),
                'title'        => __('Notifications'),
                'pageHead'     => [
                    'title'   => __('Notifications'),

                ],
                'data'        => NotificationResource::collection($notifications),
            ]
        )->table($this->tableStructure(request()->user(), prefix: 'notifications'));
    }

    public function tableStructure($user, ?array $modelOperations = null, $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($modelOperations, $prefix, $user) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            $table
                ->withModelOperations($modelOperations)
                ->withGlobalSearch()
                ->withEmptyState(
                    [
                        'title'       => __('You have no notification yet.'),
                        'description' => null,
                        // 'count'       => $user->crmStats->number_prospects
                    ]
                )
                ->column(key: 'title', label: __('title'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'body', label: __('body'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'type', label: __('type'), canBeHidden: false, sortable: true, searchable: true);
        };
    }

    public function getBreadcrumbs(string $routeName): array
    {
        $headCrumb = function (array $routeParameters = []) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => $routeParameters,
                        'label' => __('notifications'),
                        'icon'  => 'fal fa-bars'
                    ],
                ],
            ];
        };

        return match ($routeName) {
            'grp.notifications' =>
            array_merge(
                ShowDashboard::make()->getBreadcrumbs(),
                $headCrumb(
                    [
                        'name' => 'grp.notifications',
                        null
                    ]
                ),
            ),

            default => []
        };
    }
}
