<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\PurchaseOrder\UI;

use App\Actions\OrgAction;
use App\Actions\Procurement\WithAgentOrganisation;
use App\Actions\Traits\Authorisations\WithProcurementAuthorisation;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStageEnum;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Enums\Procurement\PurchaseOrderChase\PurchaseOrderChaseChannelEnum;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseOrderChase;
use App\Models\SysAdmin\Organisation;
use App\Models\SysAdmin\User;
use Lorisleiva\Actions\ActionRequest;

/**
 * What the agent's chase list opens for one purchase order: its stage tracker with the dates this user may
 * record, whether it can be confirmed, and the chases logged so far.
 */
class GetPurchaseOrderChasePanel extends OrgAction
{
    use WithAgentOrganisation;
    use WithProcurementAuthorisation;

    public function handle(PurchaseOrder $purchaseOrder, User $user): array
    {
        $agentCanEdit = $this->agentEditsOwnOrder($purchaseOrder, $user);
        $buyerCanEdit = $user->authTo("procurement.{$purchaseOrder->organisation_id}.edit");

        $markableFields = match (true) {
            $buyerCanEdit => array_keys(PurchaseOrderStageEnum::MARKABLE_FIELDS),
            $agentCanEdit => PurchaseOrderStageEnum::AGENT_MARKABLE_FIELDS,
            default       => [],
        };
        $canEdit = $agentCanEdit || $buyerCanEdit;

        return [
            'reference'      => $purchaseOrder->reference,
            'supplier'       => $purchaseOrder->parent_name,
            'state'          => $purchaseOrder->state->value,
            'eta'            => $purchaseOrder->estimated_received_at?->toDateString(),
            'can_edit'       => $canEdit,
            'confirm_route'  => $canEdit && $purchaseOrder->state === PurchaseOrderStateEnum::SUBMITTED ? [
                'name'       => 'grp.models.purchase-order.confirm',
                'parameters' => ['purchaseOrder' => $purchaseOrder->id],
            ] : null,
            'stages'         => GetPurchaseOrderStages::make()->forUi($purchaseOrder, $markableFields),
            'stage_route'    => [
                'name'       => 'grp.models.purchase-order.stage',
                'parameters' => ['purchaseOrder' => $purchaseOrder->id],
            ],
            'chase_route'    => $canEdit ? [
                'name'       => 'grp.models.purchase-order.chase.store',
                'parameters' => ['purchaseOrder' => $purchaseOrder->id],
            ] : null,
            'channels'       => collect(PurchaseOrderChaseChannelEnum::labels())->map(fn ($label, $value) => ['value' => $value, 'label' => $label])->values(),
            'chases'         => $purchaseOrder->chases()->with('user:id,contact_name,username')->latest('chased_at')->limit(10)->get()
                ->map(fn (PurchaseOrderChase $chase) => [
                    'id'          => $chase->id,
                    'chased_at'   => $chase->chased_at,
                    'channel'     => PurchaseOrderChaseChannelEnum::labels()[$chase->channel->value],
                    'contact'     => $chase->contact,
                    'promised_at' => $chase->promised_at?->toDateString(),
                    'note'        => $chase->note,
                    'by'          => $chase->user?->contact_name ?: $chase->user?->username,
                ]),
        ];
    }

    public function asController(Organisation $organisation, PurchaseOrder $purchaseOrder, ActionRequest $request): array
    {
        $this->initialisation($organisation, $request);
        $this->authorizeProcurementRecord($purchaseOrder);

        return $this->handle($purchaseOrder, $request->user());
    }
}
