<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 09 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\AgentPayment;

use App\Actions\OrgAction;
use App\Models\SupplyChain\AgentPayment;
use App\Models\SysAdmin\User;
use Illuminate\Http\RedirectResponse;
use Lorisleiva\Actions\ActionRequest;

class DeleteAgentPayment extends OrgAction
{
    private AgentPayment $agentPayment;

    public function handle(AgentPayment $agentPayment): AgentPayment
    {
        $agentPayment->delete();

        return $agentPayment;
    }

    public function authorize(ActionRequest $request): bool
    {
        return self::canDelete($this->agentPayment, $request->user());
    }

    /**
     * The organisation's purchaser or accountant removes any payment; the agent's staff only one they recorded.
     */
    public static function canDelete(AgentPayment $agentPayment, User $user): bool
    {
        if ($user->authTo(["procurement.$agentPayment->organisation_id.edit", "accounting.$agentPayment->organisation_id.edit"])) {
            return true;
        }

        $agentOrganisationId = $agentPayment->agent?->organisation_id;

        return $agentOrganisationId !== null
            && $agentPayment->created_by === $user->id
            && $user->authTo("procurement.$agentOrganisationId.edit");
    }

    public function asController(AgentPayment $agentPayment, ActionRequest $request): AgentPayment
    {
        $this->agentPayment = $agentPayment;
        $this->initialisation($agentPayment->organisation, $request);

        return $this->handle($agentPayment);
    }

    public function htmlResponse(): RedirectResponse
    {
        return back();
    }
}
