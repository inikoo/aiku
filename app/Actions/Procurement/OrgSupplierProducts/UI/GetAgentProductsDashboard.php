<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\OrgSupplierProducts\UI;

use App\Actions\SupplyChain\SupplierStockFamily\GetProductionCalendarAdvice;
use App\Models\SupplyChain\Agent;
use App\Models\SupplyChain\Supplier;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsObject;

/**
 * How much of the data freight costing and slotting depend on an agent has filled in.
 */
class GetAgentProductsDashboard
{
    use AsObject;
    use WithAgentProductFacts;

    /**
     * Families whose production calendar says to order this month, those about to stop producing first.
     *
     * @return array<int, array<string, mixed>>
     */
    private function orderNow(Agent $agent): array
    {
        $products = DB::query()->fromSub($this->agentProductFacts($agent), 'f')
            ->groupBy('f.supplier_id', 'f.stock_family_id')
            ->select(['f.supplier_id', 'f.stock_family_id'])
            ->selectRaw('count(*) as number_products');

        return DB::table('supplier_stock_families as ssf')
            ->join('suppliers as s', 's.id', 'ssf.supplier_id')
            ->join('stock_families as sf', 'sf.id', 'ssf.stock_family_id')
            ->joinSub($products, 'p', fn ($join) => $join->on('p.supplier_id', 'ssf.supplier_id')->on('p.stock_family_id', 'ssf.stock_family_id'))
            ->where('s.agent_id', $agent->id)
            ->whereRaw("ssf.production_calendar <> '{}'::jsonb")
            ->select(['s.id as supplier_id', 's.code as supplier_code', 'sf.id as stock_family_id', 'sf.code as family_code', 'sf.name as family_name', 'ssf.production_calendar', 'p.number_products'])
            ->selectRaw('coalesce(ssf.estimated_lead_time_days, '.Supplier::DEFAULT_LEAD_TIME_SQL.') as lead_time_days')
            ->get()
            ->map(fn ($row) => [
                'supplier_id'     => $row->supplier_id,
                'supplier_code'   => $row->supplier_code,
                'stock_family_id' => $row->stock_family_id,
                'family_code'     => $row->family_code,
                'family_name'     => $row->family_name,
                'number_products' => (int) $row->number_products,
                'advice'          => GetProductionCalendarAdvice::run(json_decode($row->production_calendar, true) ?? [], $row->lead_time_days !== null ? (int) $row->lead_time_days : null),
            ])
            ->filter(fn (array $row) => in_array($row['advice']['status'], [GetProductionCalendarAdvice::ORDER_BEFORE_STOP, GetProductionCalendarAdvice::GOOD_TIME], true))
            ->sortBy(fn (array $row) => [$row['advice']['status'] === GetProductionCalendarAdvice::ORDER_BEFORE_STOP ? 0 : 1, -$row['number_products']])
            ->take(20)
            ->values()
            ->all();
    }

