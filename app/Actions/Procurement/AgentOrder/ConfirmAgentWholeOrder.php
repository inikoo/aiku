<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 16 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\AgentOrder;

use App\Actions\OrgAction;
use App\Actions\Procurement\PurchaseOrder\NotifyBuyerOfAgentStageChange;
use App\Actions\Procurement\PurchaseOrder\UpdatePurchaseOrderStateToConfirmed;
use App\Actions\Traits\Authorisations\WithPurchaseOrderAgentEditAuthorisation;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Models\Procurement\PurchaseOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\ActionRequest;

/**
 * Confirms in one go every submitted supplier order of an agent order, once the suppliers said they can supply it.
 */
class ConfirmAgentWholeOrder extends OrgAction
{
    use WithPurchaseOrderAgentEditAuthorisation;

    /**
     * @return Collection<int, PurchaseOrder>
     */
    public function handle(PurchaseOrder $purchaseOrder): Collection
    {
        return $purchaseOrder->wholeAgentOrder()
            ->where('state', PurchaseOrderStateEnum::SUBMITTED)
            ->get()
            ->map(fn (PurchaseOrder $supplierOrder) => UpdatePurchaseOrderStateToConfirmed::make()->action($supplierOrder));
    }

    public function asController(PurchaseOrder $purchaseOrder, ActionRequest $request): RedirectResponse
    {
        $this->initialisation($purchaseOrder->organisation, $request);

        $this->handle($purchaseOrder)
            ->unique('buyer_id')
            ->each(fn (PurchaseOrder $supplierOrder) => NotifyBuyerOfAgentStageChange::run(
                $supplierOrder,
                $request->user(),
                __('accepted by the agent, with every supplier order of :reference', ['reference' => $purchaseOrder->agent_order_reference])
            ));

        return redirect()->back();
    }

    /**
     * @return Collection<int, PurchaseOrder>
     */
    public function action(PurchaseOrder $purchaseOrder): Collection
    {
        $this->asAction = true;
        $this->initialisation($purchaseOrder->organisation, []);

        return $this->handle($purchaseOrder);
    }
}
