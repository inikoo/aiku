<?php
/*
 * author Arya Permana - Kirin
 * created on 16-10-2024-10h-54m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\CRM\BackInStockReminder\UI;

use App\Actions\OrgAction;
use App\Http\Resources\CRM\CustomerBackInStockRemindersResource;
use App\Http\Resources\CRM\CustomerFavouritesResource;
use App\InertiaTable\InertiaTable;
use App\Models\CRM\Customer;
use App\Models\CRM\Favourite;
use App\Models\Reminder\BackInStockReminder;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexCustomerBackInStockReminders extends OrgAction
{
    private Customer $parent;

    public function handle(Customer $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereStartWith('products.code', $value)
                    ->orWhereAnyWordStartWith('products.name', 'ILIKE', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }


        $query = QueryBuilder::for(BackInStockReminder::class);

        $query->where('back_to_stock_reminders.customer_id', $parent->id);

        $query->leftJoin('products', 'back_to_stock_reminders.product_id', '=', 'products.id');

        return $query->defaultSort('products.code')
            ->select([
                'products.id',
                'products.slug',
                'products.code',
                'products.name',
                'products.description',
                'products.price',
                'products.image_id',
            ])
            ->allowedSorts(['code', 'name'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(Customer $parent, $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($parent, $prefix) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            if ($prefix) {
                InertiaTable::updateQueryBuilderParameters($prefix);
            }


            $stats     = $parent->stats;
            $noResults = __("Customer has no reminders");


            $table
                ->withGlobalSearch()
                ->withEmptyState(
                    [
                        'title' => $noResults,
                    ]
                );


            $table->column(key: 'code', label: __('code'), canBeHidden: false, searchable: true);
            $table->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true);
        };
    }

    public function authorize(ActionRequest $request): bool
    {

        $this->canEdit = $request->user()->hasPermissionTo("crm.{$this->shop->id}.view");

        return $request->user()->hasPermissionTo("crm.{$this->organisation->id}.view");
    }

    public function jsonResponse(LengthAwarePaginator $reminders): AnonymousResourceCollection
    {
        return CustomerBackInStockRemindersResource::collection($reminders);
    }

}
