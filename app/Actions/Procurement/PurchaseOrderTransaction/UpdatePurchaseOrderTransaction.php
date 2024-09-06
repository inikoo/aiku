<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 10:48:24 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\PurchaseOrderTransaction;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\Procurement\PurchaseOrderResource;
use App\Models\Procurement\PurchaseOrderTransaction;
use Lorisleiva\Actions\ActionRequest;

class UpdatePurchaseOrderTransaction extends OrgAction
{
    use WithActionUpdate;

    public function handle(PurchaseOrderTransaction $purchaseOrderTransaction, array $modelData): PurchaseOrderTransaction
    {
        return $this->update($purchaseOrderTransaction, $modelData, ['data']);
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo("procurement.{$this->organisation->id}.edit");

        return $request->user()->hasPermissionTo("procurement.{$this->organisation->id}.view");
    }

    public function rules(): array
    {
        return [
            'unit_quantity' => ['sometimes', 'required', 'numeric', 'gt:0'],
            'unit_price'    => ['sometimes', 'required', 'numeric'],
        ];
    }

    public function asController(PurchaseOrderTransaction $purchaseOrderTransaction, ActionRequest $request): PurchaseOrderTransaction
    {
        $this->initialisation($purchaseOrderTransaction->organisation, $request);
        return $this->handle($purchaseOrderTransaction, $this->validatedData);
    }

    public function jsonResponse(PurchaseOrderTransaction $purchaseOrderTransaction): PurchaseOrderResource
    {
        return new PurchaseOrderResource($purchaseOrderTransaction);
    }
}
