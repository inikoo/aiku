<?php

/*
    * Author: Vika Aqordi
    * Created on: 2026-10-16 15:45
    * Github: https://github.com/aqordeon
    * Copyright: 2026
*/

namespace App\Actions\Procurement\AgentGoodsReceipt;

use App\Models\Procurement\AgentGoodsReceipt;
use App\Models\SysAdmin\Organisation;
use Lorisleiva\Actions\ActionRequest;

trait WithAgentGoodsReceipt
{
    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->authTo("procurement.{$this->organisation->id}.edit");
    }

    protected function ensureReceiptOfOrganisationAgent(Organisation $organisation, AgentGoodsReceipt $receipt): void
    {
        abort_unless($receipt->agent_id === $organisation->agent?->id, 404);
    }
}
