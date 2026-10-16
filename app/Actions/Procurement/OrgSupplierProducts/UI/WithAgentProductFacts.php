<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\OrgSupplierProducts\UI;

use App\Enums\SupplyChain\SupplierProduct\SupplierProductStateEnum;
use App\Models\SupplyChain\Agent;
use App\Models\SupplyChain\Supplier;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * One row per active supplier product of an agent with what freight costing and slotting need,
 * values the product does not set taken from its family at the supplier, then from the supplier.
 */
trait WithAgentProductFacts
{
    protected function agentProductFacts(Agent $agent): Builder
    {
        $firstStock = DB::table('stock_has_supplier_products as shs')
            ->join('stocks as st', 'st.id', 'shs.stock_id')
            ->whereColumn('shs.supplier_product_id', 'sp.id')
            ->orderBy('shs.id')
            ->limit(1)
            ->select(['st.stock_family_id', 'st.code as internal_code']);

        return DB::table('supplier_products as sp')
            ->join('suppliers as s', 's.id', 'sp.supplier_id')
            ->leftJoinLateral($firstStock, 'fam')
            ->leftJoin('stock_families as sf', 'sf.id', 'fam.stock_family_id')
            ->leftJoin('supplier_stock_families as ssf', function ($join) {
                $join->on('ssf.supplier_id', 'sp.supplier_id')->on('ssf.stock_family_id', 'fam.stock_family_id');
            })
            ->where('sp.agent_id', $agent->id)
            ->where('sp.state', SupplierProductStateEnum::ACTIVE->value)
            ->whereNull('sp.deleted_at')
            ->select([
                'sp.id',
                'sp.supplier_id',
                'fam.stock_family_id',
                'fam.internal_code',
                'sf.code as family_code',
                's.is_generic',
                's.phone',
                's.email',
                's.wechat',
                's.whatsapp',
                'sp.cbm',
                'sp.carton_dimensions',
                'sp.carton_weight',
                'sp.carton_net_weight',
                'sp.measured_lead_time_days',
                'sp.lead_time_confirmed_at',
                'sp.availability_status',
                'sp.unavailable_reason',
            ])
            ->selectRaw('coalesce(sp.minimum_carton_order, ssf.minimum_carton_order) as moq')
            ->selectRaw('coalesce(sp.estimated_lead_time_days, ssf.estimated_lead_time_days, '.Supplier::DEFAULT_LEAD_TIME_SQL.') as lead_time_days');
    }

    /**
     * How many of the 7 product checks a fact row passes; the supplier contact and packaging checks are counted on the supplier.
     */
    protected function completenessSql(string $alias = 'f'): string
    {
        $checks = [
            "$alias.cbm > 0",
            "$alias.carton_dimensions is not null",
            "$alias.carton_weight > 0",
            "$alias.carton_net_weight > 0",
            "$alias.moq is not null",
            "$alias.lead_time_days is not null",
            "$alias.lead_time_confirmed_at is not null",
        ];

        return '((' . implode(' + ', array_map(fn (string $check) => "coalesce($check, false)::int", $checks)) . ') * 100.0 / ' . count($checks) . ')';
    }
}
