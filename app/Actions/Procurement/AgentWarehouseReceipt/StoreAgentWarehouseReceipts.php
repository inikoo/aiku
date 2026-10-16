<?php

/*
    * Author: Vika Aqordi
    * Created on: 2026-10-16 10:27
    * Github: https://github.com/aqordeon
    * Copyright: 2026
*/

namespace App\Actions\Procurement\AgentWarehouseReceipt;

use App\Actions\OrgAction;
use App\Actions\Procurement\PurchaseOrder\MarkPurchaseOrderStage;
use App\Actions\Procurement\WithAgentOrganisation;
use App\Actions\Traits\Authorisations\WithProcurementEditAuthorisation;
use App\Models\Procurement\PurchaseOrderTransaction;
use App\Models\SysAdmin\Organisation;
use App\Models\SysAdmin\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

/**
 * The agent receives several purchase order lines at once, all on the same day: each one is a batch of its own, as if
 * received one by one from its order. Either all of them are received or none is.
 */
class StoreAgentWarehouseReceipts extends OrgAction
{
    use WithAgentOrganisation;
    use WithProcurementEditAuthorisation;

    /**
     * @var Collection<int, PurchaseOrderTransaction>
     */
    private Collection $purchaseOrderTransactions;

    public function handle(User $user, array $modelData): int
    {
        DB::transaction(function () use ($user, $modelData) {
            foreach ($modelData['lines'] as $index => $line) {
                $purchaseOrderTransaction = $this->purchaseOrderTransactions->get($line['purchase_order_transaction_id']);

                try {
                    StoreAgentWarehouseReceipt::make()->handle($purchaseOrderTransaction, $user, [
                        'received_at'       => $modelData['received_at'],
                        'quantity_received' => $line['quantity_received'],
                    ]);
                } catch (ValidationException $exception) {
                    throw ValidationException::withMessages([
                        "lines.$index.quantity_received" => $purchaseOrderTransaction->supplierProduct?->code.': '.collect($exception->errors())->flatten()->first(),
                    ]);
                }
            }
        });

        return count($modelData['lines']);
    }

    public function rules(): array
    {
        return [
            'received_at'                           => ['required', 'date', 'before_or_equal:'.Carbon::parse(MarkPurchaseOrderStage::today($this->organisation))->addDay()->toDateString()],
            'lines'                                 => ['required', 'array', 'min:1', 'max:200'],
            'lines.*.purchase_order_transaction_id' => ['required', 'integer', 'distinct'],
            'lines.*.quantity_received'             => ['required', 'numeric', 'gt:0'],
        ];
    }

    public function afterValidator(Validator $validator): void
    {
        if ($validator->errors()->isNotEmpty()) {
            return;
        }

        $agent = $this->getOrganisationAgent($this->organisation);

        $this->purchaseOrderTransactions = PurchaseOrderTransaction::whereIn('id', collect($this->get('lines'))->pluck('purchase_order_transaction_id'))
            ->with(['purchaseOrder', 'supplierProduct:id,code'])
            ->get()
            ->keyBy('id');

        foreach ($this->get('lines') as $index => $line) {
            $purchaseOrderTransaction = $this->purchaseOrderTransactions->get($line['purchase_order_transaction_id']);

            if (!$agent || !$purchaseOrderTransaction || $purchaseOrderTransaction->purchaseOrder->agent_id !== $agent->id) {
                $validator->errors()->add("lines.$index.purchase_order_transaction_id", __('This line is not on an order placed through us'));
            } elseif (!StoreAgentWarehouseReceipt::isReceivable($purchaseOrderTransaction)) {
                $validator->errors()->add("lines.$index.purchase_order_transaction_id", __(':code: only the lines of a sent order that are not cancelled can be received', ['code' => $purchaseOrderTransaction->supplierProduct?->code]));
            }
        }
    }

    public function asController(Organisation $organisation, ActionRequest $request): int
    {
        $this->initialisation($organisation, $request);

        return $this->handle($request->user(), $this->validatedData);
    }

    public function htmlResponse(): RedirectResponse
    {
        return back();
    }
}
