<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\Supplier;

use App\Actions\Procurement\OrgSupplier\Hydrators\OrgSupplierHydrateOrgSupplierProducts;
use App\Actions\Procurement\OrgSupplier\Hydrators\OrgSupplierHydratePurchaseOrders;
use App\Actions\Procurement\OrgSupplier\Hydrators\OrgSupplierHydrateStockDeliveries;
use App\Actions\SupplyChain\Agent\Hydrators\AgentHydrateSupplierProducts;
use App\Actions\SupplyChain\Agent\Hydrators\AgentHydrateSuppliers;
use App\Models\Procurement\OrgSupplier;
use App\Models\Procurement\PurchaseOrder;
use App\Models\SupplyChain\Supplier;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Folds a duplicate supplier record into the one kept: its products, purchase orders, containers, messages,
 * invoices and attachments move over, per organisation onto the kept supplier's org supplier, and the duplicate
 * is archived, never deleted. Where both are on one agent order, the duplicate's supplier order hands its lines and
 * stage dates to the kept one and is removed, unless it carries costing, deliveries, deposits or notes. Refused when the two price in different currencies or share a product code,
 * since that would change what a cost means or need the products merged first.
 */
class MergeSuppliers
{
    use AsAction;

    private const array SUPPLIER_TABLES = [
        'purchase_orders',
        'stock_deliveries',
        'agent_supplier_purchase_orders',
        'shopping_list_items',
        'supplier_declarations',
        'supplier_invoices',
        'supplier_messages',
    ];

    private const array AGENT_ORDER_STAGE_DATES = [
        'estimated_received_at',
        'proposed_ready_at',
        'approved_ready_at',
        'sample_approved_at',
        'produced_at',
        'handed_over_at',
        'qc_passed_at',
        'compliance_complete_at',
    ];

    public function handle(Supplier $duplicate, Supplier $kept): Supplier
    {
        $this->ensureCanMerge($duplicate, $kept);

        $touchedOrgSuppliers = DB::transaction(function () use ($duplicate, $kept) {
            $productIds = $duplicate->supplierProducts()->pluck('id');
            DB::table('supplier_products')->whereIn('id', $productIds)->update(['supplier_id' => $kept->id, 'agent_id' => $kept->agent_id]);

            $touched = collect();
            foreach ($duplicate->orgSuppliers()->get() as $from) {
                $to = OrgSupplier::where('supplier_id', $kept->id)->where('organisation_id', $from->organisation_id)->first();

                if (!$to) {
                    $from->update(['supplier_id' => $kept->id]);
                    $this->renameParent($from, $kept);
                    $touched->push($from);

                    continue;
                }

                DB::table('org_supplier_products')->where('org_supplier_id', $from->id)->update(['org_supplier_id' => $to->id]);
                DB::table('supplier_messages')->where('org_supplier_id', $from->id)->update(['org_supplier_id' => $to->id]);
                foreach (['purchase_orders', 'stock_deliveries'] as $table) {
                    DB::table($table)->where('parent_type', 'OrgSupplier')->where('parent_id', $from->id)
                        ->update(['parent_id' => $to->id, 'parent_code' => $kept->code, 'parent_name' => $kept->name]);
                }
                $from->update(['status' => false]);
                $touched->push($from, $to);
            }

            $this->foldSharedAgentOrders($duplicate, $kept);

            foreach (self::SUPPLIER_TABLES as $table) {
                DB::table($table)->where('supplier_id', $duplicate->id)->update(['supplier_id' => $kept->id]);
            }

            DB::table('supplier_stock_families')->where('supplier_id', $duplicate->id)
                ->whereNotIn('stock_family_id', DB::table('supplier_stock_families')->where('supplier_id', $kept->id)->select('stock_family_id'))
                ->update(['supplier_id' => $kept->id]);
            foreach (['suppliers', 'supplier_products'] as $table) {
                DB::table($table)->where('packaging_supplier_id', $duplicate->id)->update(['packaging_supplier_id' => $kept->id]);
            }
            DB::table('model_has_attachments')->where('model_type', 'Supplier')->where('model_id', $duplicate->id)->update(['model_id' => $kept->id]);
            foreach (['supplier_agreed_terms', 'procurement_notes'] as $table) {
                DB::table($table)->where('subject_type', 'Supplier')->where('subject_id', $duplicate->id)->update(['subject_id' => $kept->id]);
            }

            $duplicate->update([
                'status'      => false,
                'archived_at' => now(),
                'data'        => [...($duplicate->data ?? []), 'merged_into_supplier_id' => $kept->id],
            ]);

            return $touched;
        });

        $this->hydrate($duplicate, $kept, $touchedOrgSuppliers);

        return $kept->refresh();
    }

