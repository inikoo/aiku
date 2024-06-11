<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Fri, 17 Mar 2023 14:13:34 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Mail\PostRoom;

use App\Actions\InertiaAction;
use App\Actions\Mail\DispatchedEmail\IndexDispatchedEmails;
use App\Actions\Mail\Mailshot\IndexMailshots;
use App\Actions\Mail\Outbox\IndexOutboxes;
use App\Actions\UI\Grp\Dashboard\ShowDashboard;
use App\Enums\Mail\PostRoom\PostRoomsTabsEnum;
use App\Http\Resources\Mail\DispatchedEmailResource;
use App\Http\Resources\Mail\MailshotResource;
use App\Http\Resources\Mail\OutboxResource;
use App\Http\Resources\Mail\PostRoomResource;
use App\InertiaTable\InertiaTable;
use App\Models\Mail\PostRoom;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexPostRooms extends InertiaAction
{
    public function handle($prefix=null): LengthAwarePaginator
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

        $queryBuilder=QueryBuilder::for(PostRoom::class);
        foreach ($this->elementGroups as $key => $elementGroup) {
            $queryBuilder->whereElementGroup(
                key: $key,
                allowedElements: array_keys($elementGroup['elements']),
                engine: $elementGroup['engine'],
                prefix: $prefix
            );
        }

        return $queryBuilder
            ->defaultSort('post_rooms.code')
            ->select(['code', 'number_outboxes', 'number_mailshots', 'number_dispatched_emails'])
            ->leftJoin('post_room_stats', 'post_rooms.id', 'post_room_stats.post_room_id')
            ->allowedSorts(['code', 'number_outboxes', 'number_mailshots', 'number_dispatched_emails'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure($prefix): Closure
    {
        return function (InertiaTable $table) use ($prefix) {

            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            $table
                ->withGlobalSearch()
                ->defaultSort('code')
                ->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'number_outboxes', label: __('outboxes'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'number_mailshots', label: __('mailshots'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'number_dispatched_emails', label: __('dispatched emails'), canBeHidden: false, sortable: true, searchable: true);
        };
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo('mail.edit');
        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('mail.view')
            );
    }


    public function jsonResponse(): AnonymousResourceCollection
    {
        return PostRoomResource::collection($this->handle());
    }


    public function htmlResponse(LengthAwarePaginator $postRoom, ActionRequest $request): Response
    {
        $scope=app('currentTenant');

        return Inertia::render(
            'Mail/PostRooms',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('post room'),
                'pageHead'    => [
                    'title'   => __('post room'),
                    'create'  => $this->canEdit && $request->route()->getName()=='mail.post_rooms.index' ? [
                        'route' => [
                            'name'       => 'shops.create',
                            'parameters' => array_values($request->route()->originalParameters())
                        ],
                        'label'    => __('post room'),
                    ] : false,
                ],
                'tabs' => [
                    'current'    => $this->tab,
                    'navigation' => PostRoomsTabsEnum::navigation(),
                ],


                PostRoomsTabsEnum::POST_ROOMS->value => $this->tab == PostRoomsTabsEnum::POST_ROOMS->value ?
                    fn () => PostRoomResource::collection($postRoom)
                    : Inertia::lazy(fn () => PostRoomResource::collection($postRoom)),

                PostRoomsTabsEnum::OUTBOXES->value => $this->tab == PostRoomsTabsEnum::OUTBOXES->value ?
                    fn () => OutboxResource::collection(IndexOutboxes::run($scope, PostRoomsTabsEnum::OUTBOXES->value))
                    : Inertia::lazy(fn () => OutboxResource::collection(IndexOutboxes::run($scope, PostRoomsTabsEnum::OUTBOXES->value))),

                PostRoomsTabsEnum::MAILSHOTS->value => $this->tab == PostRoomsTabsEnum::MAILSHOTS->value ?
                    fn () => MailshotResource::collection(IndexMailshots::run($scope))
                    : Inertia::lazy(fn () => MailshotResource::collection(IndexMailshots::run($scope))),

                PostRoomsTabsEnum::DISPATCHED_EMAILS->value => $this->tab == PostRoomsTabsEnum::DISPATCHED_EMAILS->value ?
                    fn () => DispatchedEmailResource::collection(IndexDispatchedEmails::run($scope))
                    : Inertia::lazy(fn () => DispatchedEmailResource::collection(IndexDispatchedEmails::run($scope))),
            ]
        )->table($this->tableStructure(prefix: 'post_rooms'))
            ->table(IndexOutboxes::make()->tableStructure(parent:$scope, prefix: 'outboxes'))
            ->table(IndexMailshots::make()->tableStructure(parent:$scope, prefix: 'mailshots'))
            ->table(IndexDispatchedEmails::make()->tableStructure(parent:$scope, prefix: 'dispatched_emails'));
    }


    public function inOrganisation(ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);
        return $this->handle();
    }

    public function inShop(ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);
        return $this->handle();
    }

    public function getBreadcrumbs($suffix=null): array
    {
        return array_merge(
            (new ShowDashboard())->getBreadcrumbs(),
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name' => 'mail.post_rooms.index'
                        ],
                        'label' => __('post rooms'),
                        'icon'  => 'fal fa-bars'
                    ],
                    'suffix'=> $suffix

                ]
            ]
        );
    }
}
