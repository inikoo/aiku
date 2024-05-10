<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 13 Oct 2023 09:10:58 Malaysia Time, Office, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\History;

use App\Actions\SysAdmin\User\Traits\WithFormattedUserHistories;
use App\InertiaTable\InertiaTable;
use Closure;
use Illuminate\Pagination\LengthAwarePaginator;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use OwenIt\Auditing\Models\Audit;
use Spatie\QueryBuilder\AllowedFilter;
use App\Services\QueryBuilder;

class IndexModuleHistory
{
    use AsAction;
    use WithAttributes;
    use WithFormattedUserHistories;

    public string $model;

    public function handle(array $tags, $prefix = null): LengthAwarePaginator|array|bool
    {

        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereAnyWordStartWith('user_type', $value)
                    ->orWhereStartWith('user_type', $value)
                    ->orWhereStartWith('url', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(Audit::class);

        $queryBuilder->whereJsonContains('tags', [$tags]);


        $queryBuilder->orderBy('id', 'DESC');


        return $queryBuilder
            ->defaultSort('audits.created_at')
            ->allowedSorts(['auditable_id', 'auditable_type', 'user_type', 'url'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(?array $exportLinks = null): Closure
    {
        return function (InertiaTable $table) use ($exportLinks) {
            $table
                ->name('hst')
                ->pageName('historyPage')
                ->withGlobalSearch()
                ->withExportLinks($exportLinks)
                ->column(key: 'datetime', label: __('Date & Time'), canBeHidden: false, sortable: true)
                ->column(key: 'user_id', label: __('User'), canBeHidden: false, sortable: true)

                //->column(key: 'ip_address', label: __('IP Address'), canBeHidden: false, sortable: true, searchable: true)
                //->column(key: 'url', label: __('URL'), canBeHidden: false, sortable: true, searchable: true)
                //->column(key: 'old_values', label: __('Old Values'), canBeHidden: false, sortable: true)
                //->column(key: 'new_values', label: __('New Values'), canBeHidden: false, sortable: true)
                ->column(key: 'event', label: __('Action'), canBeHidden: false, sortable: true)
        //        ->column(key: 'auditable_type', label: __('Module'), canBeHidden: false)
                ->defaultSort('ip_address');
        };
    }
}
