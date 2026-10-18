<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 13 Oct 2026 Malaga, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\PartnerShoppingListItem;

use App\Actions\Procurement\OrgPartner\Hydrators\OrgPartnerHydrateShoppingListItems;
use App\Enums\Procurement\ShoppingListItem\ShoppingListItemStateEnum;
use App\Enums\Production\JobOrder\JobOrderStateEnum;
use App\Models\Ordering\Transaction;
use App\Models\Procurement\PartnerShoppingListItem;
use Lorisleiva\Actions\Concerns\AsAction;

class ReturnLoweredQuantityToPartnerShoppingList
{
    use AsAction;

    /**
     * A partner order line never stands for more than it ships: what the buyer's list has ordered
     * against the line beyond its quantity goes back on the list as open. Lines not yet pre-picked
     * give back first, the goods of a pre-picked one are already set aside for the buyer.
     */
    public function handle(Transaction $transaction): void
    {
        $items = PartnerShoppingListItem::query()
            ->where('transaction_id', $transaction->id)
            ->whereNotNull('partner_organisation_id')
            ->where('state', ShoppingListItemStateEnum::ORDERED)
            ->orderByRaw('pre_picked_at is not null')
            ->orderByDesc('id')
            ->get();

        if ($items->isEmpty()) {
            return;
        }

        if ((float) $transaction->quantity_ordered <= 0) {
            ReopenPartnerShoppingListItemsOfTransactions::run([$transaction->id]);

            return;
        }

        $productOrgStocks = $transaction->model?->orgStocks ?? collect();

        foreach ($items->groupBy('stock_id') as $stockId => $stockItems) {
            $skosPerProductUnit = (float) ($productOrgStocks->firstWhere('stock_id', $stockId)?->pivot->quantity ?? 1);
            if ($skosPerProductUnit <= 0) {
                $skosPerProductUnit = 1;
            }

            $excess = round((float) $stockItems->sum('quantity') - (float) $transaction->quantity_ordered * $skosPerProductUnit, 3);

            foreach ($stockItems as $item) {
                if ($excess <= 0) {
                    break;
                }

                $returned = min($excess, (float) $item->quantity);
                $excess   = round($excess - $returned, 3);

                if ($returned >= (float) $item->quantity) {
                    ReopenPartnerShoppingListItemsOfTransactions::make()->reopen($item);
                } else {
                    $this->splitOff($item, $returned);
                }
            }
        }

        $items->unique('org_partner_id')->each(
            fn (PartnerShoppingListItem $item) => OrgPartnerHydrateShoppingListItems::dispatch($item->orgPartner)
        );
    }

    private function splitOff(PartnerShoppingListItem $item, float $returned): void
    {
        $jobOrderId = in_array($item->jobOrder?->state, [JobOrderStateEnum::IN_PROCESS, JobOrderStateEnum::SUBMITTED, JobOrderStateEnum::CONFIRMED], true) ? $item->job_order_id : null;

        $openSibling = $jobOrderId || $item->pre_picked_at
            ? null
            : PartnerShoppingListItem::openPartnerLineFor($item->org_partner_id, $item->org_stock_id)->first();

        if ($openSibling) {
            $openSibling->increment('quantity', $returned);
        } else {
            PartnerShoppingListItem::create([
                ...$item->only([
                    'group_id',
                    'organisation_id',
                    'org_partner_id',
                    'partner_organisation_id',
                    'stock_id',
                    'org_stock_id',
                    'priority',
                    'needed_by',
                    'notes',
                    'added_by_user_id',
                    'pre_picked_at',
                ]),
                'job_order_id' => $jobOrderId,
                'parent_id'    => $item->id,
                'quantity'     => $returned,
                'state'        => ShoppingListItemStateEnum::OPEN,
                'created_at'   => $item->created_at,
            ]);
        }

        $item->update(['quantity' => (float) $item->quantity - $returned]);
    }
}
