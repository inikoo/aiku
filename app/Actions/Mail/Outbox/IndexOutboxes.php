<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Thu, 16 Mar 2023 15:40:54 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Mail\Outbox;

use App\Actions\InertiaAction;
use App\Http\Resources\Mail\OutboxResource;
use App\Models\Mail\Mailroom;
use App\Models\Mail\Outbox;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexOutboxes extends InertiaAction
{
    //use HasUIOutboxes;

    private Mailroom $parent;

    public function handle(): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('outboxes.name', '~*', "\y$value\y")
                    ->orWhere('outboxes.data', '=', $value);
            });
        });// code name data


        return QueryBuilder::for(Outbox::class)
            ->defaultSort('outboxes.name')
            ->select(['outboxes.name', 'outboxes.slug','outboxes.data', 'mailrooms.id as mailrooms_id'])
            ->leftJoin('outbox_stats', 'outbox_stats.id', 'outbox_stats.outbox_id')
            ->leftJoin('outboxes', 'outboxes_id', 'outboxes.id')
            ->when($this->parent, function ($query) {
                if (class_basename($this->parent) == 'Mailroom') {
                    $query->where('outboxes.mailroom_id', $this->parent->id);
                }
            })
            ->allowedSorts(['name', 'data'])
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
        return OutboxResource::collection($this->handle());
    }


    public function htmlResponse(LengthAwarePaginator $outboxes)
    {
        return Inertia::render(
            'Mail/Outboxes',
            [
                'breadcrumbs' => $this->getBreadcrumbs($this->routeName, $this->parent),
                'title'       => __('outboxes '),
                'pageHead'    => [
                    'title'   => __('outboxes'),
                    'create'  => $this->canEdit && $this->routeName=='mail.outboxes.index' ? [
                        'route' => [
                            'name'       => 'mail.outboxes.create',
                            'parameters' => array_values($this->originalParameters)
                        ],
                        'label'=> __('outboxes')
                    ] : false,
                ],
                'outboxes' => OutboxResource::collection($outboxes),


            ]
        )->table(function (InertiaTable $table) {
            $table
                ->withGlobalSearch()
                ->defaultSort('code');

            $table->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true);

            $table->column(key: 'data', label: __('data'), canBeHidden: false, sortable: true, searchable: true);
        });
    }


    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);
        return $this->handle();
    }

    public function inMailroom(Mailroom $mailroom, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $mailroom;
        $this->initialisation($request);
        return $this->handle();
    }
}
