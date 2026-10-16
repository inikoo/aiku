<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 01 Aug 2025 08:08:18 Central European Summer Time, Trnava, Slovakia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\OrgStock\UI;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\Inventory\WithInventoryAuthorisation;
use App\InertiaTable\InertiaTable;
use App\Models\Inventory\Location;
use App\Models\Inventory\LocationOrgStock;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;

class IndexOrgStocksInLocation extends OrgAction
{
    use WithInventoryAuthorisation;

    public const string IS_PACKED_SQL = 'location_org_stocks.packed_at IS NOT NULL AND location_org_stocks.packed_quantity = location_org_stocks.quantity';

    protected function getPackedElementGroup(Location $location): array
    {
        $counts = DB::table('location_org_stocks')
            ->where('location_id', $location->id)
            ->where('quantity', '>', 0)
            ->selectRaw('count(*) FILTER (WHERE '.self::IS_PACKED_SQL.') as packed, count(*) FILTER (WHERE NOT ('.self::IS_PACKED_SQL.')) as not_packed')
            ->first();

        return [
            'packed' => [
                'label'    => __('Packed'),
                'elements' => [
                    'packed'     => [__('Packed'), $counts->packed],
                    'not_packed' => [__('Not packed'), $counts->not_packed],
                ],
                'engine'   => function ($query, $elements) {
                    $query->whereRaw(in_array('packed', $elements) ? self::IS_PACKED_SQL : 'NOT ('.self::IS_PACKED_SQL.')');
                },
            ],
        ];
    }


    public function handle(Location $location, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereStartWith('org_stocks.code', $value)
                    ->orWhereAnyWordStartWith('org_stocks.name', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(LocationOrgStock::class);

        $queryBuilder->where('location_org_stocks.location_id', $location->id)
            ->leftJoin('org_stocks', 'location_org_stocks.org_stock_id', 'org_stocks.id');

        /* A shelf keeps the SKU it is assigned to even when empty, a goods out bay does not:
           stock is staged there for one partner and once it is picked nothing is left there. */
        if ($location->is_goods_out) {
            $queryBuilder->where('location_org_stocks.quantity', '>', 0)
                ->leftJoin('users as packers', 'location_org_stocks.packed_by_user_id', 'packers.id');

            foreach ($this->getPackedElementGroup($location) as $key => $elementGroup) {
                $queryBuilder->whereElementGroup(
                    key: $key,
                    allowedElements: array_keys($elementGroup['elements']),
                    engine: $elementGroup['engine'],
                    prefix: $prefix
                );
            }
        }

        $packedSelects = $location->is_goods_out ? [
            'location_org_stocks.packed_at',
            'location_org_stocks.packed_quantity',
            DB::raw('COALESCE(packers.contact_name, packers.username) as packed_by'),
        ] : [];

        return $queryBuilder
            ->defaultSort('org_stocks.code')
            ->select([
                'org_stocks.id',
                'org_stocks.code',
                'org_stocks.name',
                'org_stocks.slug',
                'org_stocks.sku_value',
                'location_org_stocks.id as location_org_stock_id',
                'location_org_stocks.type',
                'location_org_stocks.picking_priority',
                'location_org_stocks.value',
                'location_org_stocks.dropshipping_pipe',
                'location_org_stocks.quantity',
                'location_org_stocks.notes',
                ...$packedSelects,
            ])
            ->allowedSorts([AllowedSort::field('code', 'org_stocks.code'), 'type', 'picking_priority', 'value', 'dropshipping_pipe', 'quantity', 'notes', AllowedSort::callback('packed', function ($query, bool $descending) {
                $direction = $descending ? 'DESC' : 'ASC';
                $query->orderByRaw('COALESCE('.self::IS_PACKED_SQL.", false) $direction, location_org_stocks.packed_at $direction");
            })])
            ->allowedFilters([$globalSearch, AllowedFilter::exact('state')])
            ->withPaginator($prefix, tableName: request()->route()->getName())
            ->withQueryString();
    }

    public function tableStructure(Location $location, $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($location, $prefix) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix . 'Page');
            }

            if ($location->is_goods_out) {
                foreach ($this->getPackedElementGroup($location) as $key => $elementGroup) {
                    $table->elementGroup(
                        key: $key,
                        label: $elementGroup['label'],
                        elements: $elementGroup['elements']
                    );
                }
            }

            $table->column(key: 'type', label: ['fal', 'fa-yin-yang'], type: 'icon', canBeHidden: false, sortable: true, searchable: false);
            $table
                ->defaultSort('code')
                ->withGlobalSearch()
                ->column(key: 'code', label: __('Reference'), canBeHidden: false, sortable: true, searchable: true);
            $table
                // ->column(key: 'picking_priority', label: __('picking priority'), canBeHidden: false, sortable: true, searchable: true)
                // ->column(key: 'value', label: __('Sku value'), canBeHidden: false, sortable: true, searchable: true, align: 'right')
             //   ->column(key: 'dropshipping_pipe', label: __('Dropshipping Pipe'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'quantity', label: __('Quantity'), canBeHidden: false, sortable: true, searchable: true, align: 'right')
                ->column(key: 'notes', label: __('Notes'), canBeHidden: false, sortable: true, searchable: true);

            if ($location->is_goods_out) {
                $table->column(key: 'packed', label: __('Packed'), canBeHidden: false, sortable: true, searchable: false);
            }

            $table
                ->column(key: 'actions', label: '', canBeHidden: false, sortable: false, searchable: false, align: 'right');
        };
    }
}
