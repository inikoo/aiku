<?php

/*
    * Author: Vika Aqordi
    * Created on: 2026-10-20 11:00
    * Github: https://github.com/aqordeon
    * Copyright: 2026
*/

namespace App\Actions\SupplyChain\PackingSpec;

use App\Enums\Procurement\PurchaseOrderTransaction\PurchaseOrderTransactionStateEnum;
use App\Enums\SupplyChain\PackingSpec\PackingSpecStatusEnum;
use App\Models\Procurement\PurchaseOrder;
use App\Models\SupplyChain\Agent;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * The lines of a purchase order whose supplier product has no packing spec. They are a warning on the order until the
 * cut-off date set on the agent; from that day the order cannot be submitted until the agent declares them.
 */
class GetPurchaseOrderPackingSpecGaps
{
    use AsAction;

    public const string CUT_OFF_SETTING = 'packing_spec_required_from';

    public const array PARENT_TYPES = ['OrgAgent', 'OrgSupplier'];

    /**
     * @return array{lines: list<array{code: string, name: string|null, org_supplier_product_slug: string|null}>, cut_off: string|null, blocking: bool, organisation_slug: string}|null
     */
    public function handle(PurchaseOrder $purchaseOrder, ?Organisation $viewer = null): ?array
    {
        if (!in_array($purchaseOrder->parent_type, self::PARENT_TYPES)) {
            return null;
        }

        $lines = DB::table('purchase_order_transactions as pot')
            ->join('supplier_products as sp', 'sp.id', 'pot.supplier_product_id')
            ->leftJoin('org_supplier_products as osp', 'osp.id', 'pot.org_supplier_product_id')
            ->where('pot.purchase_order_id', $purchaseOrder->id)
            ->whereNotIn('pot.state', [PurchaseOrderTransactionStateEnum::CANCELLED->value, PurchaseOrderTransactionStateEnum::NOT_RECEIVED->value])
            ->whereNotExists(fn ($query) => $query->from('supplier_product_packing_specs as s')
                ->whereColumn('s.supplier_product_id', 'sp.id')
                ->whereIn('s.status', array_map(fn (PackingSpecStatusEnum $status) => $status->value, PackingSpecStatusEnum::live())))
            ->distinct()
            ->orderBy('sp.code')
            ->get(['sp.code', 'sp.name', 'osp.slug as org_supplier_product_slug'])
            ->map(fn ($row) => (array)$row)
            ->all();

        if (!$lines) {
            return null;
        }

        $cutOff = $this->cutOff($purchaseOrder->agent);

        return [
            'lines'    => $lines,
            'cut_off'  => $cutOff?->toDateString(),
            'blocking' => $cutOff !== null && $cutOff->lte(today()),
            'organisation_slug' => ($viewer ?? $purchaseOrder->organisation)->slug,
        ];
    }

    public function cutOff(?Agent $agent): ?Carbon
    {
        $date = $agent ? Arr::get($agent->settings, self::CUT_OFF_SETTING) : null;

        return $date ? Carbon::parse($date)->startOfDay() : null;
    }

    public function blockingMessage(array $gaps): string
    {
        return __('From :date every product needs a packing spec from the agent before the order is submitted. Missing: :codes', [
            'date'  => Carbon::parse($gaps['cut_off'])->toFormattedDateString(),
            'codes' => implode(', ', array_column($gaps['lines'], 'code')),
        ]);
    }
}
