<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 16 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\AgentOrder;

use App\Actions\OrgAction;
use App\Actions\Procurement\PurchaseOrder\NotifyBuyerOfAgentStageChange;
use App\Actions\SupplyChain\AspoDeposit\StoreAspoDeposit;
use App\Actions\SupplyChain\DepositRequest\StoreDepositRequest;
use App\Actions\Traits\Authorisations\WithPurchaseOrderAgentEditAuthorisation;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Models\Procurement\PurchaseOrder;
use App\Models\SupplyChain\DepositRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

/**
 * The agent asks the client for the deposits of an agent order: one deposit per supplier order with the amount
 * that supplier wants, sent to the client as one deposit request per currency.
 */
class RequestAgentWholeOrderDeposits extends OrgAction
{
    use WithPurchaseOrderAgentEditAuthorisation;

    private PurchaseOrder $purchaseOrder;

    public function rules(): array
    {
        return [
            'reference'                    => ['sometimes', 'nullable', 'string', 'max:255'],
            'notes'                        => ['sometimes', 'nullable', 'string', 'max:2000'],
            'deposits'                     => ['required', 'array', 'min:1'],
            'deposits.*.purchase_order_id' => ['required', 'integer', 'distinct'],
            'deposits.*.amount'            => ['required', 'numeric', 'gt:0', 'max:999999999999'],
        ];
    }

    public function afterValidator(Validator $validator): void
    {
        $requestable = $this->purchaseOrder->wholeAgentOrder()
            ->whereIn('state', [PurchaseOrderStateEnum::SUBMITTED, PurchaseOrderStateEnum::CONFIRMED, PurchaseOrderStateEnum::SETTLED])
            ->pluck('id');

        foreach ($this->get('deposits', []) as $deposit) {
            if (!$requestable->contains((int) Arr::get($deposit, 'purchase_order_id'))) {
                $validator->errors()->add('deposits', __('One of the orders is not a live supplier order of this agent order'));

                return;
            }
        }
    }

    /**
     * @param  array{reference?: string|null, notes?: string|null, deposits: array<int, array{purchase_order_id: int, amount: float|string}>}  $modelData
     *
     * @return Collection<int, DepositRequest>
     */
    public function handle(PurchaseOrder $purchaseOrder, array $modelData): Collection
    {
        $supplierOrders = $purchaseOrder->wholeAgentOrder()->get()->keyBy('id');

        return DB::transaction(function () use ($purchaseOrder, $modelData, $supplierOrders) {
            $items = collect($modelData['deposits'])->map(function (array $deposit) use ($modelData, $supplierOrders) {
                $supplierOrder = $supplierOrders->get((int) $deposit['purchase_order_id']);
                $aspoDeposit   = StoreAspoDeposit::make()->action($supplierOrder, [
                    'amount'    => $deposit['amount'],
                    'reference' => Arr::get($modelData, 'reference'),
                    'notes'     => Arr::get($modelData, 'notes'),
                ]);

                return [
                    'currency_id'     => $aspoDeposit->currency_id,
                    'aspo_deposit_id' => $aspoDeposit->id,
                    'organisation_id' => $supplierOrder->organisation_id,
                    'amount'          => $aspoDeposit->amount,
                ];
            });

            return $items->groupBy('currency_id')
                ->map(fn (Collection $currencyItems, $currencyId) => StoreDepositRequest::make()->action($purchaseOrder->agent, [
                    'reference'   => Arr::get($modelData, 'reference') ?: $purchaseOrder->agent_order_reference,
                    'currency_id' => $currencyId,
                    'items'       => $currencyItems->map(fn (array $item) => Arr::except($item, 'currency_id'))->values()->all(),
                ]))
                ->values();
        });
    }

    public function asController(PurchaseOrder $purchaseOrder, ActionRequest $request): RedirectResponse
    {
        $this->purchaseOrder = $purchaseOrder;
        $this->initialisation($purchaseOrder->organisation, $request);

        $this->handle($purchaseOrder, $this->validatedData);

        $purchaseOrder->wholeAgentOrder()
            ->whereIn('id', Arr::pluck($this->validatedData['deposits'], 'purchase_order_id'))
            ->get()
            ->unique('buyer_id')
            ->each(fn (PurchaseOrder $supplierOrder) => NotifyBuyerOfAgentStageChange::run(
                $supplierOrder,
                $request->user(),
                __('the agent asked for the deposits of :reference', ['reference' => $purchaseOrder->agent_order_reference])
            ));

        return redirect()->back();
    }

    /**
     * @return Collection<int, DepositRequest>
     */
    public function action(PurchaseOrder $purchaseOrder, array $modelData): Collection
    {
        $this->asAction      = true;
        $this->purchaseOrder = $purchaseOrder;
        $this->initialisation($purchaseOrder->organisation, $modelData);

        return $this->handle($purchaseOrder, $this->validatedData);
    }
}
