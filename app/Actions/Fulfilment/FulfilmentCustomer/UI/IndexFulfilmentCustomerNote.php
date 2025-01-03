<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 03-01-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Actions\Fulfilment\FulfilmentCustomer\UI;

use App\Actions\SysAdmin\User\Traits\WithFormattedUserHistories;
use App\Enums\Helpers\Audit\AuditEventEnum;
use App\InertiaTable\InertiaTable;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Pagination\LengthAwarePaginator;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use OwenIt\Auditing\Models\Audit;
use Spatie\QueryBuilder\AllowedFilter;

class IndexFulfilmentCustomerNote
{
    use AsAction;
    use WithAttributes;
    use WithFormattedUserHistories;

    public string $model;

    public function handle(FulfilmentCustomer $fulfilmentCustomer, $prefix = null): LengthAwarePaginator|array|bool
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('new_values->note', 'like', "%{$value}%");
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(Audit::class);

        $queryBuilder->orderBy('id', 'DESC')
            ->where('auditable_type', 'Customer')
            ->where('event', AuditEventEnum::CUSTOMER_NOTE)
            ->where('auditable_id', $fulfilmentCustomer->customer_id)
            ->where('customer_id', $fulfilmentCustomer->customer_id);

        return $queryBuilder
            ->select(
                'audits.created_at as datetime',
                'audits.new_values->note as note'
            )
            ->defaultSort('-audits.created_at')
            ->allowedSorts(['created_at'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure($prefix = null, ?array $exportLinks = null): Closure
    {
        return function (InertiaTable $table) use ($exportLinks, $prefix) {

            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            $table
                ->withGlobalSearch()
                ->withExportLinks($exportLinks)
                ->column(key: 'note', label: __('Note'), canBeHidden: false, sortable: true)
                ->column(key: 'datetime', label: __('Date'), canBeHidden: false, sortable: true)
                ->defaultSort('-datetime');
        };
    }
}
