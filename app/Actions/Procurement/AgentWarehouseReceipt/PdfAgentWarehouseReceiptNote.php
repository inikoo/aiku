<?php

/*
    * Author: Vika Aqordi
    * Created on: 2026-10-16 10:45
    * Github: https://github.com/aqordeon
    * Copyright: 2026
*/

namespace App\Actions\Procurement\AgentWarehouseReceipt;

use App\Actions\OrgAction;
use App\Actions\Procurement\WithAgentOrganisation;
use App\Actions\Traits\Authorisations\WithProcurementAuthorisation;
use App\Models\Helpers\Media;
use App\Models\Procurement\AgentWarehouseReceipt;
use App\Models\Procurement\OrgSupplier;
use App\Models\Procurement\PurchaseOrder;
use App\Models\SysAdmin\Organisation;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\ActionRequest;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as PDF;
use Throwable;

/**
 * The receipt note the agent hands to the supplier: what arrived at the agent's warehouse for one purchase order on a
 * day, or over a few days, signed by whoever delivered and whoever received.
 */
class PdfAgentWarehouseReceiptNote extends OrgAction
{
    use WithAgentOrganisation;
    use WithProcurementAuthorisation;

    public function handle(PurchaseOrder $purchaseOrder, Carbon $from, Carbon $to): string
    {
        $receipts = AgentWarehouseReceipt::where('purchase_order_id', $purchaseOrder->id)
            ->whereBetween('received_at', [$from->toDateString(), $to->toDateString()])
            ->with(['supplierProduct:id,code,name', 'purchaseOrderTransaction:id,quantity_ordered'])
            ->get()
            ->sortBy(fn (AgentWarehouseReceipt $receipt) => [$receipt->received_at->toDateString(), $receipt->supplierProduct?->code])
            ->values();

        $agentOrganisation = $purchaseOrder->agent->organisation;
        $agentOrganisation->loadMissing(['address', 'image']);

        return PDF::loadView('procurement.templates.pdf.agent-receipt-note', [
            'purchaseOrder' => $purchaseOrder,
            'agent'         => $agentOrganisation,
            'logo'          => $this->logo($agentOrganisation->image),
            'supplierName'  => $purchaseOrder->parent instanceof OrgSupplier ? $purchaseOrder->parent->supplier?->name : null,
            'dateLabel'     => $from->isSameDay($to) ? $from->format('d/m/Y') : $from->format('d/m/Y').' – '.$to->format('d/m/Y'),
            'isOneDay'      => $from->isSameDay($to),
            'lines'         => $this->lines($receipts),
        ])->output();
    }

    /**
     * @return Collection<int, array{date: string, code: ?string, name: ?string, ordered: float, received: float, remarks: ?string}>
     */
    private function lines(Collection $receipts): Collection
    {
        return $receipts->map(fn (AgentWarehouseReceipt $receipt) => [
            'date'     => $receipt->received_at->format('d/m/Y'),
            'code'     => $receipt->supplierProduct?->code,
            'name'     => $receipt->supplierProduct?->name,
            'ordered'  => (float) $receipt->purchaseOrderTransaction?->quantity_ordered,
            'received' => (float) $receipt->quantity_received,
            'remarks'  => $receipt->notes,
        ]);
    }

    /**
     * The file itself when it is on this server, else the picture from the image server; no logo when neither is there.
     */
    private function logo(?Media $image): ?string
    {
        if (!$image) {
            return null;
        }

        $path = $image->getPath();
        if ($path && file_exists($path)) {
            return $path;
        }

        try {
            return 'data:image/png;base64,'.$image->getBase64Image();
        } catch (Throwable) {
            return null;
        }
    }

    public function filename(PurchaseOrder $purchaseOrder, Carbon $from, Carbon $to): string
    {
        $dates = $from->isSameDay($to) ? $from->toDateString() : $from->toDateString().'_'.$to->toDateString();

        return preg_replace('/[^A-Za-z0-9._-]/', '-', __('Receipt note').'-'.$purchaseOrder->reference.'-'.$dates).'.pdf';
    }

    public function rules(): array
    {
        return [
            'from' => ['sometimes', 'nullable', 'date'],
            'to'   => ['sometimes', 'nullable', 'date', 'after_or_equal:from'],
        ];
    }

    public function asController(Organisation $organisation, PurchaseOrder $purchaseOrder, ActionRequest $request): Response
    {
        $agent = $this->getOrganisationAgent($organisation);
        abort_unless($agent && $purchaseOrder->isAgentOrder() && $purchaseOrder->agent_id === $agent->id, 404);

        $this->initialisation($organisation, $request);

        $from = Carbon::parse($this->validatedData['from'] ?? now($organisation->timezone?->name)->toDateString());
        $to   = Carbon::parse($this->validatedData['to'] ?? $from->toDateString());

        return response($this->handle($purchaseOrder, $from, $to), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="'.$this->filename($purchaseOrder, $from, $to).'"');
    }
}
