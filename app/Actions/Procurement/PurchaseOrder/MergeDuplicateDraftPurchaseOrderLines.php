<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 11 Oct 2026 Malaga, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\PurchaseOrder;

use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseOrderTransaction;
use App\Models\Procurement\ShoppingListItem;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Laravel\Nightwatch\Facades\Nightwatch;
use Lorisleiva\Actions\Concerns\AsAction;

class MergeDuplicateDraftPurchaseOrderLines
{
    use AsAction;

    public string $commandSignature = 'procurement:merge-duplicate-draft-lines {--dry-run : only list what would be joined}';

    /**
     * A draft purchase order holds a product once. Where it holds it on several lines (the old
     * shopping list could save the same product twice), they are joined into one line holding the
     * quantity entered last: a second entry was a correction of the first, not more of it. Orders
     * already submitted are history and are left alone.
     *
     * @return array<int, array{purchase_order: string, product: string, quantities: array<int, float>, kept: float}>
     */
    public function handle(bool $dryRun = false): array
    {
        $groups = DB::table('purchase_order_transactions as pot')
            ->join('purchase_orders as po', 'po.id', 'pot.purchase_order_id')
            ->where('po.state', PurchaseOrderStateEnum::IN_PROCESS->value)
            ->whereNull('po.deleted_at')
            ->whereNull('pot.deleted_at')
            ->whereNotNull('pot.supplier_product_id')
            ->groupBy('pot.purchase_order_id', 'pot.supplier_product_id')
            ->havingRaw('count(*) > 1')
            ->select(['pot.purchase_order_id', 'pot.supplier_product_id'])
            ->get();

        $joined = [];

        foreach ($groups as $group) {
            $joined[] = DB::transaction(function () use ($group, $dryRun) {
                $purchaseOrder = PurchaseOrder::whereKey($group->purchase_order_id)->lockForUpdate()->firstOrFail();
                $lines         = $purchaseOrder->purchaseOrderTransactions()
                    ->where('supplier_product_id', $group->supplier_product_id)
                    ->oldest('id')
                    ->get();

                $kept  = $lines->first();
                $last  = (float) $lines->last()->quantity_ordered;

                if (!$dryRun && $lines->count() > 1 && $purchaseOrder->state === PurchaseOrderStateEnum::IN_PROCESS) {
                    $lines->skip(1)->each(function (PurchaseOrderTransaction $duplicate) use ($kept) {
                        ShoppingListItem::where('purchase_order_transaction_id', $duplicate->id)->update(['purchase_order_transaction_id' => $kept->id]);
                        DeletePurchaseOrderTransaction::make()->action($duplicate);
                    });
                    UpdatePurchaseOrderTransactionQuantity::make()->action($kept, ['quantity_ordered' => $last]);
                }

                return [
                    'purchase_order' => $purchaseOrder->reference,
                    'product'        => (string) $kept->supplierProduct?->code,
                    'quantities'     => $lines->map(fn (PurchaseOrderTransaction $line) => (float) $line->quantity_ordered)->all(),
                    'kept'           => $last,
                ];
            });
        }

        return $joined;
    }

    public function asCommand(Command $command): int
    {
        Nightwatch::dontSample();

        $dryRun = (bool) $command->option('dry-run');

        foreach ($this->handle($dryRun) as $joined) {
            $command->info(($dryRun ? 'Would join' : 'Joined')." {$joined['product']} on {$joined['purchase_order']}: ".implode(', ', $joined['quantities'])." → {$joined['kept']}");
        }

        return 0;
    }
}
