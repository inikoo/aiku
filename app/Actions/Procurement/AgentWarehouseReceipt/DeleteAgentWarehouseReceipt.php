<?php

/*
    * Author: Vika Aqordi
    * Created on: 2026-10-16 09:52
    * Github: https://github.com/aqordeon
    * Copyright: 2026
*/

namespace App\Actions\Procurement\AgentWarehouseReceipt;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithProcurementEditAuthorisation;
use App\Models\Procurement\AgentWarehouseReceipt;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

/**
 * Takes back a batch received by mistake, with its goods in the agent's warehouse. Only while they are not packed.
 */
class DeleteAgentWarehouseReceipt extends OrgAction
{
    use WithProcurementEditAuthorisation;

    private AgentWarehouseReceipt $receipt;

    public function handle(AgentWarehouseReceipt $receipt): void
    {
        DB::transaction(function () use ($receipt) {
            $receipt->agentWarehouseStock?->delete();
            $receipt->delete();
        });
    }

    public function afterValidator(Validator $validator): void
    {
        if (!$this->receipt->isEditable()) {
            $validator->errors()->add('receipt', __('These goods are already packed for a container: the receipt can no longer be removed'));
        }
    }

    public function asController(AgentWarehouseReceipt $agentWarehouseReceipt, ActionRequest $request): void
    {
        $this->receipt = $agentWarehouseReceipt;
        $this->initialisation($agentWarehouseReceipt->agent->organisation, $request);

        $this->handle($agentWarehouseReceipt);
    }

    public function htmlResponse(): RedirectResponse
    {
        return back();
    }
}
