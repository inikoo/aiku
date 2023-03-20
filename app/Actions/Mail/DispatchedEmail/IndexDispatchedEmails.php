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
use App\Http\Resources\Mail\DispatchedEmailResource;
use App\Models\Central\Tenant;
use App\Models\Mail\DispatchedEmail;
use App\Models\Mail\Mailroom;
use App\Models\Mail\Mailshot;
use App\Models\Mail\Outbox;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexDispatchedEmails extends InertiaAction
{
    //use HasUIDispatchedEmails;

    private Mailshot|Outbox|Mailroom|Tenant $parent;

    public function handle(): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('dispatched_emails.state', '~*', "\y$value\y")
                    ->orWhere('dispatched_emails.number_reads', '=', $value)
                    ->orWhere('dispatched_emails.number_clicks', '=', $value);
            }); // reference status date data
        });


        return QueryBuilder::for(DispatchedEmail::class)
            ->defaultSort('dispatched_emails.state')
            ->select(['dispatched_emails.state', 'dispatched_emails.id', 'dispatched_emails.number_reads', 'dispatched_emails.number_clicks',
                'outboxes.slug as outbox_id',
                'outboxes.slug as outboxes_id'
            ])
            ->leftJoin('outboxes', 'dispatched_emails.outbox_id', 'outboxes.id')
            ->leftJoin('mailrooms', 'outboxes.mailroom_id', 'mailrooms.id')
            ->when($this->parent, function ($query) {
                if (class_basename($this->parent) == 'Mailroom') {
                    $query->where('outboxes.mailroom_id', $this->parent->id);
                }
                if (class_basename($this->parent) == 'Outbox') {
                    $query->where('dispatched_emails.mailroom_id', $this->parent->id);
                }
                if (class_basename($this->parent) == 'Mailshot') {
                    $query->where('dispatched_emails.mailshot_id', $this->parent->id);
                }
            })
            ->allowedSorts(['dispatched_emails.state', 'dispatched_emails.number_reads', 'dispatched_emails.number_clicks'])
            ->allowedFilters([$globalSearch])
            ->paginate($this->perPage ?? config('ui.table.records_per_page'))
            ->withQueryString();
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


    public function jsonResponse(): AnonymousResourceCollection
    {
        return DispatchedEmailResource::collection($this->handle());
    }


    public function htmlResponse(LengthAwarePaginator $dispatched_emails)
    {
        return Inertia::render(
            'Mail/DispatchedEmails',
            [
                'breadcrumbs' => $this->getBreadcrumbs($this->routeName, $this->parent),
                'title'       => __('dispatched emails '),
                'pageHead'    => [
                    'title' => __('dispatched emails'),
                ],
                'dispatched emails' => DispatchedEmailResource::collection($dispatched_emails),


            ]
        )->table(function (InertiaTable $table) {
            $table
                ->withGlobalSearch()
                ->defaultSort('state');

            $table->column(key: 'state', label: __('state'), canBeHidden: false, sortable: true, searchable: true);

            $table->column(key: 'number_reads', label: __('reads'), canBeHidden: false, sortable: true, searchable: true);

            $table->column(key: 'number_clicks', label: __('clicks'), canBeHidden: false, sortable: true, searchable: true);
        });
    }


    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = app('currentTenant');
        $this->initialisation($request);
        return $this->handle();
    }

    public function inMailroom(Mailroom $mailroom, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $mailroom;
        //$this->validateAttributes();
        $this->initialisation($request);
        return $this->handle();
    }

    public function inOutbox(Outbox $outbox, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $outbox;
        //$this->validateAttributes();
        $this->initialisation($request);
        return $this->handle();
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inMailroomInOutbox(Mailroom $mailroom, Outbox $outbox, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $outbox;
        //$this->validateAttributes();
        $this->initialisation($request);
        return $this->handle();
    }

    public function inMailroomInOutboxInMailshot(Mailshot $mailshot, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $mailshot;
        //$this->validateAttributes();
        $this->initialisation($request);
        return $this->handle();
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
