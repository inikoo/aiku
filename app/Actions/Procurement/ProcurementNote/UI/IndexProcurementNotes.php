<?php

/*
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\ProcurementNote\UI;

use App\Enums\Helpers\Audit\AuditEventEnum;
use App\InertiaTable\InertiaTable;
use App\Models\GoodsIn\StockDelivery;
use App\Models\Procurement\ProcurementNote;
use App\Models\Procurement\PurchaseOrder;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsObject;
use Spatie\QueryBuilder\AllowedFilter;

class IndexProcurementNotes
{
    use AsObject;

    public function handle(PurchaseOrder|StockDelivery $model, ?string $prefix = null): LengthAwarePaginator
    {
        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->whereRaw("audits.new_values->>'note' ILIKE ?", ["%$value%"]);
        });

        $queryBuilder = QueryBuilder::for(ProcurementNote::class)
            ->leftJoin('users', 'users.id', 'audits.user_id')
            ->leftJoin('purchase_orders', function ($join) {
                $join->on('purchase_orders.id', 'audits.auditable_id')->where('audits.auditable_type', 'PurchaseOrder');
            })
            ->joinSub($this->distinctNotes($model), 'distinct_notes', 'distinct_notes.id', 'audits.id')
            ->select([
                'distinct_notes.number_documents',
                'audits.id',
                'audits.auditable_type',
                'audits.new_values',
                'audits.data',
                'audits.created_at',
                'users.contact_name as user_name',
            ])
            ->selectRaw($model instanceof StockDelivery ? 'purchase_orders.reference as purchase_order_reference' : 'null as purchase_order_reference');

        return $queryBuilder
            ->orderByDesc('audits.created_at')
            ->orderByDesc('audits.id')
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix, tableName: request()->route()?->getName())
            ->withQueryString();
    }

    /**
     * A note copied onto several purchase orders of a stock delivery is one note: same text, author and minute.
     */
    private function distinctNotes(PurchaseOrder|StockDelivery $model): Builder
    {
        return DB::table('audits')
            ->where('audits.event', AuditEventEnum::NOTE->value)
            ->where(function ($query) use ($model) {
                $query->where(fn ($query) => $query->where('audits.auditable_type', class_basename($model))->where('audits.auditable_id', $model->id));

                if ($model instanceof StockDelivery) {
                    $query->orWhere(fn ($query) => $query->where('audits.auditable_type', 'PurchaseOrder')
                        ->whereIn('audits.auditable_id', $model->purchaseOrders()->pluck('purchase_orders.id')));
                }
            })
            ->groupByRaw("audits.auditable_type, audits.new_values->>'note', audits.user_id, audits.data->>'author', date_trunc('minute', audits.created_at)")
            ->selectRaw('min(audits.id) as id, count(*) as number_documents');
    }

    public function tableStructure(?string $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($prefix) {
            if ($prefix) {
                $table->name($prefix)->pageName($prefix.'Page');
            }

            $table
                ->withGlobalSearch()
                ->withEmptyState(['title' => __('No notes yet')])
                ->column(key: 'created_at', label: __('Date'), canBeHidden: false)
                ->column(key: 'author', label: __('Author'), canBeHidden: false)
                ->column(key: 'note', label: __('Note'), canBeHidden: false);
        };
    }
}
