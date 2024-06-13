<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 11 Jun 2024 22:35:37 Central European Summer Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\Outbox\UI;

use App\Actions\InertiaAction;
use App\Actions\Mail\PostRoom\UI\ShowPostRoom;
use App\Actions\UI\Marketing\MarketingHub;
use App\Http\Resources\Mail\OutboxResource;
use App\InertiaTable\InertiaTable;
use App\Models\Mail\Outbox;
use App\Models\Mail\PostRoom;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexOutboxes extends InertiaAction
{
    public function handle(PostRoom|Organisation $parent, $prefix=null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('outboxes.name', '~*', "\y$value\y")
                    ->orWhere('outboxes.data', '=', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder=QueryBuilder::for(Outbox::class);
        foreach ($this->elementGroups as $key => $elementGroup) {
            $queryBuilder->whereElementGroup(
                key: $key,
                allowedElements: array_keys($elementGroup['elements']),
                engine: $elementGroup['engine'],
                prefix: $prefix
            );
        }

        return $queryBuilder
            ->defaultSort('outboxes.name')
            ->select(['outboxes.name', 'outboxes.slug', 'outboxes.data', 'post_rooms.id as post_rooms_id'])
            ->leftJoin('outbox_stats', 'outbox_stats.id', 'outbox_stats.outbox_id')
            ->leftJoin('post_rooms', 'post_room_id', 'post_rooms.id')
            ->when($parent, function ($query) use ($parent) {
                if (class_basename($parent) == 'Mail') {
                    $query->where('outboxes.post_room_id', $parent->id);
                }
            })
            ->allowedSorts(['name', 'data'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure($parent, $prefix=null): Closure
    {
        return function (InertiaTable $table) use ($parent, $prefix) {

            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            $table->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true);

            $table->column(key: 'data', label: __('data'), canBeHidden: false, sortable: true, searchable: true);
        };
    }
    public function authorize(ActionRequest $request): bool
    {
        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('mail.view')
            );
    }


    public function jsonResponse(LengthAwarePaginator $outboxes): AnonymousResourceCollection
    {
        return OutboxResource::collection($outboxes);
    }


    public function htmlResponse(LengthAwarePaginator $outboxes, ActionRequest $request): Response
    {
        $parent = $request->route()->originalParameters()() == [] ? app('currentTenant') : last($request->route()->originalParameters()());
        return Inertia::render(
            'Mail/Outboxes',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $parent
                ),
                'title'       => __('outboxes '),
                'pageHead'    => [
                    'title'   => __('outboxes'),
                ],
                'outboxes' => OutboxResource::collection($outboxes),


            ]
        )->table($this->tableStructure($parent));
    }


    public function inShop(ActionRequest $request): LengthAwarePaginator
    {

        $this->initialisation($request);
        return $this->handle(app('currentTenant'));
    }

    /** @noinspection PhpUnused */
    public function inPostRoom(PostRoom $postRoom, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);
        return $this->handle($postRoom);
    }

    public function getBreadcrumbs(string $routeName, PostRoom|Organisation $parent): array
    {
        $headCrumb = function (array $routeParameters = []) use ($routeName) {
            return [
                $routeName => [
                    'route'           => $routeName,
                    'routeParameters' => $routeParameters,
                    'modelLabel'      => [
                        'label' => __('Outbox')
                    ]
                ],
            ];
        };

        return match ($routeName) {
            'mail.outboxes.index' =>
            array_merge(
                (new MarketingHub())->getBreadcrumbs(
                    $routeName,
                    $request->route()->originalParameters()
                ),
                $headCrumb()
            ),
            'mail.post_rooms.show.outboxes.index' =>
            array_merge(
                (new ShowPostRoom())->getBreadcrumbs($parent),
                $headCrumb([$parent->slug])
            ),
            default => []
        };
    }
}