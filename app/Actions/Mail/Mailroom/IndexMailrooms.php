<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Fri, 17 Mar 2023 14:13:34 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Mail\Mailroom;

use App\Actions\InertiaAction;
use App\Actions\UI\Accounting\AccountingDashboard;
use App\Http\Resources\Mail\MailroomResource;
use App\Models\Mail\Mailroom;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexMailrooms extends InertiaAction
{
    public function handle(): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('mailrooms.code', '~*', "\y$value\y")
                    ->orWhere('mailrooms.data', '=', $value);
            });
        });


        return QueryBuilder::for(Mailroom::class)
            ->defaultSort('mailrooms.code')
            ->select(['code', 'number_outboxes', 'number_mailshots', 'number_dispatched_emails'])
            ->leftJoin('mailroom_stats', 'mailrooms.id', 'mailroom_stats.mailroom_id')
            ->allowedSorts(['code', 'number_outboxes', 'number_mailshots', 'number_dispatched_emails'])
            ->allowedFilters([$globalSearch])
            ->paginate($this->perPage ?? config('ui.table.records_per_page'))
            ->withQueryString();
    }

    public function authorize(ActionRequest $request): bool
    {
        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('mailroom.view')
            );
    }


    public function jsonResponse(): AnonymousResourceCollection
    {
        return MailroomResource::collection($this->handle());
    }


    public function htmlResponse(LengthAwarePaginator $mailroom)
    {
        return Inertia::render(
            'Mail/Mailrooms',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('mailroom'),
                'pageHead'    => [
                    'title' => __('mailroom'),
                ],
                'payment_service_providers' => MailroomResource::collection($mailroom),


            ]
        )->table(function (InertiaTable $table) {
            $table
                ->withGlobalSearch()
                ->defaultSort('code');

            $table->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true);

            $table->column(key: 'number_outboxes', label: __('outboxes'), canBeHidden: false, sortable: true, searchable: true);

            $table->column(key: 'number_mailshots', label: __('mailshots'), canBeHidden: false, sortable: true, searchable: true);

            $table->column(key: 'number_dispatched_emails', label: __('dispatched emails'), canBeHidden: false, sortable: true, searchable: true);
        });
    }


    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);
        return $this->handle();
    }

    public function getBreadcrumbs(): array
    {
        return array_merge(
            (new AccountingDashboard())->getBreadcrumbs(),
            [
                'mail.mailrooms.index' => [
                    'route'      => 'mail.mailrooms.index',
                    'modelLabel' => [
                        'label' => __('mailroom')
                    ],
                ],
            ]
        );
    }
}
