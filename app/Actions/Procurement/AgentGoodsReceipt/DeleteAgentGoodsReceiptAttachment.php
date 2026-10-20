<?php

/*
    * Author: Vika Aqordi
    * Created on: 2026-10-16 15:45
    * Github: https://github.com/aqordeon
    * Copyright: 2026
*/

namespace App\Actions\Procurement\AgentGoodsReceipt;

use App\Actions\OrgAction;
use App\Models\Helpers\Media;
use App\Models\Procurement\AgentGoodsReceipt;
use App\Models\SysAdmin\Organisation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;

class DeleteAgentGoodsReceiptAttachment extends OrgAction
{
    use WithAgentGoodsReceipt;

    public function handle(AgentGoodsReceipt $receipt, Media $media): void
    {
        $receipt->attachments()->detach($media->id);
    }

    public function asController(Organisation $organisation, AgentGoodsReceipt $agentGoodsReceipt, Media $media, ActionRequest $request): void
    {
        $this->ensureReceiptOfOrganisationAgent($organisation, $agentGoodsReceipt);
        $this->initialisation($organisation, $request);

        $this->handle($agentGoodsReceipt, $media);
    }

    public function htmlResponse(): RedirectResponse
    {
        return Redirect::back();
    }
}
