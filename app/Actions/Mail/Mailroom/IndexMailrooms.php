<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Fri, 17 Mar 2023 14:13:34 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Mail\Mailroom;

use App\Actions\InertiaAction;
use App\Actions\Mail\DispatchedEmail\IndexDispatchedEmails;
use App\Actions\Mail\Mailshot\IndexMailshots;
use App\Actions\Mail\Outbox\IndexOutboxes;
use App\Actions\UI\Dashboard\ShowDashboard;
use App\Enums\UI\MailroomsTabsEnum;
use App\Http\Resources\Mail\DispatchedEmailResource;
use App\Http\Resources\Mail\MailroomResource;
use App\Http\Resources\Mail\MailshotResource;
use App\Http\Resources\Mail\OutboxResource;
use App\Models\Mail\Mailroom;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use App\InertiaTable\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexMailrooms extends InertiaAction
{
    /** @noinspection PhpUndefinedMethodInspection */
    public function handle($prefix=null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('mailrooms.code', '~*', "\y$value\y")
                    ->orWhere('mailrooms.data', '=', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder=QueryBuilder::for(Mailroom::class);
        foreach ($this->elementGroups as $key => $elementGroup) {
            $queryBuilder->whereElementGroup(
                prefix: $prefix,
                key: $key,
                allowedElements: array_keys($elementGroup['elements']),
                engine: $elementGroup['engine']
            );
        }

        return $queryBuilder
            ->defaultSort('mailrooms.code')
            ->select(['code', 'number_outboxes', 'number_mailshots', 'number_dispatched_emails'])
            ->leftJoin('mailroom_stats', 'mailrooms.id', 'mailroom_stats.mailroom_id')
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
        return MailroomResource::collection($this->handle());
    }


    public function htmlResponse(LengthAwarePaginator $mailroom, ActionRequest $request): Response
    {
        $scope=app('currentTenant');

        return Inertia::render(
            'Mail/Mailrooms',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('mailroom'),
                'pageHead'    => [
                    'title'   => __('mailroom'),
                    'create'  => $this->canEdit && $this->routeName=='mail.mailrooms.index' ? [
                        'route' => [
                            'name'       => 'shops.create',
                            'parameters' => array_values($this->originalParameters)
                        ],
                        'label'    => __('mailroom'),
                    ] : false,
                ],
                'tabs' => [
                    'current'    => $this->tab,
                    'navigation' => MailroomsTabsEnum::navigation(),
                ],


                MailroomsTabsEnum::MAILROOMS->value => $this->tab == MailroomsTabsEnum::MAILROOMS->value ?
                    fn () => MailroomResource::collection($mailroom)
                    : Inertia::lazy(fn () => MailroomResource::collection($mailroom)),

                MailroomsTabsEnum::OUTBOXES->value => $this->tab == MailroomsTabsEnum::OUTBOXES->value ?
                    fn () => OutboxResource::collection(IndexOutboxes::run($scope, MailroomsTabsEnum::OUTBOXES->value))
                    : Inertia::lazy(fn () => OutboxResource::collection(IndexOutboxes::run($scope, MailroomsTabsEnum::OUTBOXES->value))),

                MailroomsTabsEnum::MAILSHOTS->value => $this->tab == MailroomsTabsEnum::MAILSHOTS->value ?
                    fn () => MailshotResource::collection(IndexMailshots::run($scope))
                    : Inertia::lazy(fn () => MailshotResource::collection(IndexMailshots::run($scope))),

                MailroomsTabsEnum::DISPATCHED_EMAILS->value => $this->tab == MailroomsTabsEnum::DISPATCHED_EMAILS->value ?
                    fn () => DispatchedEmailResource::collection(IndexDispatchedEmails::run($scope))
                    : Inertia::lazy(fn () => DispatchedEmailResource::collection(IndexDispatchedEmails::run($scope))),
            ]
        )->table($this->tableStructure(prefix: 'mailrooms'))
            ->table(IndexOutboxes::make()->tableStructure(parent:$scope, prefix: 'outboxes'))
            ->table(IndexMailshots::make()->tableStructure(parent:$scope, prefix: 'mailshots'))
            ->table(IndexDispatchedEmails::make()->tableStructure(parent:$scope, prefix: 'dispatched_emails'));
    }


    public function inTenant(ActionRequest $request): LengthAwarePaginator
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
                            'name' => 'mail.mailrooms.index'
                        ],
                        'label' => __('mailrooms'),
                        'icon'  => 'fal fa-bars'
                    ],
                    'suffix'=> $suffix

                ]
            ]
        );
    }
}
