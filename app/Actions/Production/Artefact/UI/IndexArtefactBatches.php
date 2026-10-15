<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 Malaga, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Production\Artefact\UI;

use App\Enums\Production\JobOrder\JobOrderStateEnum;
use App\InertiaTable\InertiaTable;
use App\Models\Production\Artefact;
use App\Models\Production\JobOrderItem;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsObject;
use Spatie\QueryBuilder\AllowedFilter;

class IndexArtefactBatches
{
    use AsObject;

    public function handle(Artefact $artefact, ?string $prefix = null, bool $open = false): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereStartWith('job_orders.reference', $value)
                    ->orWhereStartWith('employees.contact_name', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        return QueryBuilder::for($open ? $this->toDo($artefact)->reorder() : $this->query($artefact)->where('job_order_items.quantity_received', '>', 0))
            ->defaultSort($open ? 'date' : '-date')
            ->allowedSorts(['reference', 'artisan', 'date', 'quantity', 'quantity_made', 'quantity_received'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix, tableName: request()->route()?->getName())
            ->withQueryString();
    }

    /**
     * One row per batch: a job order line, or each artisan's part of a split line. Its date is when the
     * job order was received into stock, or when it was raised while it is still being made.
     */
    public function query(Artefact $artefact): Builder
    {
        return JobOrderItem::query()
            ->join('job_orders', 'job_orders.id', 'job_order_items.job_order_id')
            ->join('productions', 'productions.id', 'job_orders.production_id')
            ->join('organisations', 'organisations.id', 'job_order_items.organisation_id')
            ->leftJoin('employees', 'employees.id', DB::raw('coalesce(job_order_items.employee_id, job_orders.employee_id)'))
            ->where('job_order_items.artefact_id', $artefact->id)
            ->select(
                'job_order_items.id',
                'job_order_items.quantity',
                'job_order_items.quantity_received',
                'job_orders.slug as job_order_slug',
                'productions.slug as production_slug',
                'organisations.slug as organisation_slug',
                'job_orders.reference',
                'job_orders.state as job_order_state',
                'employees.contact_name as artisan'
            )
            ->selectRaw('coalesce(job_orders.received_at, job_orders.date, job_orders.created_at) as date')
            ->selectRaw('(select t.quantity_made from job_order_item_tasks t where t.job_order_item_id = job_order_items.id order by t.position desc, t.id desc limit 1) as quantity_made')
            ->selectRaw("(select count(*) from job_order_item_tasks t where t.job_order_item_id = job_order_items.id and t.state = 'done') as number_tasks_done")
            ->selectRaw('(select count(*) from job_order_item_tasks t where t.job_order_item_id = job_order_items.id) as number_tasks');
    }

    public function produced(Artefact $artefact, int $limit = 10): Builder
    {
        return $this->query($artefact)
            ->where('job_order_items.quantity_received', '>', 0)
            ->orderByDesc('date')
            ->limit($limit);
    }

    public function toDo(Artefact $artefact): Builder
    {
        return $this->query($artefact)
            ->whereIn('job_orders.state', JobOrderStateEnum::open())
            ->whereColumn('job_order_items.quantity_received', '<', 'job_order_items.quantity')
            ->orderBy('date');
    }

    public function tableStructure(?string $prefix = null, bool $open = false): Closure
    {
        return function (InertiaTable $table) use ($prefix, $open) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            $table
                ->withGlobalSearch()
                ->withEmptyState(['title' => $open ? __('No current job orders') : __('No jobs done yet')])
                ->column(key: 'reference', label: __('Job order'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'artisan', label: __('Artisan'), canBeHidden: false, sortable: true)
                ->column(key: 'state', label: __('State'), canBeHidden: false)
                ->column(key: 'date', label: __('Date'), canBeHidden: false, sortable: true)
                ->column(key: 'tasks', label: __('Tasks done'), canBeHidden: false)
                ->column(key: 'quantity', label: __('Batch'), canBeHidden: false, sortable: true, align: 'right')
                ->column(key: 'quantity_made', label: __('Made'), canBeHidden: false, sortable: true, align: 'right')
                ->column(key: 'quantity_received', label: __('Received'), canBeHidden: false, sortable: true, align: 'right')
                ->defaultSort($open ? 'date' : '-date');
        };
    }
}
