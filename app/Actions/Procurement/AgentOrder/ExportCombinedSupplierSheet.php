<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\AgentOrder;

use App\Actions\OrgAction;
use App\Actions\Procurement\AgentOrder\UI\IndexAgentSharedSuppliers;
use App\Actions\Procurement\WithAgentOrganisation;
use App\Actions\Traits\Authorisations\WithProcurementAuthorisation;
use App\Exports\Procurement\CombinedSupplierSheetExport;
use App\Models\Procurement\PurchaseOrder;
use App\Models\SupplyChain\Supplier;
use App\Models\SysAdmin\Organisation;
use Lorisleiva\Actions\ActionRequest;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Downloads the agent's combined sheet for a factory: every client's open order with it, a block each.
 */
class ExportCombinedSupplierSheet extends OrgAction
{
    use WithAgentOrganisation;
    use WithProcurementAuthorisation;

    public function asController(Organisation $organisation, Supplier $supplier, ActionRequest $request): BinaryFileResponse
    {
        $this->initialisation($organisation, $request);
        $agent = $this->getOrganisationAgent($organisation);
        abort_unless($agent && $supplier->agent_id === $agent->id, 404);

        $purchaseOrders = PurchaseOrder::whereIn('id', IndexAgentSharedSuppliers::openOrders($agent)->where('purchase_orders.supplier_id', $supplier->id)->select('purchase_orders.id'))
            ->with(['organisation:id,code,name', 'currency:id,code', 'purchaseOrderTransactions.supplierProduct', 'purchaseOrderTransactions.orgStock:id,code,name'])
            ->get()
            ->sortBy(fn (PurchaseOrder $purchaseOrder) => $purchaseOrder->organisation->code.'|'.$purchaseOrder->date?->toDateString())
            ->values();

        return Excel::download(
            new CombinedSupplierSheetExport($supplier, $purchaseOrders),
            preg_replace('/[^A-Za-z0-9._-]/', '-', $supplier->code).'-combined-'.now()->toDateString().'.xlsx'
        );
    }
}
