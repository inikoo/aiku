<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 11 Oct 2026 Malaga, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\OrgAgent;

use App\Actions\OrgAction;
use App\Actions\Procurement\PurchaseOrder\DeletePurchaseOrderTransaction;
use App\Actions\Procurement\PurchaseOrder\StorePurchaseOrder;
use App\Actions\Procurement\PurchaseOrder\UpdatePurchaseOrderTransactionQuantity;
use App\Actions\Procurement\PurchaseOrderTransaction\StorePurchaseOrderTransaction;
use App\Actions\Traits\Authorisations\WithProcurementEditAuthorisation;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Models\Procurement\OrgAgent;
use App\Models\Procurement\OrgSupplier;
use App\Models\Procurement\OrgSupplierProduct;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseOrderTransaction;
use App\Models\SysAdmin\Organisation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;

class SetAgentDraftLine extends OrgAction
{
    use WithProcurementEditAuthorisation;

    /**
     * How much of a product the organisation wants from an agent's supplier, kept on that supplier's
     * draft purchase order: the draft is opened on the first product, the line follows the quantity
     * and goes when it reaches zero. The product is kept on one line: if the draft holds it more than
     * once the extra lines are folded into the first. A draft that changes is no longer "ready to
     * send", the organisation has to say so again.
     *
     * @throws ValidationException
     */
    public function handle(OrgAgent $orgAgent, OrgSupplierProduct $orgSupplierProduct, float $quantityUnits): ?PurchaseOrderTransaction
    {
        if ($orgSupplierProduct->org_agent_id !== $orgAgent->id) {
            throw ValidationException::withMessages(['org_supplier_product' => __('This product is not bought through :agent', ['agent' => $orgAgent->agent->name])]);
        }

        return DB::transaction(function () use ($orgAgent, $orgSupplierProduct, $quantityUnits) {
            $orgSupplier   = OrgSupplier::whereKey($orgSupplierProduct->org_supplier_id)->lockForUpdate()->firstOrFail();
            $purchaseOrder = $orgSupplier->purchaseOrders()->where('state', PurchaseOrderStateEnum::IN_PROCESS)->oldest('id')->first();
            $lines         = $purchaseOrder?->purchaseOrderTransactions()->where('org_supplier_product_id', $orgSupplierProduct->id)->oldest('id')->get() ?? collect();
            $line          = $lines->first();

            $lines->skip(1)->each(fn (PurchaseOrderTransaction $duplicate) => DeletePurchaseOrderTransaction::make()->action($duplicate));

            if ($quantityUnits <= 0 && !$line) {
                return null;
            }

            if ($line) {
                $line = UpdatePurchaseOrderTransactionQuantity::make()->action($line, ['quantity_ordered' => max(0, $quantityUnits)]);
            } else {
                GetAgentOrderCapacity::guardAdd($orgAgent, $orgSupplierProduct);
                $purchaseOrder ??= StorePurchaseOrder::make()->action($orgSupplier, []);
                $line            = StorePurchaseOrderTransaction::make()->addOrgSupplierProduct($purchaseOrder, $orgSupplierProduct, ['quantity_ordered' => $quantityUnits]);
            }

            $this->noLongerReadyToSend($purchaseOrder);

            return $quantityUnits > 0 ? $line : null;
        });
    }

    private function noLongerReadyToSend(PurchaseOrder $purchaseOrder): void
    {
        $data = $purchaseOrder->data ?? [];
        if (isset($data['ready_to_send'])) {
            unset($data['ready_to_send']);
            $purchaseOrder->update(['data' => $data]);
        }
    }

    public function rules(): array
    {
        return [
            'quantity_units' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function asController(Organisation $organisation, OrgAgent $orgAgent, OrgSupplierProduct $orgSupplierProduct, ActionRequest $request): ?PurchaseOrderTransaction
    {
        $this->initialisation($organisation, $request);

        return $this->handle($orgAgent, $orgSupplierProduct, (float) $this->validatedData['quantity_units']);
    }

    public function htmlResponse(): RedirectResponse
    {
        return Redirect::back();
    }
}
