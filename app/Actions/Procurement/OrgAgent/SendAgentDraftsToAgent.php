<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 10 Oct 2026 Malaga, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\OrgAgent;

use App\Actions\OrgAction;
use App\Actions\Procurement\OrgAgent\UI\ShowAgentDraftsToSend;
use App\Actions\Procurement\PurchaseOrder\UpdatePurchaseOrderStateToSubmitted;
use App\Actions\Traits\Authorisations\WithProcurementEditAuthorisation;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Models\Procurement\OrgAgent;
use App\Models\Procurement\PurchaseOrder;
use App\Models\SysAdmin\Organisation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;

class SendAgentDraftsToAgent extends OrgAction
{
    use WithProcurementEditAuthorisation;

    /**
     * Sends a supplier's drafts to the agent, which is when the agent starts on them. Every
     * organisation stacking on that supplier has to have marked its draft ready; whoever sends takes
     * their own organisation's draft with them. Sending before the others are ready needs a written
     * reason, and then only the ready drafts go: an organisation that has not agreed keeps its draft
     * and is never ordered for.
     *
     * @param  array<int, int>  $supplierIds
     * @return array{sent: int, held_back: int}
     *
     * @throws ValidationException
     */
    public function handle(OrgAgent $orgAgent, array $supplierIds, ?string $overrideReason = null, ?string $by = null): array
    {
        return DB::transaction(function () use ($orgAgent, $supplierIds, $overrideReason, $by) {
            $drafts = PurchaseOrder::whereIn('id', ShowAgentDraftsToSend::drafts($orgAgent)->whereIn('po.supplier_id', $supplierIds)->pluck('po.id'))
                ->lockForUpdate()
                ->get();

            $sent     = 0;
            $heldBack = 0;

            foreach ($supplierIds as $supplierId) {
                $supplierDrafts = $drafts->where('supplier_id', $supplierId);
                $isGoing        = fn (PurchaseOrder $draft) => $draft->organisation_id === $orgAgent->organisation_id || isset($draft->data['ready_to_send']);

                if (!$supplierDrafts->contains('organisation_id', $orgAgent->organisation_id)) {
                    throw ValidationException::withMessages(['drafts' => __('Your organisation has no draft for this supplier, so it is not yours to submit')]);
                }

                $waiting = $supplierDrafts->reject($isGoing);
                if ($waiting->isNotEmpty() && !$overrideReason) {
                    throw ValidationException::withMessages(['override_reason' => __('Not every organisation has marked its draft ready, write why you are submitting without them')]);
                }

                foreach ($supplierDrafts->filter($isGoing) as $draft) {
                    $data = $draft->data ?? [];
                    unset($data['ready_to_send']);
                    if ($waiting->isNotEmpty()) {
                        $data['sent_without_waiting'] = [
                            'by'      => $by,
                            'at'      => now()->toIso8601String(),
                            'reason'  => $overrideReason,
                            'waiting' => $waiting->pluck('reference')->values()->all(),
                        ];
                    }
                    $draft->update(['data' => $data]);

                    UpdatePurchaseOrderStateToSubmitted::make()->action($draft);
                    $sent++;
                }

                $heldBack += $waiting->count();
            }

            return ['sent' => $sent, 'held_back' => $heldBack];
        });
    }

    /**
     * The organisations still stacking on the same supplier that have not marked their draft ready.
     * An order sent any other way than from POs to submit must not go past them unnoticed.
     *
     * @return array<int, string> organisation codes
     */
    public static function organisationsNotReady(PurchaseOrder $purchaseOrder): array
    {
        if ($purchaseOrder->parent_type !== 'OrgSupplier' || !$purchaseOrder->agent_id) {
            return [];
        }

        return DB::table('purchase_orders as po')
            ->join('organisations as o', 'o.id', 'po.organisation_id')
            ->where('po.group_id', $purchaseOrder->group_id)
            ->where('po.agent_id', $purchaseOrder->agent_id)
            ->where('po.supplier_id', $purchaseOrder->supplier_id)
            ->where('po.parent_type', 'OrgSupplier')
            ->where('po.organisation_id', '!=', $purchaseOrder->organisation_id)
            ->where('po.state', PurchaseOrderStateEnum::IN_PROCESS->value)
            ->whereNull('po.deleted_at')
            ->whereRaw("po.data->'ready_to_send' is null")
            ->whereExists(fn ($query) => $query->selectRaw('1')
                ->from('purchase_order_transactions as line')
                ->whereColumn('line.purchase_order_id', 'po.id')
                ->whereNull('line.deleted_at'))
            ->orderBy('o.code')
            ->pluck('o.code')
            ->unique()
            ->values()
            ->all();
    }

    public function rules(): array
    {
        return [
            'supplier_ids'    => ['required', 'array', 'min:1'],
            'supplier_ids.*'  => ['integer'],
            'override_reason' => ['sometimes', 'nullable', 'string', 'min:10', 'max:1000'],
        ];
    }

    public function asController(Organisation $organisation, OrgAgent $orgAgent, ActionRequest $request): array
    {
        $this->initialisation($organisation, $request);

        return $this->handle(
            $orgAgent,
            array_map(intval(...), $this->validatedData['supplier_ids']),
            $this->validatedData['override_reason'] ?? null,
            $request->user()->contact_name ?? $request->user()->username
        );
    }

    public function htmlResponse(array $result): RedirectResponse
    {
        return Redirect::back()->with('notification', [
            'status'  => $result['held_back'] ? 'warning' : 'success',
            'title'   => __('Sent to the agent'),
            'message' => __(':sent purchase orders sent, :held left as drafts', ['sent' => $result['sent'], 'held' => $result['held_back']]),
        ]);
    }
}
