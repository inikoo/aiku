<?php

namespace App\Actions\Traits\Authorisations;

use App\Models\Procurement\PurchaseOrder;
use Lorisleiva\Actions\ActionRequest;

trait WithPurchaseOrderAgentEditAuthorisation
{
    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        $user = $request->user();

        if ($user->authTo("procurement.{$this->organisation->id}.edit")) {
            return true;
        }

        $purchaseOrder       = $request->route('purchaseOrder');
        $agentOrganisationId = $purchaseOrder instanceof PurchaseOrder && $purchaseOrder->isAgentOrder() ? $purchaseOrder->agent?->organisation_id : null;

        return $agentOrganisationId !== null && $user->authTo("procurement.$agentOrganisationId.edit");
    }
}