    public function handle(Agent $agent): array
    {
        $products = DB::query()->fromSub($this->agentProductFacts($agent), 'f')
            ->selectRaw('count(*) as total')
            ->selectRaw('count(*) filter (where f.cbm > 0 and f.lead_time_days is not null) as ready_to_cost')
            ->selectRaw('count(*) filter (where f.cbm > 0) as with_cbm')
            ->selectRaw('count(*) filter (where f.lead_time_days is not null) as with_lead_time')
            ->selectRaw('count(*) filter (where f.measured_lead_time_days is not null) as with_measured_lead_time')
            ->selectRaw('count(*) filter (where f.lead_time_confirmed_at is not null) as with_lead_time_confirmed')
            ->selectRaw('count(*) filter (where f.moq is not null) as with_moq')
            ->selectRaw('count(*) filter (where f.carton_weight > 0) as with_carton_gross_weight')
            ->selectRaw('count(*) filter (where f.carton_net_weight > 0) as with_carton_net_weight')
            ->selectRaw('count(*) filter (where f.carton_dimensions is not null) as with_carton_dimensions')
            ->selectRaw('count(*) filter (where f.moq is not null and f.carton_weight > 0 and f.carton_net_weight > 0) as with_moq_and_weights')
            ->selectRaw("count(*) filter (where f.availability_status <> 'available') as not_available")
            ->selectRaw("count(*) filter (where f.availability_status = 'unavailable' and f.unavailable_reason is null) as not_available_without_reason")
            ->selectRaw('count(*) filter (where f.stock_family_id is null) as without_family')
            ->selectRaw('count(*) filter (where f.lead_time_days is not null and f.measured_lead_time_days is not null) as with_both_lead_times')
            ->selectRaw('round(avg(f.lead_time_days) filter (where f.measured_lead_time_days is not null)) as average_estimated_lead_time')
            ->selectRaw('round(avg(f.measured_lead_time_days) filter (where f.lead_time_days is not null)) as average_measured_lead_time')
            ->selectRaw('count(*) filter (where f.measured_lead_time_days > f.lead_time_days * 1.25) as arrived_late')
            ->first();

        $suppliers = DB::table('suppliers as s')
            ->whereIn('s.id', $this->agentProductFacts($agent)->select('sp.supplier_id'))
            ->selectRaw('count(*) as total')
            ->selectRaw("count(*) filter (where coalesce(s.phone, '') <> '') as with_phone")
            ->selectRaw("count(*) filter (where coalesce(s.email, '') <> '') as with_email")
            ->selectRaw("count(*) filter (where coalesce(s.wechat, '') <> '' or coalesce(s.whatsapp, '') <> '') as with_wechat_or_whatsapp")
            ->first();

        $total = (int) $products->total;

        $productBar = fn (string $key, string $label, int $filled) => ['key' => $key, 'label' => $label, 'filled' => $filled, 'total' => $total, 'scope' => 'products'];
        $supplierBar = fn (string $key, string $label, int $filled) => ['key' => $key, 'label' => $label, 'filled' => $filled, 'total' => (int) $suppliers->total, 'scope' => 'suppliers'];

        return [
            'order_now'   => $this->orderNow($agent),
            'kpis'        => [
                'total'                        => $total,
                'ready_to_cost'                => (int) $products->ready_to_cost,
                'missing_cbm'                  => $total - (int) $products->with_cbm,
                'missing_moq_or_weights'       => $total - (int) $products->with_moq_and_weights,
                'not_available'                => (int) $products->not_available,
                'not_available_without_reason' => (int) $products->not_available_without_reason,
                'without_family'               => (int) $products->without_family,
                'suppliers'                    => (int) $suppliers->total,
                'lead_time'                    => [
                    'products'           => (int) $products->with_both_lead_times,
                    'average_estimated'  => $products->average_estimated_lead_time !== null ? (int) $products->average_estimated_lead_time : null,
                    'average_measured'   => $products->average_measured_lead_time !== null ? (int) $products->average_measured_lead_time : null,
                    'arrived_late'       => (int) $products->arrived_late,
                ],
            ],
            'fill_rates'  => [
                $productBar('cbm', __('CBM'), (int) $products->with_cbm),
                $productBar('lead_time', __('Lead time estimated'), (int) $products->with_lead_time),
                $productBar('measured_lead_time', __('Lead time measured'), (int) $products->with_measured_lead_time),
                $productBar('lead_time_confirmed', __('Lead time confirmed'), (int) $products->with_lead_time_confirmed),
                $productBar('moq', __('MOQ'), (int) $products->with_moq),
                $productBar('carton_gross_weight', __('Carton gross weight'), (int) $products->with_carton_gross_weight),
                $productBar('carton_net_weight', __('Carton net weight'), (int) $products->with_carton_net_weight),
                $productBar('carton_dimensions', __('Carton dimensions'), (int) $products->with_carton_dimensions),
                $supplierBar('phone', __('Supplier phone'), (int) $suppliers->with_phone),
                $supplierBar('email', __('Supplier email'), (int) $suppliers->with_email),
                $supplierBar('wechat_or_whatsapp', __('Supplier WeChat or WhatsApp'), (int) $suppliers->with_wechat_or_whatsapp),
            ],
        ];
    }
}
