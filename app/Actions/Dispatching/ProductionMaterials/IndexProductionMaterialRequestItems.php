<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 Malaga, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatching\ProductionMaterials;

use App\Enums\Production\ProductionMaterialRequest\ProductionMaterialRequestStateEnum;
use App\Enums\Production\ProductionMaterialRequestItem\ProductionMaterialRequestItemStateEnum;
use App\InertiaTable\InertiaTable;
use App\Models\Inventory\Warehouse;
use App\Models\Production\ProductionMaterialRequestItem;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;
use Spatie\QueryBuilder\AllowedFilter;

class IndexProductionMaterialRequestItems
{
    use AsAction;

    /**
     * Shelves a material can be taken from for the factory, best first: never the factory
     * itself nor a partner's goods out bay. Needs production_material_request_items and
     * productions in scope.
     */
    private const string SHELVES_SQL = 'from location_org_stocks shelf
        join locations on locations.id = shelf.location_id
        where shelf.org_stock_id = production_material_request_items.org_stock_id
            and shelf.location_id is distinct from productions.factory_location_id
            and not locations.is_goods_out
            and shelf.quantity > 0';

    public function handle(Warehouse $warehouse, string $prefix): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereStartWith('org_stocks.code', $value)
                    ->orWhereAnyWordStartWith('org_stocks.name', $value)
                    ->orWhereStartWith('raw_materials.code', $value);
            });
        });

        InertiaTable::updateQueryBuilderParameters($prefix);

        return QueryBuilder::for(self::pendingItems($warehouse))
            ->join('org_stocks', 'org_stocks.id', 'production_material_request_items.org_stock_id')
            ->join('raw_materials', 'raw_materials.id', 'production_material_request_items.raw_material_id')
            ->leftJoin('locations as factory', 'factory.id', 'productions.factory_location_id')
            ->select([
                'production_material_request_items.*',
                'production_material_requests.plan_date',
                'productions.code as production_code',
                'productions.factory_location_id',
                'factory.code as factory_location_code',
                'org_stocks.code as org_stock_code',
                'org_stocks.name as org_stock_name',
                'raw_materials.code as raw_material_code',
                DB::raw('(select locations.code '.self::SHELVES_SQL.' order by shelf.picking_priority nulls last, shelf.quantity desc limit 1) as location_code'),
                DB::raw('(select locations.sort_code '.self::SHELVES_SQL.' order by shelf.picking_priority nulls last, shelf.quantity desc limit 1) as location_sort_code'),
                DB::raw('coalesce((select sum(shelf.quantity) '.self::SHELVES_SQL.'), 0) as warehouse_skos'),
            ])
            ->defaultSort('location_sort_code')
            ->allowedSorts(['location_sort_code', 'org_stock_code', 'plan_date'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix, tableName: request()->route()?->getName())
            ->withQueryString()
            ->through(fn (ProductionMaterialRequestItem $item) => [
                'id'               => $item->id,
                'plan_date'        => $item->plan_date,
                'production_code'  => $item->production_code,
                'to_location'      => $item->factory_location_code,
                'org_stock_code'   => $item->org_stock_code,
                'org_stock_name'   => $item->org_stock_name,
                'location_code'    => $item->location_code,
                'boxes'            => $item->boxes,
                'box_units'        => (float) $item->box_units,
                'skos_to_bring'    => (float) $item->skos_to_bring,
                'delivered_skos'   => (float) $item->delivered_skos,
                'warehouse_skos'   => (float) $item->warehouse_skos,
                'warehouse_short'  => (float) $item->warehouse_skos < (float) $item->skos_to_bring - (float) $item->delivered_skos,
                'job_orders'       => $item->job_orders,
                'from_locations'   => self::fromLocations($item),
            ]);
    }

    public static function pendingItems(Warehouse $warehouse): Builder
    {
        return ProductionMaterialRequestItem::query()
            ->join('production_material_requests', 'production_material_requests.id', 'production_material_request_items.production_material_request_id')
            ->join('productions', 'productions.id', 'production_material_requests.production_id')
            ->where('production_material_requests.warehouse_id', $warehouse->id)
            ->where('production_material_requests.state', ProductionMaterialRequestStateEnum::SUBMITTED)
            ->where('production_material_request_items.state', ProductionMaterialRequestItemStateEnum::PENDING);
    }

    /**
     * @return array<int, array{location_org_stock_id: int, code: string, quantity: float}>
     */
    private static function fromLocations(ProductionMaterialRequestItem $item): array
    {
        return DB::table('location_org_stocks as shelf')
            ->join('locations', 'locations.id', 'shelf.location_id')
            ->where('shelf.org_stock_id', $item->org_stock_id)
            ->when($item->factory_location_id, fn ($query, $factoryLocationId) => $query->where('shelf.location_id', '!=', $factoryLocationId))
            ->where('locations.is_goods_out', false)
            ->where('shelf.quantity', '>', 0)
            ->orderByRaw('shelf.picking_priority nulls last')
            ->orderByDesc('shelf.quantity')
            ->limit(5)
            ->get(['shelf.id as location_org_stock_id', 'locations.code', 'shelf.quantity'])
            ->map(fn ($row) => [
                'location_org_stock_id' => (int) $row->location_org_stock_id,
                'code'                  => $row->code,
                'quantity'              => (float) $row->quantity,
            ])
            ->all();
    }

    public function tableStructure(string $prefix): Closure
    {
        return function (InertiaTable $table) use ($prefix) {
            $table->name($prefix)->pageName($prefix.'Page');
            $table->withGlobalSearch();
            $table->withEmptyState([
                'icons' => ['fal fa-industry'],
                'title' => __('Production has asked for nothing'),
            ]);

            $table->column(key: 'location_sort_code', label: __('From'), canBeHidden: false, sortable: true);
            $table->column(key: 'org_stock_code', label: __('SKO'), canBeHidden: false, sortable: true);
            $table->column(key: 'bring', label: __('Bring'), canBeHidden: false);
            $table->column(key: 'plan_date', label: __('For'), canBeHidden: false, sortable: true);
            $table->column(key: 'actions', label: __('To do actions'), canBeHidden: false);
            $table->defaultSort('location_sort_code');
        };
    }
}
