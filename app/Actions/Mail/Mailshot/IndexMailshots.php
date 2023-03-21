<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Thu, 16 Mar 2023 08:17:51 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Mail\Mailshot;

use App\Actions\InertiaAction;
use App\Actions\Mail\Mailshot\UI\HasUIMailshots;
use App\Enums\UI\TabsAbbreviationEnum;
use App\Http\Resources\Mail\MailshotResource;
use App\Models\Central\Tenant;
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

class IndexMailshots extends InertiaAction
{
    use HasUIMailshots;

    public function handle(Outbox|Mailroom|Tenant $parent): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('mailshots.state', '~*', "\y$value\y")
                    ->orWhere('mailshots.data', '=', $value);
            });
        });
        InertiaTable::updateQueryBuilderParameters(TabsAbbreviationEnum::MAILSHOTS->value);

        return QueryBuilder::for(Mailshot::class)
            ->defaultSort('mailshots.state')
            ->select([
                'mailshots.state',
                'mailshots.id',
                'mailshots.data',
                'outboxes.slug as outboxes_slug',
                'mailrooms.id as mailroom_id'
            ])
            ->leftJoin('outboxes', 'mailshots.outbox_id', 'outboxes.id')
            ->leftJoin('mailrooms', 'outboxes.mailroom_id', 'mailrooms.id')
            ->when($parent, function ($query) use ($parent) {
                if (class_basename($parent) == 'Mailroom') {
                    $query->where('mailshots.mailroom_id', $parent->id);
                }
            })
            ->allowedSorts(['mailshots.state', 'mailshots.data'])
            ->allowedFilters([$globalSearch])
            ->paginate(
                perPage: $this->perPage ?? config('ui.table.records_per_page'),
                pageName: TabsAbbreviationEnum::MAILSHOTS->value.'Page'
            )
            ->withQueryString();
    }

    public function tableStructure($parent): Closure
    {
        return function (InertiaTable $table) use ($parent) {
            $table
                ->name(TabsAbbreviationEnum::MAILSHOTS->value)
                ->pageName(TabsAbbreviationEnum::MAILSHOTS->value.'Page');

            $table->column(key: 'state', label: __('state'), canBeHidden: false, sortable: true, searchable: true);

            $table->column(key: 'data', label: __('data'), canBeHidden: false, sortable: true, searchable: true);
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


    public function jsonResponse(LengthAwarePaginator $mailshots): AnonymousResourceCollection
    {
        return MailshotResource::collection($mailshots);
    }


    public function htmlResponse(LengthAwarePaginator $mailshots, ActionRequest $request)
    {
        $parent = $request->route()->parameters() == [] ? app('currentTenant') : last($request->route()->parameters());

        return Inertia::render(
            'Mail/Mailshots',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $parent
                ),
                'title'       => __('mailshots '),
                'pageHead'    => [
                    'title'   => __('mailshots'),
                    'create'  => $this->canEdit && $this->routeName=='mail.mailshots.index' ? [
                        'route' => [
                            'name'       => 'mail.mailshots.create',
                            'parameters' => array_values($this->originalParameters)
                        ],
                        'label'=> __('mailshot')
                    ] : false,
                ],
                'payments' => MailshotResource::collection($mailshots),


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
}
