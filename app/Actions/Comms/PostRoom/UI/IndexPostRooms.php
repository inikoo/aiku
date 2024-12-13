<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:09:35 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\PostRoom\UI;

use App\Actions\Comms\DispatchedEmail\UI\IndexDispatchedEmails;
use App\Actions\Comms\Mailshot\UI\IndexMailshots;
use App\Actions\Comms\Outbox\UI\IndexOutboxes;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\UI\ShowOverviewHub;
use App\Actions\UI\Dashboards\ShowGroupDashboard;
use App\Enums\Comms\PostRoom\PostRoomsTabsEnum;
use App\Http\Resources\Mail\DispatchedEmailResource;
use App\Http\Resources\Mail\MailshotResource;
use App\Http\Resources\Mail\OutboxesResource;
use App\Http\Resources\Mail\PostRoomResource;
use App\InertiaTable\InertiaTable;
use App\Models\Catalogue\Shop;
use App\Models\Comms\PostRoom;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexPostRooms extends OrgAction
{
    // private Organisation|Shop $parent;

    public function handle($prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('post_rooms.code', '~*', "\y$value\y")
                    ->orWhere('post_rooms.data', '=', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }
        // foreach ($this->elementGroups as $key => $elementGroup) {
        //     $queryBuilder->whereElementGroup(
        //         key: $key,
        //         allowedElements: array_keys($elementGroup['elements']),
        //         engine: $elementGroup['engine'],
        //         prefix: $prefix
        //     );
        // }

        // return $queryBuilder
        //     ->defaultSort('post_rooms.code')
        //     ->select(['code', 'number_outboxes', 'number_mailshots', 'number_dispatched_emails'])
        //     ->leftJoin('post_room_stats', 'post_rooms.id', 'post_room_stats.post_room_id')
        //     ->allowedSorts(['code', 'number_outboxes', 'number_mailshots', 'number_dispatched_emails'])
        //     ->allowedFilters([$globalSearch])
        //     ->withPaginator($prefix)
        //     ->withQueryString();

        $queryBuilder = QueryBuilder::for(PostRoom::class);
        $queryBuilder->where('post_rooms.group_id', $this->group->id);

        $queryBuilder
            ->defaultSort('post_rooms.name')
            ->select([
            'post_rooms.id',
            'post_rooms.slug',
            'post_rooms.name',
            'post_room_stats.number_outboxes',
            'post_room_stats.number_mailshots',
            'post_room_intervals.dispatched_emails_lw',
            'post_room_intervals.opened_emails_lw',
            'post_room_intervals.unsubscribed_emails_lw'
            ])
            ->selectRaw('(post_room_stats.number_mailshots) as runs')
            ->leftJoin('post_room_stats', 'post_room_stats.post_room_id', '=', 'post_rooms.id')
            ->leftJoin('post_room_intervals', 'post_room_intervals.post_room_id', '=', 'post_rooms.id');

        return $queryBuilder
            ->allowedSorts(['name', 'runs', 'number_outboxes', 'number_mailshots', 'dispatched_emails_lw', 'opened_emails_lw', 'unsubscribed_emails_lw'])
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
        $this->initialisationFromGroup(app('group'), $request);

        return $this->handle();
    }

    // public function tableStructure($prefix): Closure
    // {
    //     return function (InertiaTable $table) use ($prefix) {

    //         if ($prefix) {
    //             $table
    //                 ->name($prefix)
    //                 ->pageName($prefix.'Page');
    //         }

    //         $table
    //             ->withGlobalSearch()
    //             ->defaultSort('name')
    //             ->column(key: 'number_outboxes', label: __('outboxes'), canBeHidden: false, sortable: true, searchable: true)
    //             ->column(key: 'number_mailshots', label: __('mailshots'), canBeHidden: false, sortable: true, searchable: true)
    //             ->column(key: 'number_dispatched_emails', label: __('dispatched emails'), canBeHidden: false, sortable: true, searchable: true);
    //     };
    // }

    public function tableStructure(
        Group $parent,
        ?array $modelOperations = null,
        $prefix = null,
        $canEdit = false
    ): Closure {
        return function (InertiaTable $table) use ($parent, $modelOperations, $prefix, $canEdit) {
            if ($prefix) {
                $table->name($prefix)->pageName($prefix . 'Page');
            }

            // foreach ($this->getElementGroups($parent) as $key => $elementGroup) {
            //     $table->elementGroup(
            //         key: $key,
            //         label: $elementGroup['label'],
            //         elements: $elementGroup['elements']
            //     );
            // }

            $table
                ->withGlobalSearch()
                ->withModelOperations($modelOperations)
                ->withEmptyState(
                    match (class_basename($parent)) {
                        'Group' => [
                            'title'       => __("No post room found"),
                            'count'       => $parent->commsStats->number_org_post_rooms,
                        ],
                        default => null
                    }
                );

            $table
                ->column(key: 'name', label: __('Name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'runs', label: __('Mailshots/Runs'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'dispatched_emails_lw', label: __('Dispatched').' '.__('1w'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'opened_emails_lw', label: __('Opened').' '.__('1w'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'unsubscribed_emails_lw', label: __('Unsubscribed').' '.__('1w'), canBeHidden: false, sortable: true, searchable: true);
        };
    }

    public function jsonResponse(): AnonymousResourceCollection
    {
        return PostRoomResource::collection($this->handle());
    }


    public function htmlResponse(LengthAwarePaginator $postRoom, ActionRequest $request): Response
    {

        $title = __('Post Room');
        $icon  = [
            'icon'  => ['fal', 'fa-cube'],
            'title' => __('post rooms')
        ];
        $afterTitle = null;
        $iconRight = null;

        return Inertia::render(
            'Mail/PostRooms',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('post room'),
                'pageHead' => [
                    'title'         => $title,
                    'icon'          => $icon,
                    'afterTitle'    => $afterTitle,
                    'iconRight'     => $iconRight,
                ],
                // 'tabs' => [
                //     'current'    => $this->tab,
                //     'navigation' => PostRoomsTabsEnum::navigation(),
                // ],
                'data'          => PostRoomResource::collection($postRoom),
                // PostRoomsTabsEnum::POST_ROOMS->value => $this->tab == PostRoomsTabsEnum::POST_ROOMS->value ?
                //     fn () => PostRoomResource::collection($postRoom)
                //     : Inertia::lazy(fn () => PostRoomResource::collection($postRoom)),

                // PostRoomsTabsEnum::OUTBOXES->value => $this->tab == PostRoomsTabsEnum::OUTBOXES->value ?
                //     fn () => OutboxesResource::collection(IndexOutboxes::run($this->parent, PostRoomsTabsEnum::OUTBOXES->value))
                //     : Inertia::lazy(fn () => OutboxesResource::collection(IndexOutboxes::run($this->parent, PostRoomsTabsEnum::OUTBOXES->value))),

                // PostRoomsTabsEnum::MAILSHOTS->value => $this->tab == PostRoomsTabsEnum::MAILSHOTS->value ?
                //     fn () => MailshotResource::collection(IndexMailshots::run($this->parent))
                //     : Inertia::lazy(fn () => MailshotResource::collection(IndexMailshots::run($this->parent))),

                // PostRoomsTabsEnum::DISPATCHED_EMAILS->value => $this->tab == PostRoomsTabsEnum::DISPATCHED_EMAILS->value ?
                //     fn () => DispatchedEmailResource::collection(IndexDispatchedEmails::run($this->parent))
                //     : Inertia::lazy(fn () => DispatchedEmailResource::collection(IndexDispatchedEmails::run($this->parent))),
            ]
        )->table($this->tableStructure($this->group));
        // ->table(IndexOutboxes::make()->tableStructure(parent:$this->parent, prefix: 'outboxes'))
        // ->table(IndexMailshots::make()->tableStructure(parent:$this->parent, prefix: 'mailshots'))
        // ->table(IndexDispatchedEmails::make()->tableStructure(parent:$this->parent, prefix: 'dispatched_emails'));
    }


    public function inOrganisation(Organisation $organisation, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $organisation;
        $this->initialisation($organisation, $request);
        return $this->handle();
    }

    // public function getBreadcrumbs($suffix = null): array
    // {
    //     return array_merge(
    //         (new ShowGroupDashboard())->getBreadcrumbs(),
    //         [
    //             [
    //                 'type'   => 'simple',
    //                 'simple' => [
    //                     'route' => [
    //                         'name' => 'mail.post_rooms.index'
    //                     ],
    //                     'label' => __('post rooms'),
    //                     'icon'  => 'fal fa-bars'
    //                 ],
    //                 'suffix' => $suffix

    //             ]
    //         ]
    //     );
    // }

    public function getBreadcrumbs(string $routeName, array $routeParameters, string $suffix = null): array
    {
        $headCrumb = function (array $routeParameters = []) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => $routeParameters,
                        'label' => __('Post Rooms'),
                        'icon'  => 'fal fa-bars'
                    ],
                ],
            ];
        };

        // dd($routeName);

        return match ($routeName) {
            'grp.overview.post-rooms.index' =>
            array_merge(
                ShowOverviewHub::make()->getBreadcrumbs($routeParameters),
                $headCrumb(
                    [
                        'name'       => $routeName,
                        'parameters' => $routeParameters
                    ],
                    $suffix
                )
            ),
            default => []
        };
    }
}