    private function ensureCanMerge(Supplier $duplicate, Supplier $kept): void
    {
        if ($duplicate->id === $kept->id) {
            throw ValidationException::withMessages(['target_supplier_id' => __('A supplier cannot be merged into itself')]);
        }

        if ($duplicate->currency_id !== $kept->currency_id) {
            throw ValidationException::withMessages(['target_supplier_id' => __('The two suppliers price in different currencies')]);
        }

        $sharedCodes = $duplicate->supplierProducts()->withTrashed()
            ->whereIn('code', $kept->supplierProducts()->withTrashed()->select('code'))
            ->pluck('code');

        if ($sharedCodes->isNotEmpty()) {
            throw ValidationException::withMessages([
                'target_supplier_id' => __('Both suppliers have products with the codes :codes, merge those products first', ['codes' => $sharedCodes->implode(', ')]),
            ]);
        }

        $sharedAgentOrders = $this->sharedAgentOrders($duplicate, $kept)
            ->join('purchase_orders', 'purchase_orders.id', '=', 'duplicate_orders.purchase_order_id')
            ->where(function ($query) {
                $query->where('duplicate_orders.is_costed', true)
                    ->orWhere('duplicate_orders.number_stock_deliveries', '>', 0)
                    ->orWhere('duplicate_orders.deposit_amount', '>', 0)
                    ->orWhereNotNull('duplicate_orders.deposit_paid_at')
                    ->orWhereNotNull('duplicate_orders.balance_paid_at')
                    ->orWhere('duplicate_orders.notes', '<>', '')
                    ->orWhereExists(fn ($deposits) => $deposits->from('aspo_deposits')->whereColumn('aspo_deposits.agent_supplier_purchase_order_id', 'duplicate_orders.id'));
            })
            ->pluck('purchase_orders.reference');

        if ($sharedAgentOrders->isNotEmpty()) {
            throw ValidationException::withMessages([
                'target_supplier_id' => __('Both suppliers are on the agent orders :references, sort those orders out first', ['references' => $sharedAgentOrders->implode(', ')]),
            ]);
        }
    }

    private function sharedAgentOrders(Supplier $duplicate, Supplier $kept): Builder
    {
        return DB::table('agent_supplier_purchase_orders as duplicate_orders')
            ->join('agent_supplier_purchase_orders as kept_orders', function ($join) use ($kept) {
                $join->on('kept_orders.purchase_order_id', '=', 'duplicate_orders.purchase_order_id')
                    ->where('kept_orders.supplier_id', $kept->id)
                    ->whereNull('kept_orders.deleted_at');
            })
            ->where('duplicate_orders.supplier_id', $duplicate->id)
            ->whereNull('duplicate_orders.deleted_at');
    }

    private function foldSharedAgentOrders(Supplier $duplicate, Supplier $kept): void
    {
        $pairs = $this->sharedAgentOrders($duplicate, $kept)->select('duplicate_orders.id as duplicate_id', 'kept_orders.id as kept_id')->get();

        foreach ($pairs as $pair) {
            DB::table('purchase_order_transactions')->where('agent_supplier_purchase_order_id', $pair->duplicate_id)
                ->update(['agent_supplier_purchase_order_id' => $pair->kept_id]);

            $duplicateDates = DB::table('agent_supplier_purchase_orders')->where('id', $pair->duplicate_id)->first(self::AGENT_ORDER_STAGE_DATES);
            foreach (self::AGENT_ORDER_STAGE_DATES as $column) {
                DB::table('agent_supplier_purchase_orders')->where('id', $pair->kept_id)->whereNull($column)->update([$column => $duplicateDates->$column]);
            }

            DB::table('agent_supplier_purchase_orders')->where('id', $pair->duplicate_id)->update(['deleted_at' => now()]);
        }
    }

    private function renameParent(OrgSupplier $orgSupplier, Supplier $kept): void
    {
        foreach (['purchase_orders', 'stock_deliveries'] as $table) {
            DB::table($table)->where('parent_type', 'OrgSupplier')->where('parent_id', $orgSupplier->id)
                ->update(['parent_code' => $kept->code, 'parent_name' => $kept->name]);
        }
    }

    private function hydrate(Supplier $duplicate, Supplier $kept, $touchedOrgSuppliers): void
    {
        foreach ([$duplicate, $kept] as $supplier) {
            HydrateSuppliers::run($supplier);
        }

        foreach ($touchedOrgSuppliers->unique('id') as $orgSupplier) {
            OrgSupplierHydrateOrgSupplierProducts::run($orgSupplier);
            OrgSupplierHydratePurchaseOrders::run($orgSupplier);
            OrgSupplierHydrateStockDeliveries::run($orgSupplier);
        }

        if ($kept->agent) {
            AgentHydrateSuppliers::dispatch($kept->agent);
            AgentHydrateSupplierProducts::dispatch($kept->agent);
        }

        PurchaseOrder::where('supplier_id', $kept->id)->searchable();
    }
}
