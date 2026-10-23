<?php

/*
    * Author: Vika Aqordi
    * Created on: 2026-10-23 09:57
    * Github: https://github.com/aqordeon
    * Copyright: 2026
*/

namespace App\Actions\Procurement\PurchaseOrderTransaction;

use App\Actions\OrgAction;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseOrderTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;

/**
 * The agent packs several lines of a purchase order at once, each as PackPurchaseOrderTransaction does. Lines with
 * nothing received waiting to be packed are left as they are.
 */
class PackPurchaseOrderTransactions extends OrgAction
{
    private PurchaseOrder $purchaseOrder;

    /**
     * @param  array<int, int>  $transactionIds
     */
    public function handle(PurchaseOrder $purchaseOrder, array $transactionIds): int
    {
        $transactions = $purchaseOrder->purchaseOrderTransactions()->whereIn('id', $transactionIds)->get();

        abort_unless($transactions->count() === count(array_unique($transactionIds)), 404);

        $packable = $this->packable($transactions);

        DB::transaction(function () use ($packable) {
            $packable->each(fn (PurchaseOrderTransaction $transaction) => PackPurchaseOrderTransaction::make()->handle($transaction));
        });

        return $packable->count();
    }

    /**
     * @param  Collection<int, PurchaseOrderTransaction>  $transactions
     *
     * @return Collection<int, PurchaseOrderTransaction>
     */
    private function packable(Collection $transactions): Collection
    {
        $withStock = PackPurchaseOrderTransaction::receivedStocks()
            ->whereIn('agent_warehouse_stocks.purchase_order_transaction_id', $transactions->pluck('id'))
            ->distinct()
            ->pluck('agent_warehouse_stocks.purchase_order_transaction_id');

        return $transactions->filter(fn (PurchaseOrderTransaction $transaction) => $withStock->contains($transaction->id))->values();
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        $agentOrganisationId = $this->purchaseOrder->isAgentOrder() ? $this->purchaseOrder->agent?->organisation_id : null;

        return $agentOrganisationId !== null && $request->user()->authTo("procurement.$agentOrganisationId.edit");
    }

    public function rules(): array
    {
        return [
            'transactions'   => ['required', 'array', 'min:1', 'max:500'],
            'transactions.*' => ['integer', 'distinct'],
        ];
    }

    public function asController(PurchaseOrder $purchaseOrder, ActionRequest $request): int
    {
        $this->purchaseOrder = $purchaseOrder;
        $this->initialisation($purchaseOrder->organisation, $request);

        return $this->handle($purchaseOrder, $this->validatedData['transactions']);
    }

    public function htmlResponse(int $packed): RedirectResponse
    {
        return Redirect::back()->with('notification', [
            'status'  => 'success',
            'title'   => __(':count items packed', ['count' => $packed]),
            'message' => __('Packed for the next container'),
        ]);
    }
}
