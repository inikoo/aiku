<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Thu, 16 Mar 2023 08:17:51 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Mail\Mailshot;

use App\Actions\InertiaAction;
use App\Http\Resources\Mail\MailshotResource;
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

class IndexMailshots extends InertiaAction
{
    //use HasUIMailshots;

    private Mailroom | Outbox $parent;

    public function handle(): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('mailshots.state', '~*', "\y$value\y")
                    ->orWhere('mailshots.data', '=', $value);
            });// reference status date data
        });


        return QueryBuilder::for(Mailshot::class)
            ->defaultSort('mailshots.state')
            ->select(['mailshots.state', 'mailshots.id', 'mailshots.data',
                'outboxes.slug as outboxes_slug',
                'mailrooms.slug as mailroom_id'
            ])
            ->leftJoin('outboxes', 'mailshots.outbox_id', 'outboxes.id')
            ->leftJoin('mailrooms', 'outboxes.mailroom_id', 'mailrooms.id')
            ->when($this->parent, function ($query) {
                if (class_basename($this->parent) == 'Mailroom') {
                    $query->where('outboxes.mailroom_id', $this->parent->id);
                }
                if (class_basename($this->parent) == 'Outbox') {
                    $query->where('mailshots.outbox_id', $this->parent->id);
                }
            })
            ->allowedSorts(['mailshots.state', 'mailshots.data'])
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
        return MailshotResource::collection($this->handle());
    }


    public function htmlResponse(LengthAwarePaginator $mailshots)
    {
        return Inertia::render(
            'Mail/Mailshots',
            [
                'breadcrumbs' => $this->getBreadcrumbs($this->routeName, $this->parent),
                'title'       => __('mailshots '),
                'pageHead'    => [
                    'title' => __('mailshots'),
                ],
                'payments' => MailshotResource::collection($mailshots),


            ]
        )->table(function (InertiaTable $table) {
            $table
                ->withGlobalSearch()
                ->defaultSort('state');

            $table->column(key: 'state', label: __('state'), canBeHidden: false, sortable: true, searchable: true);

            $table->column(key: 'data', label: __('data'), canBeHidden: false, sortable: true, searchable: true);
        });
    }


    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        //$this->fillFromRequest($request);
        $this->parent = app('currentTenant');
        //$this->routeName = $request->route()->getName();
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
}
