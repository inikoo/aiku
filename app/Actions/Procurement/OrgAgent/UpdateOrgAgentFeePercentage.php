<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\OrgAgent;

use App\Actions\OrgAction;
use App\Models\Procurement\OrgAgent;
use Illuminate\Http\RedirectResponse;
use Lorisleiva\Actions\ActionRequest;

/**
 * The agent sets the fee it charges one client on the goods of each container, recorded in its ledger when it
 * invoices the container. It is the agent's to set, not the client's. Empty goes back to 2%.
 */
class UpdateOrgAgentFeePercentage extends OrgAction
{
    private OrgAgent $orgAgent;

    public function handle(OrgAgent $orgAgent, array $modelData): OrgAgent
    {
        $orgAgent->update(['agent_fee_percentage' => $modelData['agent_fee_percentage'] ?? null]);

        return $orgAgent;
    }

    public function rules(): array
    {
        return [
            'agent_fee_percentage' => ['present', 'nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->authTo("procurement.{$this->orgAgent->agent->organisation_id}.edit");
    }

    public function asController(OrgAgent $orgAgent, ActionRequest $request): OrgAgent
    {
        $this->orgAgent = $orgAgent;
        $this->initialisation($orgAgent->agent->organisation, $request);

        return $this->handle($orgAgent, $this->validatedData);
    }

    public function htmlResponse(): RedirectResponse
    {
        return back();
    }
}
