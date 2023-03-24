<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Thu, 16 Mar 2023 08:17:51 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Mail\DispatchedEmail;

use App\Actions\InertiaAction;
use App\Actions\Mail\Mailroom\ShowMailroom;
use App\Actions\UI\Mail\MailDashboard;
use App\Enums\UI\TabsAbbreviationEnum;
use App\Http\Resources\Mail\DispatchedEmailResource;
use App\Models\Central\Tenant;
use App\Models\Mail\DispatchedEmail;
use App\Models\Mail\Mailroom;
use App\Models\Mail\Mailshot;
use App\Models\Mail\Outbox;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexDispatchedEmails extends InertiaAction
{
    private Mailshot|Outbox|Mailroom|Tenant $parent;
    public function handle($parent): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('dispatched_emails.state', '~*', "\y$value\y")
                    ->orWhere('dispatched_emails.number_reads', '=', $value)
                    ->orWhere('dispatched_emails.number_clicks', '=', $value);
            }); // reference status date data
        });

        InertiaTable::updateQueryBuilderParameters(TabsAbbreviationEnum::DISPATCHED_EMAILS->value);
        return QueryBuilder::for(DispatchedEmail::class)
            ->defaultSort('dispatched_emails.state')
            ->select(['dispatched_emails.state',
                'dispatched_emails.id',
                'dispatched_emails.number_reads',
                'dispatched_emails.number_clicks',
                'outboxes.slug as outbox_id',
                'outboxes.slug as outboxes_id'
            ])
            ->leftJoin('outboxes', 'dispatched_emails.outbox_id', 'outboxes.id')
            ->leftJoin('mailrooms', 'outboxes.mailroom_id', 'mailrooms.id')
            ->when($parent, function ($query) use ($parent) {
                if (class_basename($parent) == 'Mailroom') {
                    $query->where('outboxes.mailroom_id', $parent->id);
                }
                if (class_basename($parent) == 'Outbox') {
                    $query->where('dispatched_emails.mailroom_id', $parent->id);
                }
                if (class_basename($parent) == 'Mailshot') {
                    $query->where('dispatched_emails.mailshot_id', $parent->id);
                }
            })
            ->allowedSorts(['dispatched_emails.state', 'dispatched_emails.number_reads', 'dispatched_emails.number_clicks'])
            ->allowedFilters([$globalSearch])
            ->paginate(
                perPage: $this->perPage ?? config('ui.table.records_per_page'),
                pageName: TabsAbbreviationEnum::DISPATCHED_EMAILS->value.'Page'
            )
            ->withQueryString();
    }

    public function tableStructure($parent): Closure
    {
        return function (InertiaTable $table) use ($parent) {
            $table
                ->name(TabsAbbreviationEnum::DISPATCHED_EMAILS->value)
                ->pageName(TabsAbbreviationEnum::DISPATCHED_EMAILS->value.'Page');

            $table->column(key: 'state', label: __('state'), canBeHidden: false, sortable: true, searchable: true);

            $table->column(key: 'number_reads', label: __('reads'), canBeHidden: false, sortable: true, searchable: true);

            $table->column(key: 'number_clicks', label: __('clicks'), canBeHidden: false, sortable: true, searchable: true);
        };
    }
    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('mail.edit');
        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('mail.view')
            );
    }


    public function jsonResponse(LengthAwarePaginator $dispatchedEmails): AnonymousResourceCollection
    {
        return DispatchedEmailResource::collection($dispatchedEmails);
    }


    public function htmlResponse(LengthAwarePaginator $dispatched_emails, ActionRequest $request)
    {
        $parent = $request->route()->parameters() == [] ? app('currentTenant') : last($request->route()->parameters());
        return Inertia::render(
            'Mail/DispatchedEmails',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $parent
                ),
                'title'       => __('dispatched emails '),
                'pageHead'    => [
                    'title' => __('dispatched emails'),
                ],
                'dispatched emails' => DispatchedEmailResource::collection($dispatched_emails),


            ]
        )->table($this->tableStructure($parent));
    }


    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        $this->routeName = $request->route()->getName();
        $this->initialisation($request);
        return $this->handle(app('currentTenant'));
    }

    public function inMailroom(Mailroom $mailroom, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);
        return $this->handle($mailroom);
    }

    public function inOutbox(Outbox $outbox, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);
        return $this->handle($outbox);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inMailroomInOutbox(Mailroom $mailroom, Outbox $outbox, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);
        return $this->handle($outbox);
    }

    public function inMailroomInOutboxInMailshot(Mailshot $mailshot, ActionRequest $request): LengthAwarePaginator
    {
        //$this->validateAttributes();
        $this->initialisation($request);
        return $this->handle($mailshot);
    }

    public function getBreadcrumbs(string $routeName, Mailshot|Outbox|Mailroom|Tenant $parent): array
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
                (new MailDashboard())->getBreadcrumbs(),
                $headCrumb()
            ),
            'mail.mailrooms.show.outboxes.index' =>
            array_merge(
                (new ShowMailroom())->getBreadcrumbs($parent),
                $headCrumb([$parent->slug])
            ),
            default => []
        };
    }
}
